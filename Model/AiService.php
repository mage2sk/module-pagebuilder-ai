<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model;

use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\Generator\AdapterFactory;
use Psr\Log\LoggerInterface;

/**
 * Thin public entry point for PageBuilder-toolbar AI generation.
 *
 * Historically this class called the OpenAI / Claude HTTP APIs inline. After
 * the merge from Panth_AdvancedSEO, it simply delegates to AdapterFactory so
 * there is exactly ONE adapter implementation per provider.
 */
class AiService
{
    public function __construct(
        private readonly Config $config,
        private readonly AdapterFactory $adapterFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Generate content using the configured AI provider.
     *
     * @param string             $prompt  User-provided prompt / brief.
     * @param array<int,string>  $images  Up to 5 base64-encoded images.
     * @return array{success: bool, content: string, message?: string, provider?: string, tokens_used?: int}
     */
    public function generate(string $prompt, array $images = []): array
    {
        $provider = $this->config->getProvider();

        if ($provider !== 'openai' && $provider !== 'claude') {
            return ['success' => false, 'content' => '', 'message' => 'No AI provider configured.'];
        }

        try {
            $adapter = $this->adapterFactory->get($provider);

            $context = [
                'entity_type'   => 'pagebuilder',
                'custom_prompt' => $prompt,
                'attributes'    => [],
                'content'       => '',
            ];
            if (!empty($images)) {
                $context['images'] = array_slice(array_filter($images, 'is_string'), 0, 5);
            }

            $result = $adapter->generate($context, [], []);

            // Adapters may return content under a number of keys. Prefer a
            // dedicated "content" key (PageBuilder full-page HTML), then fall
            // back to description / meta_description / title / meta_title.
            $content = (string)(
                $result['content']
                ?? $result['description']
                ?? $result['meta_description']
                ?? $result['title']
                ?? $result['meta_title']
                ?? ''
            );

            if ($content === '') {
                $errorKey = (string)($result['error'] ?? '');
                $message = match ($errorKey) {
                    'budget_not_configured' => 'Monthly token budget is 0. Set it in PageBuilder AI configuration.',
                    'budget_exhausted'      => 'Monthly AI token budget exhausted. Raise the limit in configuration.',
                    default                 => 'AI generation returned no content.',
                };
                return [
                    'success' => false,
                    'content' => '',
                    'message' => $message,
                    'provider' => $adapter->getProvider(),
                ];
            }

            return [
                'success'     => true,
                'content'     => $content,
                'provider'    => $adapter->getProvider(),
                'tokens_used' => $adapter->getLastUsageTokens(),
            ];
        } catch (\Throwable $e) {
            $this->logger->error('Panth PageBuilderAi: AI generation failed', [
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'content' => '', 'message' => 'AI generation failed. Check system logs.'];
        }
    }
}
