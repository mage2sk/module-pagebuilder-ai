<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Cron;

use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\QueueRepository;
use Panth\PageBuilderAi\Model\Queue\BulkGenerateConsumer;
use Psr\Log\LoggerInterface;

class DrainGenerationQueue
{
    private const CONNECTION_NAME = 'db';

    private const QUEUE_NAME = 'panth_pagebuilderai.generate_meta.q';

    private const TOPIC_NAME = 'panth_pagebuilderai.generate_meta';

    private const LOCK_NAME = 'panth_pagebuilderai_drain_queue';

    private const MAX_MESSAGES = 50;

    private const MAX_RUNTIME_SECONDS = 150;

    public function __construct(
        private readonly QueueRepository $queueRepository,
        private readonly MessageEncoder $messageEncoder,
        private readonly BulkGenerateConsumer $consumer,
        private readonly LockManagerInterface $lockManager,
        private readonly LoggerInterface $logger,
        private readonly int $maxMessages = self::MAX_MESSAGES,
        private readonly int $maxRuntimeSeconds = self::MAX_RUNTIME_SECONDS
    ) {
    }

    public function execute(): void
    {
        try {
            $locked = $this->lockManager->lock(self::LOCK_NAME, 0);
        } catch (\Throwable $e) {
            $this->logger->warning('[Panth PageBuilderAi] drain-queue lock failed: ' . $e->getMessage());
            return;
        }
        if (!$locked) {
            $this->logger->info('[Panth PageBuilderAi] drain-queue skipped: previous run still active');
            return;
        }

        try {
            $this->drain();
        } catch (\Throwable $e) {
            $this->logger->warning(
                '[Panth PageBuilderAi] drain-queue cron failed: ' . $e->getMessage()
            );
        } finally {
            $this->lockManager->unlock(self::LOCK_NAME);
        }
    }

    private function drain(): void
    {
        $deadline = microtime(true) + max(1, $this->maxRuntimeSeconds);
        $queue = $this->queueRepository->get(self::CONNECTION_NAME, self::QUEUE_NAME);
        $this->consumer->setDeadline($deadline);

        try {
            for ($i = 0; $i < $this->maxMessages; $i++) {
                if (microtime(true) >= $deadline) {
                    break;
                }
                $envelope = $queue->dequeue();
                if ($envelope === null) {
                    break;
                }
                try {
                    $body = $this->messageEncoder->decode(self::TOPIC_NAME, $envelope->getBody());
                    $this->consumer->process((string)$body);
                    $queue->acknowledge($envelope);
                } catch (\Throwable $e) {
                    $this->logger->warning(
                        '[Panth PageBuilderAi] drain-queue message failed: ' . $e->getMessage()
                    );
                    $queue->reject($envelope, false, $e->getMessage());
                }
            }
        } finally {
            $this->consumer->setDeadline(null);
        }
    }
}
