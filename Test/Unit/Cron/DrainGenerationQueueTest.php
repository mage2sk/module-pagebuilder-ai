<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Api\AiGeneratorInterface;
use Panth\PageBuilderAi\Cron\DrainGenerationQueue;
use Panth\PageBuilderAi\Model\GenerationJob;
use Panth\PageBuilderAi\Model\Queue\BulkGenerateConsumer;
use Panth\PageBuilderAi\Model\ResourceModel\GenerationJob as JobResource;
use Panth\PageBuilderAi\Model\Score\ContextBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class DrainGenerationQueueTest extends TestCase
{
    private array $updates = [];

    private array $jobRow = [];

    private int $generateCalls = 0;

    protected function setUp(): void
    {
        $this->updates = [];
        $this->generateCalls = 0;
        $this->jobRow = [
            'job_id' => 7,
            'entity_type' => 'product',
            'store_id' => 0,
            'status' => GenerationJob::STATUS_PENDING,
            'options' => json_encode(['entity_ids' => range(1, 500)]),
        ];
    }

    public function testSlowProviderStopsWithinRunBudget(): void
    {
        $consumer = $this->buildConsumer(1.0);
        [$queue, $acked] = $this->buildQueue(1);
        $cron = $this->buildCron($queue, $consumer, true, 50, 3);

        $started = microtime(true);
        $cron->execute();
        $elapsed = microtime(true) - $started;

        $this->assertLessThan(5.0, $elapsed);
        $this->assertLessThanOrEqual(4, $this->generateCalls);
        $this->assertSame(1, $acked->count);
        $final = end($this->updates);
        $this->assertSame(GenerationJob::STATUS_DRAFT, $final['status']);
        $this->assertSame(500, $final['processed'] + $final['failed']);
        $this->assertStringContainsString('per-run time limit', $final['error_message']);
    }

    public function testEmptyQueueReturnsWithoutWaiting(): void
    {
        $consumer = $this->buildConsumer(0.0);
        [$queue] = $this->buildQueue(0);
        $cron = $this->buildCron($queue, $consumer, true, 50, 150);

        $started = microtime(true);
        $cron->execute();

        $this->assertLessThan(1.0, microtime(true) - $started);
        $this->assertSame(0, $this->generateCalls);
    }

    public function testMessageBudgetIsRespected(): void
    {
        $this->jobRow['status'] = GenerationJob::STATUS_DRAFT;
        $consumer = $this->buildConsumer(0.0);
        [$queue, $acked, $dequeued] = $this->buildQueue(PHP_INT_MAX);
        $cron = $this->buildCron($queue, $consumer, true, 5, 150);

        $cron->execute();

        $this->assertSame(5, $dequeued->count);
        $this->assertSame(5, $acked->count);
        $this->assertSame(0, $this->generateCalls);
    }

    public function testOverlappingRunIsSkipped(): void
    {
        $consumer = $this->buildConsumer(0.0);
        [$queue, , $dequeued] = $this->buildQueue(10);
        $cron = $this->buildCron($queue, $consumer, false, 50, 150);

        $cron->execute();

        $this->assertSame(0, $dequeued->count);
    }

    public function testInterruptedJobIsMarkedFailedNotRetried(): void
    {
        $this->jobRow['status'] = GenerationJob::STATUS_PROCESSING;
        $consumer = $this->buildConsumer(0.0);
        [$queue, $acked] = $this->buildQueue(1);
        $cron = $this->buildCron($queue, $consumer, true, 50, 150);

        $cron->execute();

        $this->assertSame(0, $this->generateCalls);
        $this->assertSame(1, $acked->count);
        $this->assertSame(GenerationJob::STATUS_FAILED, $this->updates[0]['status']);
    }

    public function testFailingMessageIsRejectedWithoutRequeue(): void
    {
        $queue = $this->createMock(QueueInterface::class);
        $envelope = $this->createStub(EnvelopeInterface::class);
        $envelope->method('getBody')->willReturn('"x"');
        $queue->method('dequeue')->willReturnOnConsecutiveCalls($envelope, null);
        $queue->expects($this->never())->method('acknowledge');
        $queue->expects($this->once())->method('reject')->with($envelope, false, $this->isString());

        $consumer = $this->createStub(BulkGenerateConsumer::class);
        $consumer->method('process')->willThrowException(new \RuntimeException('boom'));

        $this->buildCron($queue, $consumer, true, 50, 150)->execute();
    }

    private function buildCron(
        QueueInterface $queue,
        BulkGenerateConsumer $consumer,
        bool $lockResult,
        int $maxMessages,
        int $maxRuntime
    ): DrainGenerationQueue {
        $repository = $this->createStub(QueueRepository::class);
        $repository->method('get')->willReturn($queue);
        $encoder = $this->createStub(MessageEncoder::class);
        $encoder->method('decode')->willReturnCallback(static fn ($topic, $body) => json_decode($body, true));
        $lock = $this->createStub(LockManagerInterface::class);
        $lock->method('lock')->willReturn($lockResult);
        $lock->method('unlock')->willReturn(true);

        return new DrainGenerationQueue(
            $repository,
            $encoder,
            $consumer,
            $lock,
            new NullLogger(),
            $maxMessages,
            $maxRuntime
        );
    }

    private function buildQueue(int $available): array
    {
        $acked = new \stdClass();
        $acked->count = 0;
        $dequeued = new \stdClass();
        $dequeued->count = 0;
        $envelope = $this->createStub(EnvelopeInterface::class);
        $envelope->method('getBody')->willReturn(json_encode(json_encode(['job_id' => 7])));

        $queue = $this->createStub(QueueInterface::class);
        $queue->method('dequeue')->willReturnCallback(
            static function () use (&$available, $envelope, $dequeued) {
                if ($available <= 0) {
                    return null;
                }
                $available--;
                $dequeued->count++;
                return $envelope;
            }
        );
        $queue->method('acknowledge')->willReturnCallback(
            static function () use ($acked) {
                $acked->count++;
            }
        );

        return [$queue, $acked, $dequeued];
    }

    private function buildConsumer(float $sleepSeconds): BulkGenerateConsumer
    {
        $generator = $this->createStub(AiGeneratorInterface::class);
        $generator->method('generate')->willReturnCallback(
            function () use ($sleepSeconds) {
                $this->generateCalls++;
                if ($sleepSeconds > 0) {
                    usleep((int)($sleepSeconds * 1000000));
                }
                return ['title' => 'Title', 'description' => 'Description', 'confidence' => 0.5];
            }
        );

        $contextBuilder = $this->createStub(ContextBuilder::class);
        $contextBuilder->method('build')->willReturn([]);

        $select = $this->createStub(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('limit')->willReturnSelf();

        $connection = $this->createStub(AdapterInterface::class);
        $connection->method('select')->willReturn($select);
        $connection->method('fetchRow')->willReturnCallback(fn () => $this->jobRow);
        $connection->method('update')->willReturnCallback(
            function ($table, array $data) {
                $this->updates[] = $data;
                return 1;
            }
        );

        $resource = $this->createStub(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);
        $resource->method('getTableName')->willReturnArgument(0);

        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturn('2026-01-01 00:00:00');

        return new BulkGenerateConsumer(
            $generator,
            $contextBuilder,
            $resource,
            $this->createStub(JobResource::class),
            $this->createStub(SerializerInterface::class),
            $dateTime,
            new NullLogger()
        );
    }
}
