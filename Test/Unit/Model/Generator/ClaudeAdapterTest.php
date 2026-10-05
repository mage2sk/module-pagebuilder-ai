<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model\Generator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Model\Generator\ClaudeAdapter;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ClaudeAdapterTest extends TestCase
{
    use DbStubs;

    private const KEY_PATH = 'panth_pagebuilderai/ai/claude_api_key';
    private const BUDGET_PATH = 'panth_pagebuilderai/ai/monthly_budget';
    private const EMPTY = ['title' => '', 'description' => '', 'confidence' => 0.0];

    public function testProviderCode(): void
    {
        $this->assertSame('claude', $this->adapter()->getProvider());
    }

    public function testMissingApiKeyShortCircuits(): void
    {
        $adapter = $this->adapter([self::BUDGET_PATH => '1000']);

        $this->assertSame(self::EMPTY, $adapter->generate(['entity_type' => 'product']));
        $this->assertSame([], $adapter->requests);
    }

    public function testZeroBudgetIsRejected(): void
    {
        $adapter = $this->adapter([self::KEY_PATH => 'key']);

        $result = $adapter->generate(['entity_type' => 'product']);

        $this->assertSame('budget_not_configured', $result['error']);
        $this->assertSame([], $adapter->requests);
    }

    public function testExhaustedBudgetIsRejected(): void
    {
        $connection = $this->connectionStub(['panth_seo_ai_usage']);
        $connection->method('update')->willReturn(0);
        $adapter = $this->adapter([self::KEY_PATH => 'key', self::BUDGET_PATH => '10'], $connection);

        $result = $adapter->generate(['entity_type' => 'product']);

        $this->assertSame('budget_exhausted', $result['error']);
        $this->assertSame([], $adapter->requests);
    }

    public function testSuccessfulReplyIsParsedAndUsageTracked(): void
    {
        $title = str_repeat('T', 45);
        $description = str_repeat('D', 140);
        $adapter = $this->adapter([self::KEY_PATH => 'key', self::BUDGET_PATH => '10000']);
        $adapter->responses = [$this->ok([
            'content' => [
                ['type' => 'text', 'text' => '{"meta_title":"' . $title . '",'],
                ['type' => 'other', 'text' => 'ignored'],
                ['type' => 'text', 'text' => '"meta_description":"' . $description . '"}'],
            ],
            'usage' => ['input_tokens' => 10, 'output_tokens' => 5],
        ])];

        $result = $adapter->generate(['entity_type' => 'product', 'attributes' => ['name' => 'Lamp']]);

        $this->assertSame($title, $result['title']);
        $this->assertSame($title, $result['meta_title']);
        $this->assertSame($description, $result['description']);
        $this->assertSame(1.0, $result['confidence']);
        $this->assertSame(15, $adapter->getLastUsageTokens());

        $this->assertCount(1, $adapter->requests);
        $this->assertContains('x-api-key: key', $adapter->requests[0]['headers']);
        $payload = json_decode($adapter->requests[0]['body'], true);
        $this->assertSame('claude-sonnet-4-6', $payload['model']);
        $this->assertSame(600, $payload['max_tokens']);
        $this->assertSame(0.4, $payload['temperature']);
        $this->assertIsString($payload['messages'][0]['content']);
        $this->assertStringContainsString('Name: Lamp', $payload['messages'][0]['content']);
    }

    public function testConfiguredModelAndFieldsAreSent(): void
    {
        $adapter = $this->adapter([
            self::KEY_PATH => 'key',
            self::BUDGET_PATH => '10000',
            'panth_pagebuilderai/ai/claude_model' => 'model-x',
            'panth_pagebuilderai/ai/max_tokens' => '100',
        ]);
        $adapter->responses = [$this->ok(['content' => [['type' => 'text', 'text' => '{"title":"A","description":"B"}']]])];

        $result = $adapter->generate(['entity_type' => 'product'], ['meta_keywords']);

        $payload = json_decode($adapter->requests[0]['body'], true);
        $this->assertSame('model-x', $payload['model']);
        $this->assertSame(100, $payload['max_tokens']);
        $this->assertStringContainsString('"meta_keywords": "5-10', $payload['messages'][0]['content']);
        $this->assertSame('A', $result['title']);
        $this->assertSame('B', $result['description']);
    }

    public function testImagesAreSentAsBase64Blocks(): void
    {
        $adapter = $this->adapter([self::KEY_PATH => 'key', self::BUDGET_PATH => '10000']);
        $adapter->responses = [$this->ok(['content' => [['type' => 'text', 'text' => '{"title":"A"}']]])];
        $images = array_merge(['data:image/png;base64,AAAA', 'BBBB'], array_fill(0, 5, 'CCCC'));

        $adapter->generate(['entity_type' => 'product', 'images' => $images]);

        $content = json_decode($adapter->requests[0]['body'], true)['messages'][0]['content'];
        $this->assertCount(6, $content);
        $this->assertSame(['type' => 'base64', 'media_type' => 'image/png', 'data' => 'AAAA'], $content[0]['source']);
        $this->assertSame(['type' => 'base64', 'media_type' => 'image/jpeg', 'data' => 'BBBB'], $content[1]['source']);
        $this->assertSame('text', $content[5]['type']);
    }

    public function testHttpFailureReturnsEmptyResult(): void
    {
        $adapter = $this->adapter([self::KEY_PATH => 'key', self::BUDGET_PATH => '10000']);
        $adapter->responses = [['body' => '{"error":{}}', 'status' => 401, 'content_type' => 'application/json', 'error' => '']];

        $this->assertSame(self::EMPTY, $adapter->generate(['entity_type' => 'product']));
        $this->assertCount(1, $adapter->requests);
    }

    public function testUnexpectedStructureReturnsEmptyResult(): void
    {
        $adapter = $this->adapter([self::KEY_PATH => 'key', self::BUDGET_PATH => '10000']);
        $adapter->responses = [$this->ok(['foo' => 1])];

        $this->assertSame(self::EMPTY, $adapter->generate(['entity_type' => 'product']));
    }

    public function testPlainTextReplyIsReturnedAsContent(): void
    {
        $adapter = $this->adapter([self::KEY_PATH => 'key', self::BUDGET_PATH => '10000']);
        $adapter->responses = [$this->ok(['content' => [['type' => 'text', 'text' => "  Just words \n"]]])];

        $this->assertSame(
            ['title' => '', 'description' => '', 'content' => 'Just words', 'confidence' => 0.0],
            $adapter->generate(['entity_type' => 'product'])
        );
    }

    public function testCachedResponseIsReturnedWithoutRequest(): void
    {
        $connection = $this->connectionStub(['panth_seo_ai_cache']);
        $connection->method('fetchRow')->willReturn(['response' => '{"title":"Cached","confidence":"0.5"}']);
        $adapter = $this->adapter(
            [self::KEY_PATH => 'key', self::BUDGET_PATH => '10000', 'panth_pagebuilderai/ai/cache_ttl' => '60'],
            $connection
        );

        $result = $adapter->generate(['entity_type' => 'product']);

        $this->assertSame('Cached', $result['title']);
        $this->assertSame(0.5, $result['confidence']);
        $this->assertSame([], $adapter->requests);
    }

    private function ok(array $body): array
    {
        return ['body' => json_encode($body), 'status' => 200, 'content_type' => 'application/json', 'error' => ''];
    }

    private function adapter(array $config = [], ?AdapterInterface $connection = null): object
    {
        $scope = $this->createStub(ScopeConfigInterface::class);
        $scope->method('getValue')->willReturnCallback(static fn ($path) => $config[$path] ?? null);
        $encryptor = $this->createStub(EncryptorInterface::class);
        $encryptor->method('decrypt')->willReturnArgument(0);
        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturn('2026-01-01 00:00:00');

        return new class (
            $scope,
            $encryptor,
            $this->resourceStub($connection ?? $this->connectionStub()),
            $dateTime,
            new NullLogger()
        ) extends ClaudeAdapter {
            protected const BACKOFF_BASE_MS = 1;

            public array $responses = [];

            public array $requests = [];

            protected function executeRequest(string $url, array $headers, string $body, int $timeout): array
            {
                $this->requests[] = ['url' => $url, 'headers' => $headers, 'body' => $body];
                return array_shift($this->responses)
                    ?? ['body' => null, 'status' => 0, 'content_type' => '', 'error' => 'no response'];
            }
        };
    }
}
