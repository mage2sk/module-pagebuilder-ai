<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model\Generator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Model\Generator\ClaudeAdapter;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AbstractHttpAdapterTest extends TestCase
{
    use DbStubs;

    private const URL = 'https://api.openai.com/v1/chat/completions';

    public function testApiKeyIsDecryptedOrFallsBackToRawValue(): void
    {
        $encryptor = $this->createStub(EncryptorInterface::class);
        $encryptor->method('decrypt')->willReturnMap([['enc', 'plain'], ['raw', '']]);
        $adapter = $this->adapter(['k1' => 'enc', 'k2' => 'raw'], null, $encryptor);

        $this->assertSame('plain', $adapter->call('getApiKey', 'k1'));
        $this->assertSame('raw', $adapter->call('getApiKey', 'k2'));
        $this->assertSame('', $adapter->call('getApiKey', 'missing'));
    }

    public function testApiKeyFallsBackToRawValueWhenDecryptionThrows(): void
    {
        $encryptor = $this->createStub(EncryptorInterface::class);
        $encryptor->method('decrypt')->willThrowException(new \RuntimeException('bad'));
        $adapter = $this->adapter(['k' => 'stored'], null, $encryptor);

        $this->assertSame('stored', $adapter->call('getApiKey', 'k'));
    }

    #[DataProvider('temperatureProvider')]
    public function testTemperatureIsClampedToValidRange(mixed $raw, float $expected): void
    {
        $adapter = $this->adapter(['panth_pagebuilderai/ai/temperature' => $raw]);

        $this->assertSame($expected, $adapter->call('getTemperature'));
    }

    public static function temperatureProvider(): array
    {
        return [
            'unset' => [null, 0.4],
            'empty' => ['', 0.4],
            'valid' => ['1.2', 1.2],
            'zero' => ['0', 0.0],
            'too high' => ['2.5', 0.4],
            'negative' => ['-0.1', 0.4],
        ];
    }

    public function testMaxTokensUsesDefaultForNonPositiveValues(): void
    {
        $this->assertSame(600, $this->adapter()->call('getMaxTokens'));
        $this->assertSame(250, $this->adapter(['panth_pagebuilderai/ai/max_tokens' => '-3'])->call('getMaxTokens', 250));
        $this->assertSame(900, $this->adapter(['panth_pagebuilderai/ai/max_tokens' => '900'])->call('getMaxTokens'));
    }

    #[DataProvider('cacheTtlProvider')]
    public function testCacheTtlIsNeverNegative(mixed $raw, int $expected): void
    {
        $adapter = $this->adapter(['panth_pagebuilderai/ai/cache_ttl' => $raw]);

        $this->assertSame($expected, $adapter->call('getCacheTtl'));
    }

    public static function cacheTtlProvider(): array
    {
        return [[null, 0], ['', 0], ['-5', 0], ['30', 30]];
    }

    public function testMonthlyBudgetIsReadAsInteger(): void
    {
        $this->assertSame(5000, $this->adapter(['panth_pagebuilderai/ai/monthly_budget' => '5000'])->call('getMonthlyBudget'));
        $this->assertSame(0, $this->adapter()->call('getMonthlyBudget'));
    }

    public function testHeuristicConfidenceRewardsIdealLengths(): void
    {
        $adapter = $this->adapter();

        $this->assertSame(1.0, $adapter->call('heuristicConfidence', str_repeat('t', 45), str_repeat('d', 140)));
        $this->assertSame(0.0, $adapter->call('heuristicConfidence', '', ''));
        $this->assertSame(0.7, $adapter->call('heuristicConfidence', str_repeat('t', 30), str_repeat('d', 70)));
    }

    public function testPromptHashIsDeterministicSha256(): void
    {
        $adapter = $this->adapter();

        $this->assertSame(hash('sha256', 'p|m|text'), $adapter->call('promptHash', 'p', 'm', 'text'));
        $this->assertNotSame($adapter->call('promptHash', 'p', 'm', 'a'), $adapter->call('promptHash', 'p', 'm', 'b'));
    }

    #[DataProvider('replyProvider')]
    public function testParseJsonReply(string $reply, array $expected): void
    {
        $this->assertSame($expected, $this->adapter()->call('parseJsonReply', $reply));
    }

    public static function replyProvider(): array
    {
        return [
            'fenced' => ["```json\n{\"title\":\"T\",\"description\":\"D\"}\n```", ['title' => 'T', 'description' => 'D']],
            'surrounded by prose' => ['Here: {"title":"T"} thanks', ['title' => 'T', 'description' => '']],
            'not json' => ['nope', ['title' => '', 'description' => '']],
            'multi field' => [
                '{"meta_title":"MT","meta_description":"MD","meta_keywords":"","og_title":"OG","extra":"x"}',
                ['meta_title' => 'MT', 'meta_description' => 'MD', 'og_title' => 'OG', 'title' => 'MT', 'description' => 'MD'],
            ],
            'numeric value' => ['{"meta_title":123}', ['meta_title' => '123', 'title' => '123']],
        ];
    }

    public function testDefaultPromptListsScalarAttributesAndStrippedContent(): void
    {
        $prompt = $this->adapter()->call('buildPrompt', [
            'entity_type' => 'category',
            'attributes' => ['name' => 'Shoes', 'count' => 3, 'empty' => '', 'arr' => ['x']],
            'content' => '<p>' . str_repeat('a', 1500) . '</p>',
        ]);

        $this->assertStringContainsString('following category.', $prompt);
        $this->assertStringContainsString('Name: Shoes', $prompt);
        $this->assertStringContainsString('Count: 3', $prompt);
        $this->assertStringNotContainsString('Empty:', $prompt);
        $this->assertStringNotContainsString('Arr:', $prompt);
        $this->assertStringContainsString(str_repeat('a', 1200), $prompt);
        $this->assertStringNotContainsString(str_repeat('a', 1201), $prompt);
        $this->assertStringNotContainsString('<p>', $prompt);
        $this->assertStringContainsString('--- RULES', $prompt);
        $this->assertStringContainsString('Store Name: N/A', $prompt);
        $this->assertStringContainsString('Currency: USD', $prompt);
        $this->assertStringNotContainsString('Free Shipping', $prompt);
    }

    public function testStoreContextIncludesFreeShippingThreshold(): void
    {
        $adapter = $this->adapter(
            [
                'general/store_information/name' => 'Demo',
                'carriers/freeshipping/free_shipping_subtotal' => '50',
            ],
            null,
            null,
            null,
            ['carriers/freeshipping/active' => true]
        );

        $prompt = $adapter->call('buildPrompt', ['entity_type' => 'product']);

        $this->assertStringContainsString('Store Name: Demo', $prompt);
        $this->assertStringContainsString('Free Shipping: Yes (over 50)', $prompt);
    }

    public function testCustomPromptPlaceholdersAreRendered(): void
    {
        $adapter = $this->adapter(['general/store_information/name' => 'Demo']);

        $prompt = $adapter->call('buildPrompt', [
            'entity_type' => 'product',
            'custom_prompt' => 'N={{name}} S={{sku}} B={{brand}} C={{category}} D={{description}} '
                . 'T={{entity_type}} SN={{store_name}} U={{url}} SD={{short_description}} P={{price}}',
            'attributes' => [
                'name' => 'Lamp',
                'sku' => 'L1',
                'manufacturer' => 'Acme',
                'category_name' => 'Lights',
                'url_key' => 'lamp',
                'short_description' => '<b>Bright</b>',
                'price' => 9.5,
            ],
            'content' => 'Long text',
        ]);

        $this->assertStringStartsWith(
            'N=Lamp S=L1 B=Acme C=Lights D=Long text T=product SN=Demo U=lamp SD=Bright P=9.5',
            $prompt
        );
    }

    public function testRequestedFieldsProduceMultiFieldPrompt(): void
    {
        $prompt = $this->adapter()->call('buildPrompt', [
            'entity_type' => 'product',
            'fields' => ['meta_keywords', 'short_description'],
        ]);

        $this->assertStringContainsString('"meta_keywords": "5-10', $prompt);
        $this->assertStringContainsString('"short_description": "1-2 sentences summarizing the product"', $prompt);
        $this->assertStringNotContainsString('"og_title":', $prompt);
        $this->assertStringNotContainsString('"meta_title":', $prompt);
    }

    public function testUnknownRequestedFieldsFallBackToTitleAndDescription(): void
    {
        $prompt = $this->adapter()->call('buildPrompt', ['entity_type' => 'product', 'fields' => ['bogus']]);

        $this->assertStringContainsString('"meta_title": "50-60', $prompt);
        $this->assertStringContainsString('"meta_description": "140-156', $prompt);
    }

    public function testStoredDefaultTemplateIsUsed(): void
    {
        $connection = $this->connectionStub(['panth_seo_ai_prompt']);
        $connection->method('quote')->willReturnCallback(static fn ($v) => "'" . $v . "'");
        $connection->method('fetchOne')->willReturn('Stored {{name}}');

        $prompt = $this->adapter([], $connection)->call('buildPrompt', [
            'entity_type' => 'product',
            'attributes' => ['name' => 'Lamp'],
        ]);

        $this->assertStringStartsWith('Stored Lamp', $prompt);
    }

    public function testPromptSelectedByIdIsUsed(): void
    {
        $connection = $this->connectionStub(['panth_seo_ai_prompt']);
        $connection->method('fetchOne')->willReturnOnConsecutiveCalls('By id {{sku}}', 'Default');

        $prompt = $this->adapter([], $connection)->call('buildPrompt', [
            'entity_type' => 'product',
            'options' => ['prompt_id' => 5],
            'attributes' => ['sku' => 'L1'],
        ]);

        $this->assertStringStartsWith('By id L1', $prompt);
    }

    public function testTemplateLookupFailureFallsBackToBuiltInPrompt(): void
    {
        $connection = $this->connectionStub(['panth_seo_ai_prompt']);
        $connection->method('quote')->willReturnArgument(0);
        $connection->method('fetchOne')->willThrowException(new \RuntimeException('db down'));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('prompt load failed'));

        $prompt = $this->adapter([], $connection, null, $logger)->call('buildPrompt', ['entity_type' => 'product']);

        $this->assertStringContainsString('Generate a meta title and meta description', $prompt);
    }

    public function testKnowledgeGuidelinesAreRankedTruncatedAndLimited(): void
    {
        $rows = [
            ['category' => 'pagebuilder', 'title' => 'Low', 'content' => 'c', 'tags' => 'misc'],
            ['category' => 'seo', 'title' => 'SeoOnly', 'content' => 'c', 'tags' => ''],
            ['category' => 'ecommerce', 'title' => 'Prod', 'content' => str_repeat('z', 300), 'tags' => 'product,features'],
            ['category' => 'pagebuilder', 'title' => 'Low2', 'content' => 'c', 'tags' => ''],
            ['category' => 'pagebuilder', 'title' => 'Low3', 'content' => 'c', 'tags' => ''],
            ['category' => 'pagebuilder', 'title' => 'Low4', 'content' => 'c', 'tags' => ''],
            ['category' => 'pagebuilder', 'title' => 'Low5', 'content' => 'c', 'tags' => ''],
        ];
        $connection = $this->connectionStub(['panth_seo_ai_knowledge']);
        $connection->method('fetchAll')->willReturn($rows);

        $prompt = $this->adapter([], $connection)->call('buildPrompt', ['entity_type' => 'product']);

        $this->assertStringContainsString('--- GUIDELINES ---', $prompt);
        $this->assertStringContainsString('1. Prod: ' . str_repeat('z', 200) . '...', $prompt);
        $this->assertStringContainsString('2. SeoOnly: c', $prompt);
        $this->assertSame(5, preg_match_all('/^\d+\. /m', $prompt));
        $this->assertStringNotContainsString('Low5', $prompt);
    }

    public function testBlockedHostReturnsStatusZeroWithoutRequest(): void
    {
        $adapter = $this->adapter();

        $response = $adapter->call('curlPost', 'http://example.com/x', [], []);

        $this->assertSame(0, $response['status']);
        $this->assertStringContainsString('disallowed', $response['body']);
        $this->assertSame([], $adapter->requests);
    }

    public function testRetriesOnRateLimitThenSucceeds(): void
    {
        $adapter = $this->adapter();
        $adapter->responses = [$this->response(429, '{}'), $this->response(200, '{"ok":1}')];

        $response = $adapter->call('curlPost', self::URL, ['a' => 'b'], ['x' => 1]);

        $this->assertSame(['status' => 200, 'body' => '{"ok":1}'], $response);
        $this->assertCount(2, $adapter->requests);
        $this->assertSame(['a: b'], $adapter->requests[0]['headers']);
        $this->assertSame('{"x":1}', $adapter->requests[0]['body']);
    }

    public function testClientErrorIsNotRetried(): void
    {
        $adapter = $this->adapter();
        $adapter->responses = [$this->response(400, '{"error":"bad"}'), $this->response(200, '{}')];

        $response = $adapter->call('curlPost', self::URL, [], []);

        $this->assertSame(400, $response['status']);
        $this->assertCount(1, $adapter->requests);
    }

    public function testServerErrorsStopAfterMaxRetries(): void
    {
        $adapter = $this->adapter();
        $adapter->responses = array_fill(0, 5, $this->response(503, 'down'));

        $response = $adapter->call('curlPost', self::URL, [], []);

        $this->assertSame(503, $response['status']);
        $this->assertCount(3, $adapter->requests);
    }

    public function testTransportFailureReportsErrorText(): void
    {
        $adapter = $this->adapter();
        $adapter->responses = array_fill(0, 3, ['body' => null, 'status' => 0, 'content_type' => '', 'error' => 'timeout']);

        $response = $adapter->call('curlPost', self::URL, [], []);

        $this->assertSame(['status' => 0, 'body' => 'timeout'], $response);
    }

    public function testOversizedBodyIsTruncated(): void
    {
        $adapter = $this->adapter();
        $adapter->responses = [$this->response(200, str_repeat('x', 2 * 1024 * 1024 + 10))];

        $response = $adapter->call('curlPost', self::URL, [], []);

        $this->assertSame(2 * 1024 * 1024, strlen($response['body']));
    }

    public function testReserveBudgetSucceedsWhenUsageTableIsMissing(): void
    {
        $this->assertTrue($this->adapter()->call('reserveBudget', 'p', 100, 1000));
    }

    public function testReserveBudgetReflectsAffectedRows(): void
    {
        $inserted = [];
        $connection = $this->connectionStub(['panth_seo_ai_usage']);
        $connection->method('insertOnDuplicate')->willReturnCallback(
            static function ($table, array $row) use (&$inserted) {
                $inserted = $row;
                return 1;
            }
        );
        $connection->method('update')->willReturnOnConsecutiveCalls(1, 0);
        $adapter = $this->adapter([], $connection);

        $this->assertTrue($adapter->call('reserveBudget', 'p', 100, 1000));
        $this->assertFalse($adapter->call('reserveBudget', 'p', 100, 1000));
        $this->assertSame('p', $inserted['provider']);
        $this->assertSame(date('Y-m'), $inserted['period']);
        $this->assertSame(0, $inserted['total_tokens']);
    }

    public function testReserveBudgetFailsClosedOnDatabaseError(): void
    {
        $connection = $this->connectionStub(['panth_seo_ai_usage']);
        $connection->method('insertOnDuplicate')->willThrowException(new \RuntimeException('lock'));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('budget reservation failed'));

        $this->assertFalse($this->adapter([], $connection, null, $logger)->call('reserveBudget', 'p', 1, 10));
    }

    public function testAdjustUsageSkipsZeroDelta(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->never())->method('update');
        $connection->expects($this->never())->method('isTableExists');

        $this->adapter([], $connection)->call('adjustUsage', 'p', 0);
    }

    public function testAdjustUsageAndReleaseBuildExpressions(): void
    {
        $expressions = [];
        $connection = $this->connectionStub(['panth_seo_ai_usage']);
        $connection->method('update')->willReturnCallback(
            static function ($table, array $data, array $where) use (&$expressions) {
                $expressions[] = (string)$data['total_tokens'];
                return 1;
            }
        );
        $adapter = $this->adapter([], $connection);

        $adapter->call('adjustUsage', 'p', 5);
        $adapter->call('releaseBudget', 'p', 7);

        $this->assertSame(['total_tokens + 5', 'GREATEST(0, total_tokens - 7)'], $expressions);
    }

    public function testRecordUsageUpsertsTokens(): void
    {
        $row = [];
        $connection = $this->connectionStub(['panth_seo_ai_usage']);
        $connection->method('insertOnDuplicate')->willReturnCallback(
            static function ($table, array $data) use (&$row) {
                $row = $data;
                return 1;
            }
        );

        $this->adapter([], $connection)->call('recordUsage', 'p', 12);

        $this->assertSame('p', $row['provider']);
        $this->assertSame(12, $row['total_tokens']);
        $this->assertSame('2026-01-01 00:00:00', $row['created_at']);
    }

    public function testMonthlyUsageReadsAggregatedTokens(): void
    {
        $this->assertSame(0, $this->adapter()->call('getMonthlyUsage', 'p'));

        $connection = $this->connectionStub(['panth_seo_ai_usage']);
        $connection->method('fetchRow')->willReturn(['used' => '42']);

        $this->assertSame(42, $this->adapter([], $connection)->call('getMonthlyUsage', 'p'));
    }

    public function testCacheDisabledSkipsDatabase(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->never())->method('isTableExists');
        $adapter = $this->adapter([], $connection);

        $this->assertNull($adapter->call('loadCached', 'hash'));
        $adapter->call('saveCached', 'hash', ['title' => 'T']);
    }

    public function testLoadCachedDecodesStoredResponse(): void
    {
        $connection = $this->connectionStub(['panth_seo_ai_cache']);
        $connection->method('fetchRow')->willReturnOnConsecutiveCalls(['response' => '{"title":"T"}'], ['response' => 'xx'], false);
        $adapter = $this->adapter(['panth_pagebuilderai/ai/cache_ttl' => '60'], $connection);

        $this->assertSame(['title' => 'T'], $adapter->call('loadCached', 'hash'));
        $this->assertNull($adapter->call('loadCached', 'hash'));
        $this->assertNull($adapter->call('loadCached', 'hash'));
    }

    public function testSaveCachedWritesExpiry(): void
    {
        $row = [];
        $connection = $this->connectionStub(['panth_seo_ai_cache']);
        $connection->method('insertOnDuplicate')->willReturnCallback(
            static function ($table, array $data) use (&$row) {
                $row = $data;
                return 1;
            }
        );
        $before = time();

        $this->adapter(['panth_pagebuilderai/ai/cache_ttl' => '60'], $connection)
            ->call('saveCached', 'hash', ['title' => 'T'], 'prov');

        $this->assertSame('hash', $row['cache_key']);
        $this->assertSame('prov', $row['provider']);
        $this->assertSame('{"title":"T"}', $row['response']);
        $this->assertGreaterThanOrEqual($before + 60, $row['expires_at']);
    }

    private function response(int $status, string $body): array
    {
        return ['body' => $body, 'status' => $status, 'content_type' => 'application/json', 'error' => ''];
    }

    private function adapter(
        array $config = [],
        ?AdapterInterface $connection = null,
        ?EncryptorInterface $encryptor = null,
        ?LoggerInterface $logger = null,
        array $flags = []
    ): object {
        $scope = $this->createStub(ScopeConfigInterface::class);
        $scope->method('getValue')->willReturnCallback(static fn ($path) => $config[$path] ?? null);
        $scope->method('isSetFlag')->willReturnCallback(static fn ($path) => (bool)($flags[$path] ?? false));
        if ($encryptor === null) {
            $encryptor = $this->createStub(EncryptorInterface::class);
            $encryptor->method('decrypt')->willReturnArgument(0);
        }
        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturn('2026-01-01 00:00:00');

        return new class (
            $scope,
            $encryptor,
            $this->resourceStub($connection ?? $this->connectionStub()),
            $dateTime,
            $logger ?? new NullLogger()
        ) extends ClaudeAdapter {
            protected const BACKOFF_BASE_MS = 1;

            public array $responses = [];

            public array $requests = [];

            public function call(string $method, mixed ...$args): mixed
            {
                return $this->$method(...$args);
            }

            protected function executeRequest(string $url, array $headers, string $body, int $timeout): array
            {
                $this->requests[] = ['url' => $url, 'headers' => $headers, 'body' => $body];
                return array_shift($this->responses)
                    ?? ['body' => null, 'status' => 0, 'content_type' => '', 'error' => 'no response'];
            }
        };
    }
}
