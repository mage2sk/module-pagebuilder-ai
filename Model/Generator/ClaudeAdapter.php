<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\Generator;

use Panth\PageBuilderAi\Api\AiGeneratorInterface;

/**
 * Anthropic Claude content generator.
 */
class ClaudeAdapter extends AbstractHttpAdapter implements AiGeneratorInterface
{
    public function getProvider(): string
    {
        return 'claude';
    }

    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const PROVIDER = 'claude';
    private const API_VERSION = '2023-06-01';
    private const DEFAULT_MAX_TOKENS = 600;

    /**
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    public function generate(array $context, array $fields = [], array $options = []): array
    {
        $apiKey = $this->getApiKey('panth_pagebuilderai/ai/claude_api_key');
        if ($apiKey === '') {
            return ['title' => '', 'description' => '', 'confidence' => 0.0];
        }

        if (!empty($fields) && !isset($context['fields'])) {
            $context['fields'] = $fields;
        }

        $model = (string)($this->scopeConfig->getValue('panth_pagebuilderai/ai/claude_model') ?: 'claude-sonnet-4-5-20241022');

        $prompt = $this->buildPrompt($context);
        $hash = $this->promptHash(self::PROVIDER, $model, $prompt);

        $cached = $this->loadCached($hash);
        if ($cached !== null) {
            $cached['confidence'] = (float)($cached['confidence'] ?? 0.0);
            return $cached;
        }

        $budget = $this->getMonthlyBudget();
        $maxTokens = $this->getMaxTokens(self::DEFAULT_MAX_TOKENS);
        $estimate = $maxTokens * 2;
        if ($budget <= 0) {
            $this->logger->warning('Panth PageBuilderAi: Claude request rejected — monthly token budget not configured (0).');
            return ['title' => '', 'description' => '', 'confidence' => 0.0, 'error' => 'budget_not_configured'];
        }
        if (!$this->reserveBudget(self::PROVIDER, $estimate, $budget)) {
            $this->logger->warning('Panth PageBuilderAi: Claude monthly budget exhausted');
            return ['title' => '', 'description' => '', 'confidence' => 0.0, 'error' => 'budget_exhausted'];
        }

        $images = $context['images'] ?? [];
        if (!empty($images) && is_array($images)) {
            $messageContent = [];
            foreach (array_slice($images, 0, 5) as $imageData) {
                $base64 = preg_replace('/^data:image\/\w+;base64,/', '', (string)$imageData);
                $mediaType = 'image/jpeg';
                if (preg_match('/^data:(image\/\w+);base64,/', (string)$imageData, $m)) {
                    $mediaType = $m[1];
                }
                $messageContent[] = [
                    'type' => 'image',
                    'source' => [
                        'type' => 'base64',
                        'media_type' => $mediaType,
                        'data' => $base64,
                    ],
                ];
            }
            $messageContent[] = [
                'type' => 'text',
                'text' => $prompt,
            ];
            $userContent = $messageContent;
        } else {
            $userContent = $prompt;
        }

        $payload = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'temperature' => $this->getTemperature(),
            'messages' => [
                ['role' => 'user', 'content' => $userContent],
            ],
        ];

        $response = $this->curlPost(
            self::API_URL,
            [
                'x-api-key' => $apiKey,
                'anthropic-version' => self::API_VERSION,
                'content-type' => 'application/json',
            ],
            $payload
        );

        if ($response['status'] < 200 || $response['status'] >= 300) {
            $this->logger->warning('Panth PageBuilderAi Claude call failed', [
                'status' => $response['status'],
            ]);
            $this->releaseBudget(self::PROVIDER, $estimate);
            return ['title' => '', 'description' => '', 'confidence' => 0.0];
        }

        $decoded = json_decode($response['body'], true);
        if (!is_array($decoded) || !isset($decoded['content']) || !is_array($decoded['content'])) {
            $this->logger->warning('Panth PageBuilderAi Claude: unexpected response structure');
            $this->releaseBudget(self::PROVIDER, $estimate);
            return ['title' => '', 'description' => '', 'confidence' => 0.0];
        }

        $text = '';
        foreach ($decoded['content'] as $block) {
            if (is_array($block) && ($block['type'] ?? '') === 'text') {
                $text .= (string)($block['text'] ?? '');
            }
        }

        $usage = (int)($decoded['usage']['input_tokens'] ?? 0) + (int)($decoded['usage']['output_tokens'] ?? 0);
        $this->lastUsageTokens = $usage;
        $this->adjustUsage(self::PROVIDER, $usage - $estimate);

        $parsed = $this->parseJsonReply($text);
        $title = (string)($parsed['title'] ?? $parsed['meta_title'] ?? '');
        $description = (string)($parsed['description'] ?? $parsed['meta_description'] ?? '');
        if ($title === '' && $description === '') {
            // Fall back to returning raw content so PageBuilder full-page generations
            // (which don't always respond as JSON) can still surface content.
            return ['title' => '', 'description' => '', 'content' => trim($text), 'confidence' => 0.0];
        }

        $confidence = $this->heuristicConfidence($title, $description);
        $parsed['confidence'] = (float)$confidence;
        if (!isset($parsed['title'])) {
            $parsed['title'] = $title;
        }
        if (!isset($parsed['description'])) {
            $parsed['description'] = $description;
        }
        $this->saveCached($hash, $parsed, self::PROVIDER);
        return $parsed;
    }
}
