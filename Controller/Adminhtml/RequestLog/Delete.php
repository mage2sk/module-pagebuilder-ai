<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\RequestLog;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Panth\PageBuilderAi\Controller\Adminhtml\AbstractAction;

class Delete extends AbstractAction
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_request_logs';

    public function __construct(Context $context, private readonly ResourceConnection $resource)
    {
        parent::__construct($context);
    }

    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        $logId = (int) $this->getRequest()->getParam('log_id');
        if ($logId > 0) {
            try {
                $conn = $this->resource->getConnection();
                $conn->delete(
                    $this->resource->getTableName('panth_pagebuilderai_request_log'),
                    ['log_id = ?' => $logId]
                );
                $this->messageManager->addSuccessMessage(__('Log entry deleted.'));
            } catch (\Throwable $e) {
                $this->messageManager->addErrorMessage(__('Could not delete log entry.'));
            }
        }
        return $redirect->setPath('*/*/');
    }
}
