<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Shared base for Panth PageBuilderAi admin controllers.
 *
 * Each concrete subclass must override ADMIN_RESOURCE with the specific
 * ACL id it wishes to enforce, so permissions can be declared at a finer
 * granularity than the single "manage" resource that AdvancedSEO used.
 */
abstract class AbstractAction extends Action
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_manage';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(static::ADMIN_RESOURCE);
    }
}
