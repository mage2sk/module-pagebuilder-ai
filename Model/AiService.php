<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model;

use Panth\PageBuilderAi\Helper\Config;
use Psr\Log\LoggerInterface;

class AiService
{
    public function __construct(
        private readonly Config $config,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Generate content using the configured AI provider.
     *
     * @param string $prompt
     * @param array $images Base64-encoded images (optional)
     * @return array{success: bool, content: string, message?: string}
     */
    public function generate(string $prompt, array $images = []): array
    {
        $provider = $this->config->getProvider();

        try {
            if ($provider === 'openai') {
                return $this->callOpenAi($prompt, $images);
            }
            if ($provider === 'claude') {
                return $this->callClaude($prompt, $images);
            }
            return ['success' => false, 'content' => '', 'message' => 'No AI provider configured.'];
        } catch (\Throwable $e) {
            $this->logger->error('Panth PageBuilderAi: AI generation failed', [
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'content' => '', 'message' => 'AI generation failed. Check system logs.'];
        }
    }

    private function callOpenAi(string $prompt, array $images): array
    {
        $apiKey = $this->config->getOpenAiApiKey();
        if ($apiKey === '') {
            return ['success' => false, 'content' => '', 'message' => 'OpenAI API key not configured.'];
        }

        $userContent = $prompt;
        if (!empty($images)) {
            $parts = [];
            foreach (array_slice($images, 0, 5) as $img) {
                $base64 = (string) $img;
                if (!str_starts_with($base64, 'data:')) {
                    $base64 = 'data:image/jpeg;base64,' . $base64;
                }
                $parts[] = ['type' => 'image_url', 'image_url' => ['url' => $base64]];
            }
            $parts[] = ['type' => 'text', 'text' => $prompt];
            $userContent = $parts;
        }

        $payload = [
            'model' => $this->config->getOpenAiModel(),
            'max_tokens' => $this->config->getMaxTokens(),
            'temperature' => $this->config->getTemperature(),
            'messages' => [
                ['role' => 'system', 'content' => 'You are a professional web content writer. Return only the requested content — no JSON wrapping, no markdown code fences, no extra commentary.'],
                ['role' => 'user', 'content' => $userContent],
            ],
        ];

        $response = $this->curlPost(
            'https://api.openai.com/v1/chat/completions',
            ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json'],
            $payload
        );

        if ($response['status'] < 200 || $response['status'] >= 300) {
            $decoded = json_decode($response['body'], true);
            $msg = $decoded['error']['message'] ?? ('API returned HTTP ' . $response['status']);
            return ['success' => false, 'content' => '', 'message' => $msg];
        }

        $decoded = json_decode($response['body'], true);
        $content = $decoded['choices'][0]['message']['content'] ?? '';

        return ['success' => true, 'content' => trim($content)];
    }

    private function callClaude(string $prompt, array $images): array
    {
        $apiKey = $this->config->getClaudeApiKey();
        if ($apiKey === '') {
            return ['success' => false, 'content' => '', 'message' => 'Claude API key not configured.'];
        }

        $userContent = $prompt;
        if (!empty($images)) {
            $parts = [];
            foreach (array_slice($images, 0, 5) as $img) {
                $base64Raw = (string) $img;
                $mediaType = 'image/jpeg';
                if (preg_match('/^data:(image\/\w+);base64,/', $base64Raw, $m)) {
                    $mediaType = $m[1];
                }
                $base64Raw = preg_replace('/^data:image\/\w+;base64,/', '', $base64Raw);
                $parts[] = [
                    'type' => 'image',
                    'source' => [
                        'type' => 'base64',
                        'media_type' => $mediaType,
                        'data' => $base64Raw,
                    ],
                ];
            }
            $parts[] = ['type' => 'text', 'text' => $prompt];
            $userContent = $parts;
        }

        $payload = [
            'model' => $this->config->getClaudeModel(),
            'max_tokens' => $this->config->getMaxTokens(),
            'temperature' => $this->config->getTemperature(),
            'system' => 'You are a professional web content writer. Return only the requested content — no JSON wrapping, no markdown code fences, no extra commentary.',
            'messages' => [
                ['role' => 'user', 'content' => $userContent],
            ],
        ];

        $response = $this->curlPost(
            'https://api.anthropic.com/v1/messages',
            [
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
                'Content-Type: application/json',
            ],
            $payload
        );

        if ($response['status'] < 200 || $response['status'] >= 300) {
            $decoded = json_decode($response['body'], true);
            $msg = $decoded['error']['message'] ?? ('API returned HTTP ' . $response['status']);
            return ['success' => false, 'content' => '', 'message' => $msg];
        }

        $decoded = json_decode($response['body'], true);
        $text = '';
        foreach (($decoded['content'] ?? []) as $block) {
            if (is_array($block) && ($block['type'] ?? '') === 'text') {
                $text .= ($block['text'] ?? '');
            }
        }

        return ['success' => true, 'content' => trim($text)];
    }

    /**
     * @return array{status: int, body: string}
     */
    private function curlPost(string $url, array $headers, array $payload): array
    {
        // SSRF prevention: only allow known AI API hosts
        $host = parse_url($url, PHP_URL_HOST);
        $allowed = ['api.openai.com', 'api.anthropic.com'];
        if (!in_array($host, $allowed, true)) {
            return ['status' => 0, 'body' => ''];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['status' => $status, 'body' => (string) $body];
    }
}
