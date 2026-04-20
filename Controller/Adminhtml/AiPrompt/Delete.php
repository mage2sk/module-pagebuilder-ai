<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\AiPrompt;

use Panth\PageBuilderAi\Controller\Adminhtml\AbstractAction;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class Delete extends AbstractAction implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_prompts';

    public function __construct(
        Context $context,
        private readonly ResourceConnection $resource,
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

        $id = (int)$this->getRequest()->getParam('id');
        if ($id > 0) {
            try {
                $this->resource->getConnection()->delete(
                    $this->resource->getTableName('panth_seo_ai_prompt'),
                    ['prompt_id = ?' => $id]
                );
                $this->messageManager->addSuccessMessage(__('AI Prompt deleted.'));
            } catch (\Throwable $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
