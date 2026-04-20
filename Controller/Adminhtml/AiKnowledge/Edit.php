<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\AiKnowledge;

use Panth\PageBuilderAi\Controller\Adminhtml\AbstractAction;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Framework\App\ResourceConnection;
use Magento\Backend\App\Action\Context;

class Edit extends AbstractAction
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_knowledge';

    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory,
        private readonly Registry $registry,
        private readonly ResourceConnection $resource
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $row = [];
        if ($id > 0) {
            $connection = $this->resource->getConnection();
            $row = $connection->fetchRow(
                $connection->select()
                    ->from($this->resource->getTableName('panth_seo_ai_knowledge'))
                    ->where('knowledge_id = ?', $id)
            ) ?: [];
        }
        $this->registry->register('panth_pagebuilderai_ai_knowledge', $row, true);

        $page = $this->pageFactory->create();
        $page->setActiveMenu('Panth_PageBuilderAi::ai_knowledge');
        $page->getConfig()->getTitle()->prepend($id ? __('Edit Knowledge Entry') : __('New Knowledge Entry'));
        return $page;
    }
}
