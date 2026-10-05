<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model;

use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\RequestLogger;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class RequestLoggerTest extends TestCase
{
    use DbStubs;

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    public function testNothingIsWrittenWhenTableIsMissing(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('isTableExists')->willReturn(false);
        $connection->expects($this->never())->method('insert');

        $this->logger($connection)->record(['prompt' => 'x']);
    }

    public function testRowIsMappedFromRequestData(): void
    {
        $row = null;
        $connection = $this->insertingConnection($row);

        $this->logger($connection, 'claude')->record([
            'prompt' => 'Prompt text',
            'response' => 'Reply',
            'entity_type' => 'product',
            'entity_id' => 0,
            'store_id' => '2',
            'target_field' => '',
            'output_format' => 'json',
            'image_count' => 3,
            'tokens_used' => '7',
            'latency_ms' => 120.4,
            'success' => true,
            'http_status' => '200',
            'error_message' => '',
        ]);

        $this->assertSame('admin', $row['admin_user']);
        $this->assertSame('product', $row['entity_type']);
        $this->assertNull($row['entity_id']);
        $this->assertSame(2, $row['store_id']);
        $this->assertNull($row['target_field']);
        $this->assertSame('json', $row['output_format']);
        $this->assertSame('claude', $row['provider']);
        $this->assertSame('model-c', $row['model']);
        $this->assertSame(11, $row['prompt_length']);
        $this->assertSame(5, $row['response_length']);
        $this->assertSame(3, $row['image_count']);
        $this->assertSame(7, $row['tokens_used']);
        $this->assertSame(120, $row['latency_ms']);
        $this->assertSame(1, $row['success']);
        $this->assertSame('200', $row['http_status']);
        $this->assertNull($row['error_message']);
        $this->assertNull($row['images_json']);
        $this->assertSame('2026-01-01 00:00:00', $row['created_at']);
    }

    public function testOptionalFieldsDefaultToNull(): void
    {
        $row = null;
        $connection = $this->insertingConnection($row);

        $this->logger($connection, 'null', null)->record(['entity_id' => 9]);

        $this->assertNull($row['admin_user']);
        $this->assertSame(9, $row['entity_id']);
        $this->assertNull($row['store_id']);
        $this->assertNull($row['model']);
        $this->assertNull($row['tokens_used']);
        $this->assertNull($row['latency_ms']);
        $this->assertSame(0, $row['success']);
        $this->assertSame('', $row['prompt']);
    }

    public function testOnlyValidImagesArePersisted(): void
    {
        $row = null;
        $connection = $this->insertingConnection($row);
        $written = [];
        $writer = $this->createStub(WriteInterface::class);
        $writer->method('writeFile')->willReturnCallback(
            static function ($path, $content) use (&$written) {
                $written[$path] = $content;
                return strlen($content);
            }
        );
        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($writer);

        $this->logger($connection, 'openai', 'admin', $filesystem)->record([
            'images' => [
                'data:image/png;base64,' . self::PNG,
                'not a data uri',
                'data:image/png;base64,' . base64_encode('plain text'),
                42,
            ],
        ]);

        $this->assertCount(1, $written);
        $path = array_key_first($written);
        $this->assertMatchesRegularExpression('#^panth_pagebuilderai/request-log/[^/]+/0\.png$#', $path);
        $this->assertSame(base64_decode(self::PNG), $written[$path]);
        $this->assertSame([$path], json_decode($row['images_json'], true));
        $this->assertSame('model-o', $row['model']);
    }

    public function testUnavailableVarDirectorySkipsImages(): void
    {
        $row = null;
        $connection = $this->insertingConnection($row);
        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willThrowException(new \RuntimeException('no var'));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('var dir unavailable'));

        $this->logger($connection, 'openai', 'admin', $filesystem, $logger)
            ->record(['images' => ['data:image/png;base64,' . self::PNG]]);

        $this->assertNull($row['images_json']);
    }

    public function testInsertFailureIsLoggedNotThrown(): void
    {
        $connection = $this->connectionStub(['panth_pagebuilderai_request_log']);
        $connection->method('insert')->willThrowException(new \RuntimeException('disk full'));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('disk full'));

        $this->logger($connection, 'openai', 'admin', null, $logger)->record(['prompt' => 'x']);
    }

    private function insertingConnection(?array &$row): AdapterInterface
    {
        $connection = $this->connectionStub(['panth_pagebuilderai_request_log']);
        $connection->method('insert')->willReturnCallback(
            static function ($table, array $data) use (&$row) {
                $row = $data;
                return 1;
            }
        );
        return $connection;
    }

    private function logger(
        AdapterInterface $connection,
        string $provider = 'openai',
        ?string $username = 'admin',
        ?Filesystem $filesystem = null,
        ?LoggerInterface $logger = null
    ): RequestLogger {
        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturn('2026-01-01 00:00:00');
        $config = $this->createStub(Config::class);
        $config->method('getProvider')->willReturn($provider);
        $config->method('getOpenAiModel')->willReturn('model-o');
        $config->method('getClaudeModel')->willReturn('model-c');
        $user = $username === null ? null : new DataObject(['username' => $username]);
        $session = new class ($user) extends AdminSession {
            public function __construct(private readonly ?DataObject $sessionUser)
            {
            }

            public function getUser()
            {
                return $this->sessionUser;
            }
        };

        return new RequestLogger(
            $this->resourceStub($connection),
            $dateTime,
            $session,
            $config,
            $filesystem ?? $this->createStub(Filesystem::class),
            $logger ?? new NullLogger()
        );
    }
}
