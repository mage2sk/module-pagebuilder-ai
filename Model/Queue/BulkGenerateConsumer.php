<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\Queue;

use Panth\PageBuilderAi\Api\AiGeneratorInterface;
use Panth\PageBuilderAi\Model\Score\ContextBuilder;
use Panth\PageBuilderAi\Model\GenerationJob;
use Panth\PageBuilderAi\Model\ResourceModel\GenerationJob as JobResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class BulkGenerateConsumer
{
    public const MAX_ENTITIES_PER_JOB = 500;

    private ?float $deadline = null;

    public function __construct(
        private readonly AiGeneratorInterface $generator,
        private readonly ContextBuilder $contextBuilder,
        private readonly ResourceConnection $resource,
        private readonly JobResource $jobResource,
        private readonly SerializerInterface $serializer,
        private readonly DateTime $dateTime,
        private readonly LoggerInterface $logger
    ) {
    }

    public function setDeadline(?float $deadline): void
    {
        $this->deadline = $deadline;
    }

    public function process(string $message): void
    {
        $decoded = json_decode($message, true);
        if (!is_array($decoded) || !isset($decoded['job_id'])) {
            $this->logger->warning('Panth PageBuilderAi generate: invalid message', ['message' => $message]);
            return;
        }
        $jobId = (int)$decoded['job_id'];

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('panth_seo_generation_job');
        $row = $connection->fetchRow(
            $connection->select()->from($table)->where('job_id = ?', $jobId)->limit(1)
        );
        if (!$row) {
            $this->logger->warning('Panth PageBuilderAi generate: job not found', ['job_id' => $jobId]);
            return;
        }

        $currentStatus = (string)($row['status'] ?? '');
        if ($currentStatus === GenerationJob::STATUS_PROCESSING) {
            $connection->update(
                $table,
                [
                    'status' => GenerationJob::STATUS_FAILED,
                    'error_message' => 'Job was interrupted during a previous run and was not retried.',
                    'updated_at' => $this->dateTime->gmtDate(),
                ],
                ['job_id = ?' => $jobId]
            );
            return;
        }
        if ($currentStatus !== GenerationJob::STATUS_PENDING) {
            $this->logger->info('Panth PageBuilderAi generate: job already handled', ['job_id' => $jobId]);
            return;
        }

        $connection->update(
            $table,
            ['status' => GenerationJob::STATUS_PROCESSING, 'updated_at' => $this->dateTime->gmtDate()],
            ['job_id = ?' => $jobId]
        );

        $options = json_decode((string)($row['options'] ?? '{}'), true) ?: [];
        $entityIds = array_slice((array)($options['entity_ids'] ?? []), 0, self::MAX_ENTITIES_PER_JOB);
        $entityType = (string)$row['entity_type'];
        $storeId = (int)$row['store_id'];
        $processed = 0;
        $failed = 0;
        $skipped = 0;
        $results = [];

        foreach ($entityIds as $entityId) {
            if ($this->deadline !== null && microtime(true) >= $this->deadline) {
                $skipped++;
                continue;
            }
            try {
                $context = $this->contextBuilder->build($entityType, (int)$entityId, $storeId);
                $result = $this->generator->generate($context);

                $title = (string)($result['title'] ?? '');
                $description = (string)($result['description'] ?? '');
                if ($title === '' && $description === '') {
                    $failed++;
                } else {
                    $results[(int)$entityId] = [
                        'draft_title' => $title,
                        'draft_description' => $description,
                        'confidence' => (float)($result['confidence'] ?? 0.0),
                    ];
                    $processed++;
                }
            } catch (\Throwable $e) {
                $this->logger->error('Panth PageBuilderAi generate failed for entity', [
                    'job_id' => $jobId,
                    'entity_id' => $entityId,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        $failed += $skipped;
        $status = ($processed > 0) ? GenerationJob::STATUS_DRAFT : GenerationJob::STATUS_FAILED;
        $options['results'] = $results;
        $update = [
            'status' => $status,
            'processed' => $processed,
            'failed' => $failed,
            'options' => json_encode($options),
            'updated_at' => $this->dateTime->gmtDate(),
        ];
        if ($skipped > 0) {
            $update['error_message'] = sprintf(
                'Stopped at the per-run time limit; %d entities were not processed.',
                $skipped
            );
        }

        try {
            $connection->update(
                $table,
                $update,
                ['job_id = ?' => $jobId]
            );
        } catch (\Throwable $e) {
            $this->logger->error('Panth PageBuilderAi generate failed', [
                'job_id' => $jobId,
                'error' => $e->getMessage(),
            ]);
            $connection->update(
                $table,
                [
                    'status' => GenerationJob::STATUS_FAILED,
                    'error_message' => $e->getMessage(),
                    'updated_at' => $this->dateTime->gmtDate(),
                ],
                ['job_id = ?' => $jobId]
            );
        }
    }
}
