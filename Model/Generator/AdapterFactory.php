<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\Generator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Panth\PageBuilderAi\Api\AiGeneratorInterface;

/**
 * Resolves the correct AI content-generation adapter based on configuration.
 * Returns NullAdapter when AI is disabled or provider is not configured.
 *
 * Legitimate ObjectManager usage: this is a factory class whose target
 * concrete type is chosen at runtime from a store-scope configuration value.
 */
class AdapterFactory implements AiGeneratorInterface
{
    private ?AiGeneratorInterface $resolved = null;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly ObjectManagerInterface $objectManager
    ) {
    }

    public function getProvider(): string
    {
        return $this->resolve()->getProvider();
    }

    public function generate(array $context, array $fields = [], array $options = []): array
    {
        return $this->resolve()->generate($context, $fields, $options);
    }

    public function getLastUsageTokens(): int
    {
        return $this->resolve()->getLastUsageTokens();
    }

    private function resolve(): AiGeneratorInterface
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $provider = (string) $this->scopeConfig->getValue('panth_pagebuilderai/ai/provider', ScopeInterface::SCOPE_STORE);

        $this->resolved = match ($provider) {
            'claude' => $this->objectManager->get(ClaudeAdapter::class),
            'openai' => $this->objectManager->get(OpenAiAdapter::class),
            default => $this->objectManager->get(NullAdapter::class),
        };

        return $this->resolved;
    }

    /**
     * Explicitly request a particular adapter by provider key. Useful for
     * delegating from AiService when the caller has its own provider pick.
     */
    public function get(string $provider): AiGeneratorInterface
    {
        return match ($provider) {
            'claude' => $this->objectManager->get(ClaudeAdapter::class),
            'openai' => $this->objectManager->get(OpenAiAdapter::class),
            default => $this->objectManager->get(NullAdapter::class),
        };
    }
}
