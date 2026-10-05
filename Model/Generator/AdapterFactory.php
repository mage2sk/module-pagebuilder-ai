<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\Generator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Panth\PageBuilderAi\Api\AiGeneratorInterface;

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

    public function get(string $provider): AiGeneratorInterface
    {
        return match ($provider) {
            'claude' => $this->objectManager->get(ClaudeAdapter::class),
            'openai' => $this->objectManager->get(OpenAiAdapter::class),
            default => $this->objectManager->get(NullAdapter::class),
        };
    }
}
