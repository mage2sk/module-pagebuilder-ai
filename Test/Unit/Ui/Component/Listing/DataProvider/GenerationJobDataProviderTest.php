<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Ui\Component\Listing\DataProvider;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use Panth\PageBuilderAi\Ui\Component\Listing\DataProvider\GenerationJobDataProvider;
use PHPUnit\Framework\TestCase;

class GenerationJobDataProviderTest extends TestCase
{
    use DbStubs;

    public function testMissingTableYieldsEmptyListing(): void
    {
        $this->assertSame(['totalRecords' => 0, 'items' => []], $this->provider($this->connectionStub())->getData());
    }

    public function testRowsAreReturnedWithCount(): void
    {
        $rows = [['job_id' => 2, 'status' => 'draft'], ['job_id' => 1, 'status' => 'failed']];
        $connection = $this->connectionStub(['panth_seo_generation_job']);
        $connection->method('fetchAll')->willReturn($rows);

        $this->assertSame(['totalRecords' => 2, 'items' => $rows], $this->provider($connection)->getData());
    }

    private function provider(AdapterInterface $connection): GenerationJobDataProvider
    {
        return new GenerationJobDataProvider(
            'jobs_listing_data_source',
            'job_id',
            'id',
            $this->createStub(ReportingInterface::class),
            $this->createStub(SearchCriteriaBuilder::class),
            $this->createStub(RequestInterface::class),
            $this->createStub(FilterBuilder::class),
            $this->resourceStub($connection)
        );
    }
}
