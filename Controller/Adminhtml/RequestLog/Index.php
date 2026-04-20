<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\RequestLog;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Panth\PageBuilderAi\Controller\Adminhtml\AbstractAction;

class Index extends AbstractAction
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_request_logs';

    public function __construct(Context $context, private readonly PageFactory $pageFactory)
    {
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Panth_PageBuilderAi::ai_request_logs');
        $page->getConfig()->getTitle()->prepend(__('AI Request Logs'));
        return $page;
    }
}
