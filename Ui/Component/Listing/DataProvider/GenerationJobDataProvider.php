<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Ui\Component\Listing\DataProvider;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class GenerationJobDataProvider extends DataProvider
{
    private ResourceConnection $resource;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        \Magento\Framework\Api\Search\ReportingInterface $reporting,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        ResourceConnection $resource,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->resource = $resource;
    }

    public function getData(): array
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('panth_seo_generation_job');
        if (!$connection->isTableExists($table)) {
            return ['totalRecords' => 0, 'items' => []];
        }
        $rows = $connection->fetchAll(
            $connection->select()->from($table)->order('updated_at DESC')->limit(500)
        );
        return ['totalRecords' => count($rows), 'items' => $rows];
    }
}
