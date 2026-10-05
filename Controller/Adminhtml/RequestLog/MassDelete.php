<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\RequestLog;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Panth\PageBuilderAi\Controller\Adminhtml\AbstractAction;

class MassDelete extends AbstractAction implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_request_logs';

    public function __construct(
        Context $context,
        private readonly ResourceConnection $resource,
        private readonly FormKeyValidator $formKeyValidator
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();

        if (!$this->getRequest()->isPost() || !$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh and try again.'));
            return $redirect->setPath('*/*/');
        }

        $ids = (array) $this->getRequest()->getParam('selected', []);
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            $this->messageManager->addErrorMessage(__('Please select at least one log entry.'));
            return $redirect->setPath('*/*/');
        }

        try {
            $conn = $this->resource->getConnection();
            $deleted = $conn->delete(
                $this->resource->getTableName('panth_pagebuilderai_request_log'),
                ['log_id IN (?)' => $ids]
            );
            $this->messageManager->addSuccessMessage(__('Deleted %1 log entr(ies).', $deleted));
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Could not delete the selected entries.'));
        }

        return $redirect->setPath('*/*/');
    }
}
