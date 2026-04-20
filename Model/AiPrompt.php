<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model;

use Magento\Framework\Model\AbstractModel;

class AiPrompt extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(\Panth\PageBuilderAi\Model\ResourceModel\AiPrompt::class);
    }
}
