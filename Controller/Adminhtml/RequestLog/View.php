<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\RequestLog;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Panth\PageBuilderAi\Controller\Adminhtml\AbstractAction;

class View extends AbstractAction
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_request_logs';

    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory,
        private readonly ResourceConnection $resource,
        private readonly StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $logId = (int) $this->getRequest()->getParam('log_id');
        if ($logId <= 0) {
            $this->messageManager->addErrorMessage(__('Invalid log id.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $conn = $this->resource->getConnection();
        $row = $conn->fetchRow(
            $conn->select()
                ->from($this->resource->getTableName('panth_pagebuilderai_request_log'))
                ->where('log_id = ?', $logId)
        );

        if (!$row) {
            $this->messageManager->addErrorMessage(__('Log entry not found.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $page = $this->pageFactory->create();
        $page->setActiveMenu('Panth_PageBuilderAi::ai_request_logs');
        $page->getConfig()->getTitle()->prepend(__('AI Request Log #%1', $logId));

        $block = $page->getLayout()->getBlock('panth_pagebuilderai.request_log.view');
        if ($block) {
            $block->setData('log_row', $row);
            $block->setData(
                'media_base_url',
                $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            );
        }

        return $page;
    }
}
