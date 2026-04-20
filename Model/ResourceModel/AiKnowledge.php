<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AiKnowledge extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('panth_seo_ai_knowledge', 'knowledge_id');
    }
}
