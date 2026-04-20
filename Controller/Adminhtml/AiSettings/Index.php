<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\AiSettings;

use Panth\PageBuilderAi\Controller\Adminhtml\AbstractAction;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;

class Index extends AbstractAction
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_settings';

    public function __construct(Context $context, private readonly PageFactory $pageFactory)
    {
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Panth_PageBuilderAi::ai_jobs');
        $page->getConfig()->getTitle()->prepend(__('AI Settings'));
        return $page;
    }
}
