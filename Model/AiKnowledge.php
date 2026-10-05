<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model;

use Magento\Framework\Model\AbstractModel;

class AiKnowledge extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(\Panth\PageBuilderAi\Model\ResourceModel\AiKnowledge::class);
    }
}
