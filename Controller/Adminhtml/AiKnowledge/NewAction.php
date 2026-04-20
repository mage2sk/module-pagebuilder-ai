<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\AiKnowledge;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * "Add New Knowledge Entry" admin action — forwards to Edit which already
 * renders the blank form when no `id` param is present.
 *
 * Intentionally no __construct — relies on the inherited `$resultFactory`
 * property from \Magento\Framework\App\Action\AbstractAction.
 */
class NewAction extends Action
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_knowledge';

    public function execute(): ResultInterface
    {
        /** @var \Magento\Framework\Controller\Result\Forward $forward */
        $forward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        return $forward->forward('edit');
    }
}
