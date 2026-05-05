<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\ResourceModel\GenerationJob;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Panth\PageBuilderAi\Model\GenerationJob as JobModel;
use Panth\PageBuilderAi\Model\ResourceModel\GenerationJob as JobResource;

class Collection extends AbstractCollection implements SearchResultInterface
{
    protected $_idFieldName = 'job_id';

    /**
     * @var \Magento\Framework\Api\Search\AggregationInterface|null
     */
    private $aggregations;

    protected function _construct(): void
    {
        $this->_init(JobModel::class, JobResource::class);
    }

    public function getAggregations()
    {
        return $this->aggregations;
    }

    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    public function getSearchCriteria()
    {
        return null;
    }

    public function setSearchCriteria(?SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    public function getTotalCount()
    {
        return $this->getSize();
    }

    public function setTotalCount($totalCount)
    {
        return $this;
    }

    public function setItems(?array $items = null)
    {
        return $this;
    }
}
