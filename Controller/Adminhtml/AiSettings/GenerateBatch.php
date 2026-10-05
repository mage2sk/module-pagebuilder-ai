<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\AiSettings;

use Panth\PageBuilderAi\Controller\Adminhtml\AbstractAction;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Panth\PageBuilderAi\Model\GenerationJob;
use Panth\PageBuilderAi\Model\Queue\BulkGenerateConsumer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class GenerateBatch extends AbstractAction implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_jobs';
    public const TOPIC = 'panth_pagebuilderai.generate_meta';

    public function __construct(
        Context $context,
        private readonly ResourceConnection $resource,
        private readonly DateTime $dateTime,
        private readonly PublisherInterface $publisher,
        private readonly FormKeyValidator $formKeyValidator
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->getRequest()->isPost() || !$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page.'));
            return $resultRedirect->setPath('*/*/');
        }

        $entityType = (string)$this->getRequest()->getParam('entity_type', 'product');
        if (!in_array($entityType, ['product', 'category', 'cms_page'], true)) {
            $this->messageManager->addErrorMessage(__('Invalid entity type.'));
            return $resultRedirect->setPath('*/*/');
        }
        $storeId = (int)$this->getRequest()->getParam('store_id', 0);
        $ids = (array)$this->getRequest()->getParam('ids', []);
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        )));

        if (!$ids) {
            $this->messageManager->addErrorMessage(__('No entities selected.'));
            return $resultRedirect->setPath('*/*/');
        }
        if (count($ids) > BulkGenerateConsumer::MAX_ENTITIES_PER_JOB) {
            $this->messageManager->addErrorMessage(
                __('A job can contain at most %1 entities.', BulkGenerateConsumer::MAX_ENTITIES_PER_JOB)
            );
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName('panth_seo_generation_job');
            $uuid = bin2hex(random_bytes(16));
            $uuid = sprintf(
                '%s-%s-%s-%s-%s',
                substr($uuid, 0, 8),
                substr($uuid, 8, 4),
                substr($uuid, 12, 4),
                substr($uuid, 16, 4),
                substr($uuid, 20, 12)
            );
            $connection->insert($table, [
                'uuid' => $uuid,
                'entity_type' => $entityType,
                'store_id' => $storeId,
                'total' => count($ids),
                'processed' => 0,
                'failed' => 0,
                'status' => GenerationJob::STATUS_PENDING,
                'options' => json_encode(['entity_ids' => $ids]),
                'created_at' => $this->dateTime->gmtDate(),
                'updated_at' => $this->dateTime->gmtDate(),
            ]);
            $jobId = (int)$connection->lastInsertId($table);
            $this->publisher->publish(self::TOPIC, json_encode(['job_id' => $jobId]));
            $this->messageManager->addSuccessMessage(__('Generation job queued for %1 entities.', count($ids)));
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(
                __('Failed to queue generation job. Please check the system logs for details.')
            );
        }

        return $resultRedirect->setPath('*/*/');
    }
}
