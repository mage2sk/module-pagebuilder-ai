<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model\Generator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Model\Generator\ClaudeAdapter;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class HttpAdapterTimeoutTest extends TestCase
{
    public function testHangingProviderIsBoundedByTotalTimeout(): void
    {
        $adapter = $this->buildAdapter(true);

        $started = microtime(true);
        $response = $adapter->post('https://api.anthropic.com/v1/messages');
        $elapsed = microtime(true) - $started;

        $this->assertSame(0, $response['status']);
        $this->assertLessThan(4.5, $elapsed);
        $this->assertNotEmpty($adapter->timeouts);
        foreach ($adapter->timeouts as $timeout) {
            $this->assertLessThanOrEqual(3, $timeout);
            $this->assertGreaterThanOrEqual(1, $timeout);
        }
    }

    public function testDisallowedHostOrSchemeIsBlocked(): void
    {
        $adapter = $this->buildAdapter(false);

        $this->assertSame(0, $adapter->post('http://api.anthropic.com/v1/messages')['status']);
        $this->assertSame(0, $adapter->post('https://169.254.169.254/latest')['status']);
        $this->assertSame([], $adapter->timeouts);
    }

    public function testSuccessfulResponseIsReturnedOnFirstAttempt(): void
    {
        $adapter = $this->buildAdapter(false);

        $response = $adapter->post('https://api.anthropic.com/v1/messages');

        $this->assertSame(200, $response['status']);
        $this->assertCount(1, $adapter->timeouts);
    }

    private function buildAdapter(bool $hang): object
    {
        return new class (
            $this->createStub(ScopeConfigInterface::class),
            $this->createStub(EncryptorInterface::class),
            $this->createStub(ResourceConnection::class),
            $this->createStub(DateTime::class),
            new NullLogger(),
            $hang
        ) extends ClaudeAdapter {
            protected const TOTAL_TIMEOUT_SECONDS = 3;
            protected const BACKOFF_BASE_MS = 10;

            public array $timeouts = [];

            public function __construct(
                ScopeConfigInterface $scopeConfig,
                EncryptorInterface $encryptor,
                ResourceConnection $resource,
                DateTime $dateTime,
                NullLogger $logger,
                private readonly bool $hang
            ) {
                parent::__construct($scopeConfig, $encryptor, $resource, $dateTime, $logger);
            }

            public function post(string $url): array
            {
                return $this->curlPost($url, ['content-type' => 'application/json'], ['messages' => []]);
            }

            protected function executeRequest(string $url, array $headers, string $body, int $timeout): array
            {
                $this->timeouts[] = $timeout;
                if ($this->hang) {
                    sleep($timeout);
                    return ['body' => null, 'status' => 0, 'content_type' => '', 'error' => 'Operation timed out'];
                }
                return ['body' => '{"content":[]}', 'status' => 200, 'content_type' => 'application/json', 'error' => ''];
            }
        };
    }
}
