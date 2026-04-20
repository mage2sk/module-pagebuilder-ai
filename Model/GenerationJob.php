<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model;

use Magento\Framework\Model\AbstractModel;
use Panth\PageBuilderAi\Model\ResourceModel\GenerationJob as GenerationJobResource;

class GenerationJob extends AbstractModel
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_FAILED = 'failed';

    protected $_idFieldName = 'job_id';

    protected function _construct(): void
    {
        $this->_init(GenerationJobResource::class);
    }
}
