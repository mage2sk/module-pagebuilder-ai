<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Module\Manager as ModuleManager;

class Config extends AbstractHelper
{
    private const XML_ENABLED = 'panth_pagebuilderai/general/enabled';
    private const XML_PROVIDER = 'panth_pagebuilderai/ai/provider';
    private const XML_OPENAI_KEY = 'panth_pagebuilderai/ai/openai_api_key';
    private const XML_OPENAI_MODEL = 'panth_pagebuilderai/ai/openai_model';
    private const XML_CLAUDE_KEY = 'panth_pagebuilderai/ai/claude_api_key';
    private const XML_CLAUDE_MODEL = 'panth_pagebuilderai/ai/claude_model';
    private const XML_MAX_TOKENS = 'panth_pagebuilderai/ai/max_tokens';
    private const XML_TEMPERATURE = 'panth_pagebuilderai/ai/temperature';

    public function __construct(
        Context $context,
        private readonly EncryptorInterface $encryptor,
        private readonly ModuleManager $moduleManager
    ) {
        parent::__construct($context);
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_ENABLED);
    }

    public function getProvider(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PROVIDER);
    }

    public function getOpenAiApiKey(): string
    {
        $raw = (string) $this->scopeConfig->getValue(self::XML_OPENAI_KEY);
        return $raw !== '' ? $this->encryptor->decrypt($raw) : '';
    }

    public function getOpenAiModel(): string
    {
        return (string) ($this->scopeConfig->getValue(self::XML_OPENAI_MODEL) ?: 'gpt-4o');
    }

    public function getClaudeApiKey(): string
    {
        $raw = (string) $this->scopeConfig->getValue(self::XML_CLAUDE_KEY);
        return $raw !== '' ? $this->encryptor->decrypt($raw) : '';
    }

    public function getClaudeModel(): string
    {
        return (string) ($this->scopeConfig->getValue(self::XML_CLAUDE_MODEL) ?: 'claude-sonnet-4-5-20241022');
    }

    public function getMaxTokens(): int
    {
        return (int) ($this->scopeConfig->getValue(self::XML_MAX_TOKENS) ?: 2048);
    }

    public function getTemperature(): float
    {
        $val = $this->scopeConfig->getValue(self::XML_TEMPERATURE);
        return $val !== null && $val !== '' ? (float) $val : 0.7;
    }

    /**
     * Check if this module has its own API key configured.
     */
    public function hasOwnApiKey(): bool
    {
        $provider = $this->getProvider();
        if ($provider === 'openai') {
            return $this->getOpenAiApiKey() !== '';
        }
        if ($provider === 'claude') {
            return $this->getClaudeApiKey() !== '';
        }
        return false;
    }

    /**
     * Check if AdvancedSEO module is available with AI enabled.
     */
    public function isAdvancedSeoAvailable(): bool
    {
        if (!$this->moduleManager->isEnabled('Panth_AdvancedSEO')) {
            return false;
        }
        $helperClass = 'Panth\AdvancedSEO\Helper\Config';
        if (!class_exists($helperClass)) {
            return false;
        }
        try {
            $seoConfig = \Magento\Framework\App\ObjectManager::getInstance()->get($helperClass);
            return (bool) $seoConfig->isAiEnabled();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Returns true if AI is available via either own config or AdvancedSEO.
     */
    public function isAiAvailable(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }
        return $this->hasOwnApiKey() || $this->isAdvancedSeoAvailable();
    }

    /**
     * Determine which backend to use: 'own' or 'advancedseo'.
     */
    public function getActiveBackend(): string
    {
        if ($this->hasOwnApiKey()) {
            return 'own';
        }
        if ($this->isAdvancedSeoAvailable()) {
            return 'advancedseo';
        }
        return 'none';
    }
}
