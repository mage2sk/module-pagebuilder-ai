<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Cron;

use Magento\Framework\MessageQueue\ConsumerFactory;
use Psr\Log\LoggerInterface;

/**
 * Fallback drainer for the bulk-generation queue.
 *
 * Only useful when the admin has NOT configured `cron_consumers_runner` in
 * env.php (in which case Magento already drains the consumer every minute).
 * Running both is harmless — the consumer is single-tenant and idempotent.
 */
class DrainGenerationQueue
{
    private const CONSUMER_NAME = 'panth_pagebuilderai.generate_meta.consumer';

    /** Cap each cron tick so a slow AI provider doesn't hold the cron process indefinitely. */
    private const MAX_MESSAGES = 50;

    public function __construct(
        private readonly ConsumerFactory $consumerFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): void
    {
        try {
            $consumer = $this->consumerFactory->get(self::CONSUMER_NAME, self::MAX_MESSAGES);
            $consumer->process(self::MAX_MESSAGES);
        } catch (\Throwable $e) {
            // Swallow — cron should never bubble an exception that would block other jobs.
            $this->logger->warning(
                '[Panth PageBuilderAi] drain-queue cron failed: ' . $e->getMessage()
            );
        }
    }
}
