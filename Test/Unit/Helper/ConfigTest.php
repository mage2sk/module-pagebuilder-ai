<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Panth\PageBuilderAi\Helper\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testDefaultsWhenNothingConfigured(): void
    {
        $config = $this->config([]);

        $this->assertFalse($config->isEnabled());
        $this->assertSame('', $config->getProvider());
        $this->assertSame('', $config->getOpenAiApiKey());
        $this->assertSame('', $config->getClaudeApiKey());
        $this->assertSame('gpt-4o', $config->getOpenAiModel());
        $this->assertSame('claude-sonnet-4-6', $config->getClaudeModel());
        $this->assertSame(8192, $config->getMaxTokens());
        $this->assertSame(0.7, $config->getTemperature());
        $this->assertSame(0, $config->getMonthlyBudget());
        $this->assertSame(0, $config->getCacheTtl());
        $this->assertSame('professional', $config->getTone());
        $this->assertFalse($config->hasOwnApiKey());
        $this->assertFalse($config->isAdvancedSeoAvailable());
        $this->assertFalse($config->isAiAvailable());
        $this->assertSame('none', $config->getActiveBackend());
    }

    public function testConfiguredValuesAreReturned(): void
    {
        $config = $this->config([
            Config::XML_ENABLED => '1',
            Config::XML_PROVIDER => 'openai',
            Config::XML_OPENAI_KEY => 'enc-o',
            Config::XML_OPENAI_MODEL => 'm-o',
            Config::XML_CLAUDE_KEY => 'enc-c',
            Config::XML_CLAUDE_MODEL => 'm-c',
            Config::XML_MAX_TOKENS => '1000',
            Config::XML_TEMPERATURE => '0',
            Config::XML_MONTHLY_BUDGET => '5000',
            Config::XML_CACHE_TTL => '-10',
            Config::XML_TONE => 'friendly',
        ]);

        $this->assertTrue($config->isEnabled());
        $this->assertSame('openai', $config->getProvider());
        $this->assertSame('dec(enc-o)', $config->getOpenAiApiKey());
        $this->assertSame('dec(enc-c)', $config->getClaudeApiKey());
        $this->assertSame('m-o', $config->getOpenAiModel());
        $this->assertSame('m-c', $config->getClaudeModel());
        $this->assertSame(1000, $config->getMaxTokens());
        $this->assertSame(0.0, $config->getTemperature());
        $this->assertSame(5000, $config->getMonthlyBudget());
        $this->assertSame(0, $config->getCacheTtl());
        $this->assertSame('friendly', $config->getTone());
        $this->assertTrue($config->isAiAvailable());
        $this->assertSame('own', $config->getActiveBackend());
    }

    #[DataProvider('keyAvailabilityProvider')]
    public function testOwnApiKeyDependsOnSelectedProvider(array $values, bool $hasKey, bool $available): void
    {
        $config = $this->config($values);

        $this->assertSame($hasKey, $config->hasOwnApiKey());
        $this->assertSame($available, $config->isAiAvailable());
    }

    public static function keyAvailabilityProvider(): array
    {
        return [
            'second provider with key' => [
                [Config::XML_PROVIDER => 'claude', Config::XML_CLAUDE_KEY => 'k', Config::XML_ENABLED => '1'],
                true,
                true,
            ],
            'second provider without its key' => [
                [Config::XML_PROVIDER => 'claude', Config::XML_OPENAI_KEY => 'k', Config::XML_ENABLED => '1'],
                false,
                false,
            ],
            'key present but module disabled' => [
                [Config::XML_PROVIDER => 'openai', Config::XML_OPENAI_KEY => 'k'],
                true,
                false,
            ],
            'null provider' => [
                [Config::XML_PROVIDER => 'null', Config::XML_OPENAI_KEY => 'k', Config::XML_ENABLED => '1'],
                false,
                false,
            ],
        ];
    }

    public function testCacheTtlPositiveValue(): void
    {
        $this->assertSame(300, $this->config([Config::XML_CACHE_TTL => '300'])->getCacheTtl());
    }

    private function config(array $values): Config
    {
        $scope = $this->createStub(ScopeConfigInterface::class);
        $scope->method('getValue')->willReturnCallback(static fn ($path) => $values[$path] ?? null);
        $scope->method('isSetFlag')->willReturnCallback(static fn ($path) => !empty($values[$path]));
        $context = $this->createStub(Context::class);
        $context->method('getScopeConfig')->willReturn($scope);
        $encryptor = $this->createStub(EncryptorInterface::class);
        $encryptor->method('decrypt')->willReturnCallback(static fn ($v) => 'dec(' . $v . ')');

        return new Config($context, $encryptor, $this->createStub(ModuleManager::class));
    }
}
