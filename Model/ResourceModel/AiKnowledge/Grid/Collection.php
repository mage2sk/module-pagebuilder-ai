<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\ResourceModel\AiKnowledge\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    protected function _initSelect(): static
    {
        parent::_initSelect();
        return $this;
    }
}
