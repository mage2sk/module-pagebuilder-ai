<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RequestLog extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('panth_pagebuilderai_request_log', 'log_id');
    }
}
