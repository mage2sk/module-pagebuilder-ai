<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model\Generator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Model\Generator\OpenAiAdapter;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class OpenAiAdapterTest extends TestCase
{
    use DbStubs;

    private const KEY_PATH = 'panth_pagebuilderai/ai/openai_api_key';
    private const BUDGET_PATH = 'panth_pagebuilderai/ai/monthly_budget';
    private const EMPTY = ['title' => '', 'description' => '', 'confidence' => 0.0];

    public function testProviderCode(): void
    {
        $this->assertSame('openai', $this->adapter()->getProvider());
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

        $this->assertSame('budget_not_configured', $adapter->generate([])['error']);
        $this->assertSame([], $adapter->requests);
    }

    public function testExhaustedBudgetIsRejected(): void
    {
        $connection = $this->connectionStub(['panth_seo_ai_usage']);
        $connection->method('update')->willReturn(0);
        $adapter = $this->adapter([self::KEY_PATH => 'key', self::BUDGET_PATH => '10'], $connection);

        $this->assertSame('budget_exhausted', $adapter->generate([])['error']);
        $this->assertSame([], $adapter->requests);
    }

    public function testLogprobsDriveConfidence(): void
    {
        $adapter = $this->adapter([self::KEY_PATH => 'key', self::BUDGET_PATH => '10000']);
        $adapter->responses = [$this->ok([
            'choices' => [[
                'message' => ['content' => '{"title":"A","description":"B"}'],
                'logprobs' => ['content' => [['logprob' => 0.0], ['logprob' => log(0.5)], ['token' => 'x']]],
            ]],
            'usage' => ['total_tokens' => 33],
        ])];

        $result = $adapter->generate(['entity_type' => 'product']);

        $this->assertSame('A', $result['title']);
        $this->assertSame('B', $result['description']);
        $this->assertSame(0.75, $result['confidence']);
        $this->assertSame(33, $adapter->getLastUsageTokens());

        $this->assertContains('authorization: Bearer key', $adapter->requests[0]['headers']);
        $payload = json_decode($adapter->requests[0]['body'], true);
        $this->assertSame('gpt-4o', $payload['model']);
        $this->assertSame(['type' => 'json_object'], $payload['response_format']);
        $this->assertTrue($payload['logprobs']);
        $this->assertSame('system', $payload['messages'][0]['role']);
        $this->assertIsString($payload['messages'][1]['content']);
    }

    public function testHeuristicConfidenceIsUsedWithoutLogprobs(): void
    {
        $title = str_repeat('T', 45);
        $description = str_repeat('D', 140);
        $adapter = $this->adapter([
            self::KEY_PATH => 'key',
            self::BUDGET_PATH => '10000',
            'panth_pagebuilderai/ai/openai_model' => 'model-y',
        ]);
        $adapter->responses = [$this->ok([
            'choices' => [['message' => [
                'content' => '{"meta_title":"' . $title . '","meta_description":"' . $description . '"}',
            ]]],
        ])];

        $result = $adapter->generate(['entity_type' => 'product']);

        $this->assertSame(1.0, $result['confidence']);
        $this->assertSame($title, $result['meta_title']);
        $this->assertSame($title, $result['title']);
        $this->assertSame('model-y', json_decode($adapter->requests[0]['body'], true)['model']);
    }

    public function testImagesAreSentAsDataUrls(): void
    {
        $adapter = $this->adapter([self::KEY_PATH => 'key', self::BUDGET_PATH => '10000']);
        $adapter->responses = [$this->ok(['choices' => [['message' => ['content' => '{"title":"A"}']]]])];

        $adapter->generate(['images' => ['data:image/png;base64,AAAA', 'BBBB']]);

        $content = json_decode($adapter->requests[0]['body'], true)['messages'][1]['content'];
        $this->assertCount(3, $content);
        $this->assertSame('data:image/png;base64,AAAA', $content[0]['image_url']['url']);
        $this->assertSame('data:image/jpeg;base64,BBBB', $content[1]['image_url']['url']);
        $this->assertSame('text', $content[2]['type']);
    }

    public function testHttpFailureReturnsEmptyResult(): void
    {
        $adapter = $this->adapter([self::KEY_PATH => 'key', self::BUDGET_PATH => '10000']);
        $adapter->responses = [['body' => '{}', 'status' => 403, 'content_type' => 'application/json', 'error' => '']];

        $this->assertSame(self::EMPTY, $adapter->generate([]));
    }

    public function testUnexpectedStructureReturnsEmptyResult(): void
    {
        $adapter = $this->adapter([self::KEY_PATH => 'key', self::BUDGET_PATH => '10000']);
        $adapter->responses = [$this->ok(['content' => []])];

        $this->assertSame(self::EMPTY, $adapter->generate([]));
    }

    public function testPlainTextReplyIsReturnedAsContent(): void
    {
        $adapter = $this->adapter([self::KEY_PATH => 'key', self::BUDGET_PATH => '10000']);
        $adapter->responses = [$this->ok(['choices' => [['message' => ['content' => ' words ']]]])];

        $this->assertSame(
            ['title' => '', 'description' => '', 'content' => 'words', 'confidence' => 0.0],
            $adapter->generate([])
        );
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
        ) extends OpenAiAdapter {
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
