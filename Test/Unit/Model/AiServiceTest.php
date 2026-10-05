<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model;

use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\AiService;
use Panth\PageBuilderAi\Model\RequestLogger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AiServiceTest extends TestCase
{
    public function testUnconfiguredProviderFailsAndIsLogged(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getProvider')->willReturn('');
        $recorded = null;
        $logger = $this->createMock(RequestLogger::class);
        $logger->expects($this->once())->method('record')->willReturnCallback(
            static function (array $data) use (&$recorded) {
                $recorded = $data;
            }
        );

        $service = new AiService($config, new NullLogger(), $logger);
        $result = $service->generate('Write text', ['img1', 'img2'], ['entity_type' => 'product', 'entity_id' => 4]);

        $this->assertFalse($result['success']);
        $this->assertSame('', $result['content']);
        $this->assertSame('No AI provider configured.', $result['message']);
        $this->assertSame('Write text', $recorded['prompt']);
        $this->assertSame(2, $recorded['image_count']);
        $this->assertSame(['img1', 'img2'], $recorded['images']);
        $this->assertFalse($recorded['success']);
        $this->assertSame('No AI provider configured.', $recorded['error_message']);
        $this->assertSame('product', $recorded['entity_type']);
        $this->assertSame(4, $recorded['entity_id']);
        $this->assertNull($recorded['tokens_used']);
        $this->assertIsInt($recorded['latency_ms']);
    }

    #[DataProvider('providerProvider')]
    public function testMissingApiKeyFailsWithoutRequest(string $provider): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getProvider')->willReturn($provider);
        $config->method('getOpenAiApiKey')->willReturn('');
        $config->method('getClaudeApiKey')->willReturn('');

        $service = new AiService($config, new NullLogger(), $this->createStub(RequestLogger::class));
        $result = $service->generate('prompt');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('API key not configured', $result['message']);
    }

    public static function providerProvider(): array
    {
        return [['openai'], ['claude']];
    }

    public function testExceptionIsCaughtAndReported(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getProvider')->willReturn('openai');
        $config->method('getOpenAiApiKey')->willThrowException(new \RuntimeException('decrypt failed'));
        $errorLogger = $this->createMock(LoggerInterface::class);
        $errorLogger->expects($this->once())->method('error')->with(
            $this->anything(),
            ['error' => 'decrypt failed']
        );
        $recorded = null;
        $requestLogger = $this->createStub(RequestLogger::class);
        $requestLogger->method('record')->willReturnCallback(
            static function (array $data) use (&$recorded) {
                $recorded = $data;
            }
        );

        $result = (new AiService($config, $errorLogger, $requestLogger))->generate('prompt');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Check system logs', $result['message']);
        $this->assertSame($result['message'], $recorded['error_message']);
    }
}
