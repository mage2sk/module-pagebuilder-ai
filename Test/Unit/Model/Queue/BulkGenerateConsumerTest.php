<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model\Queue;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Api\AiGeneratorInterface;
use Panth\PageBuilderAi\Model\GenerationJob;
use Panth\PageBuilderAi\Model\Queue\BulkGenerateConsumer;
use Panth\PageBuilderAi\Model\ResourceModel\GenerationJob as JobResource;
use Panth\PageBuilderAi\Model\Score\ContextBuilder;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class BulkGenerateConsumerTest extends TestCase
{
    use DbStubs;

    private array $updates = [];

    public function testInvalidMessageIsIgnored(): void
    {
        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($this->never())->method('getConnection');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))->method('warning')->with($this->stringContains('invalid message'));

        $consumer = $this->consumer($this->createStub(AiGeneratorInterface::class), $resource, $logger);
        $consumer->process('not json');
        $consumer->process('{"other":1}');
    }

    public function testMissingJobIsLogged(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('job not found'), ['job_id' => 3]);

        $this->consumer($this->createStub(AiGeneratorInterface::class), $this->resource(false), $logger)
            ->process('{"job_id":3}');

        $this->assertSame([], $this->updates);
    }

    public function testInterruptedJobIsFailedWithoutRegeneration(): void
    {
        $generator = $this->createMock(AiGeneratorInterface::class);
        $generator->expects($this->never())->method('generate');

        $this->consumer($generator, $this->resource($this->job(GenerationJob::STATUS_PROCESSING, [1])))
            ->process('{"job_id":3}');

        $this->assertCount(1, $this->updates);
        $this->assertSame(GenerationJob::STATUS_FAILED, $this->updates[0]['status']);
        $this->assertStringContainsString('interrupted', $this->updates[0]['error_message']);
    }

    public function testAlreadyHandledJobIsSkipped(): void
    {
        $generator = $this->createMock(AiGeneratorInterface::class);
        $generator->expects($this->never())->method('generate');

        $this->consumer($generator, $this->resource($this->job(GenerationJob::STATUS_APPROVED, [1])))
            ->process('{"job_id":3}');

        $this->assertSame([], $this->updates);
    }

    public function testPendingJobProducesDraftResults(): void
    {
        $generator = $this->createStub(AiGeneratorInterface::class);
        $generator->method('generate')->willReturnCallback(static function (array $context) {
            return match ($context['entity_id']) {
                1 => ['title' => 'T1', 'description' => 'D1', 'confidence' => '0.8'],
                2 => ['title' => '', 'description' => ''],
                default => throw new \RuntimeException('provider error'),
            };
        });

        $this->consumer($generator, $this->resource($this->job(GenerationJob::STATUS_PENDING, [1, 2, 3])))
            ->process('{"job_id":3}');

        $this->assertCount(2, $this->updates);
        $this->assertSame(GenerationJob::STATUS_PROCESSING, $this->updates[0]['status']);
        $final = $this->updates[1];
        $this->assertSame(GenerationJob::STATUS_DRAFT, $final['status']);
        $this->assertSame(1, $final['processed']);
        $this->assertSame(2, $final['failed']);
        $this->assertArrayNotHasKey('error_message', $final);
        $options = json_decode($final['options'], true);
        $this->assertSame([1, 2, 3], $options['entity_ids']);
        $this->assertSame(
            ['1' => ['draft_title' => 'T1', 'draft_description' => 'D1', 'confidence' => 0.8]],
            $options['results']
        );
    }

    public function testJobWithoutAnySuccessIsFailed(): void
    {
        $generator = $this->createStub(AiGeneratorInterface::class);
        $generator->method('generate')->willReturn(['title' => '', 'description' => '']);

        $this->consumer($generator, $this->resource($this->job(GenerationJob::STATUS_PENDING, [5])))
            ->process('{"job_id":3}');

        $this->assertSame(GenerationJob::STATUS_FAILED, end($this->updates)['status']);
        $this->assertSame(1, end($this->updates)['failed']);
    }

    public function testEntityListIsCapped(): void
    {
        $calls = 0;
        $generator = $this->createStub(AiGeneratorInterface::class);
        $generator->method('generate')->willReturnCallback(static function () use (&$calls) {
            $calls++;
            return ['title' => 'T', 'description' => 'D'];
        });

        $this->consumer($generator, $this->resource($this->job(GenerationJob::STATUS_PENDING, range(1, 600))))
            ->process('{"job_id":3}');

        $this->assertSame(BulkGenerateConsumer::MAX_ENTITIES_PER_JOB, $calls);
        $this->assertSame(500, end($this->updates)['processed']);
    }

    public function testExpiredDeadlineSkipsAllEntities(): void
    {
        $generator = $this->createMock(AiGeneratorInterface::class);
        $generator->expects($this->never())->method('generate');

        $consumer = $this->consumer($generator, $this->resource($this->job(GenerationJob::STATUS_PENDING, [1, 2])));
        $consumer->setDeadline(microtime(true) - 1);
        $consumer->process('{"job_id":3}');

        $final = end($this->updates);
        $this->assertSame(GenerationJob::STATUS_FAILED, $final['status']);
        $this->assertSame(2, $final['failed']);
        $this->assertStringContainsString('2 entities were not processed', $final['error_message']);
    }

    public function testFinalUpdateFailureMarksJobFailed(): void
    {
        $generator = $this->createStub(AiGeneratorInterface::class);
        $generator->method('generate')->willReturn(['title' => 'T', 'description' => 'D']);
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn($this->job(GenerationJob::STATUS_PENDING, [1]));
        $calls = 0;
        $connection->method('update')->willReturnCallback(function ($table, array $data) use (&$calls) {
            $calls++;
            if ($calls === 2) {
                throw new \RuntimeException('deadlock');
            }
            $this->updates[] = $data;
            return 1;
        });

        $this->consumer($generator, $this->resourceStub($connection))->process('{"job_id":3}');

        $final = end($this->updates);
        $this->assertSame(GenerationJob::STATUS_FAILED, $final['status']);
        $this->assertSame('deadlock', $final['error_message']);
    }

    private function job(string $status, array $entityIds): array
    {
        return [
            'job_id' => 3,
            'entity_type' => 'product',
            'store_id' => '1',
            'status' => $status,
            'options' => json_encode(['entity_ids' => $entityIds]),
        ];
    }

    private function resource(array|false $row): ResourceConnection
    {
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn($row);
        $connection->method('update')->willReturnCallback(function ($table, array $data) {
            $this->updates[] = $data;
            return 1;
        });
        return $this->resourceStub($connection);
    }

    private function consumer(
        AiGeneratorInterface $generator,
        ResourceConnection $resource,
        ?LoggerInterface $logger = null
    ): BulkGenerateConsumer {
        $contextBuilder = $this->createStub(ContextBuilder::class);
        $contextBuilder->method('build')->willReturnCallback(
            static fn (string $type, int $id, int $store) => ['entity_type' => $type, 'entity_id' => $id, 'store_id' => $store]
        );
        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturn('2026-01-01 00:00:00');

        return new BulkGenerateConsumer(
            $generator,
            $contextBuilder,
            $resource,
            $this->createStub(JobResource::class),
            $this->createStub(SerializerInterface::class),
            $dateTime,
            $logger ?? new NullLogger()
        );
    }
}
