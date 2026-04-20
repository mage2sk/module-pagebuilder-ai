<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Module\Manager as ModuleManager;

class Config extends AbstractHelper
{
    public const XML_ENABLED = 'panth_pagebuilderai/general/enabled';
    public const XML_PROVIDER = 'panth_pagebuilderai/ai/provider';
    public const XML_OPENAI_KEY = 'panth_pagebuilderai/ai/openai_api_key';
    public const XML_OPENAI_MODEL = 'panth_pagebuilderai/ai/openai_model';
    public const XML_CLAUDE_KEY = 'panth_pagebuilderai/ai/claude_api_key';
    public const XML_CLAUDE_MODEL = 'panth_pagebuilderai/ai/claude_model';
    public const XML_MAX_TOKENS = 'panth_pagebuilderai/ai/max_tokens';
    public const XML_TEMPERATURE = 'panth_pagebuilderai/ai/temperature';
    public const XML_MONTHLY_BUDGET = 'panth_pagebuilderai/ai/monthly_budget';
    public const XML_CACHE_TTL = 'panth_pagebuilderai/ai/cache_ttl';
    public const XML_TONE = 'panth_pagebuilderai/ai/tone';

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
     * Monthly token budget cap for AI generation. 0 means "disabled — reject
     * all AI calls." The budget is enforced atomically in AbstractHttpAdapter.
     */
    public function getMonthlyBudget(): int
    {
        return (int) ($this->scopeConfig->getValue(self::XML_MONTHLY_BUDGET) ?: 0);
    }

    /**
     * Response cache TTL in seconds. 0 disables caching entirely.
     */
    public function getCacheTtl(): int
    {
        $raw = $this->scopeConfig->getValue(self::XML_CACHE_TTL);
        if ($raw === null || $raw === '') {
            return 0;
        }
        return max(0, (int) $raw);
    }

    /**
     * Writing tone (used inside AI prompt templates where applicable).
     */
    public function getTone(): string
    {
        return (string) ($this->scopeConfig->getValue(self::XML_TONE) ?: 'professional');
    }

    /**
     * Check if this module has its own API key configured for the selected provider.
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
     * @deprecated PageBuilderAi now owns the AI backend — AdvancedSEO integration is gone.
     *             Kept for backward compatibility with any external callers; always returns false.
     */
    public function isAdvancedSeoAvailable(): bool
    {
        return false;
    }

    /**
     * True if AI is configured and usable.
     */
    public function isAiAvailable(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }
        return $this->hasOwnApiKey();
    }

    /**
     * Which backend is active. After the merge the only possible non-"none"
     * value is "own". Retained for any callers that dispatched on backend.
     */
    public function getActiveBackend(): string
    {
        return $this->hasOwnApiKey() ? 'own' : 'none';
    }
}
