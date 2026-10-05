<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\AiSettings;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Ui\Component\MassAction\Filter;
use Panth\PageBuilderAi\Controller\Adminhtml\AiSettings\ApproveBatch;
use Panth\PageBuilderAi\Model\GenerationJob;
use Panth\PageBuilderAi\Model\ResourceModel\GenerationJob\Grid\Collection as JobCollection;
use Panth\PageBuilderAi\Model\ResourceModel\GenerationJob\Grid\CollectionFactory as JobCollectionFactory;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ApproveBatchTest extends TestCase
{
    use BackendActionStubs;
    use DbStubs;

    private const JOBS_PATH = 'panth_pagebuilderai/aiSettings/jobs';

    private array $updates = [];

    private array $rows = [];

    public function testInvalidFormKeyIsRejected(): void
    {
        $this->controller($this->request(['selected' => [1]]), [], false)->execute();

        $this->assertSame(['Invalid form key. Please refresh the page.'], $this->messagesOfType('error'));
        $this->assertSame([self::JOBS_PATH, []], $this->redirect);
    }

    public function testNoSelectionIsRejected(): void
    {
        $this->controller($this->request(['selected' => ['0']]))->execute();

        $this->assertSame(['No jobs selected.'], $this->messagesOfType('error'));
    }

    public function testDraftProductResultsAreApplied(): void
    {
        $product = (new \ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $products = $this->createMock(ProductRepositoryInterface::class);
        $products->expects($this->once())->method('getById')->with(11, true, 2)->willReturn($product);
        $products->expects($this->once())->method('save')->with($product);
        $this->rows[5] = $this->job('product', [
            'results' => [
                11 => ['draft_title' => 'New title', 'draft_description' => ''],
                12 => ['draft_title' => '', 'draft_description' => ''],
                13 => 'not an array',
            ],
        ]);

        $this->controller($this->request(['selected' => ['5']]), ['products' => $products])->execute();

        $this->assertSame('New title', $product->getData('meta_title'));
        $this->assertNull($product->getData('meta_description'));
        $this->assertSame(2, $product->getData('store_id'));
        $this->assertSame(GenerationJob::STATUS_APPROVED, $this->updates[0]['status']);
        $this->assertSame(['1 job(s) approved.'], $this->messagesOfType('success'));
    }

    public function testLegacySingleEntityDraftOnCategoryAndPage(): void
    {
        $category = (new \ReflectionClass(Category::class))->newInstanceWithoutConstructor();
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->expects($this->once())->method('get')->with(4, 2)->willReturn($category);
        $categories->expects($this->once())->method('save')->with($category);
        $page = (new \ReflectionClass(Page::class))->newInstanceWithoutConstructor();
        $pages = $this->createMock(PageRepositoryInterface::class);
        $pages->expects($this->once())->method('getById')->with(8)->willReturn($page);
        $pages->expects($this->once())->method('save')->with($page);
        $this->rows[1] = $this->job('category', ['entity_id' => 4, 'draft_title' => 'Cat', 'draft_description' => 'Cat desc']);
        $this->rows[2] = $this->job('cms_page', ['results' => [8 => ['draft_description' => 'Page desc']]]);

        $this->controller(
            $this->request(['job_ids' => [1, 2]]),
            ['categories' => $categories, 'pages' => $pages]
        )->execute();

        $this->assertSame('Cat', $category->getData('meta_title'));
        $this->assertSame('Cat desc', $category->getData('meta_description'));
        $this->assertSame('Page desc', $page->getData('meta_description'));
        $this->assertNull($page->getData('meta_title'));
        $this->assertSame(['2 job(s) approved.'], $this->messagesOfType('success'));
    }

    public function testNonDraftJobsAreSkippedWithNotice(): void
    {
        $this->rows[3] = $this->job('product', [], GenerationJob::STATUS_PENDING);

        $this->controller($this->request(['selected' => [3, 99]]))->execute();

        $this->assertSame([], $this->updates);
        $this->assertCount(1, $this->messagesOfType('notice'));
        $this->assertStringStartsWith('1 job(s) were skipped', $this->messagesOfType('notice')[0]);
        $this->assertSame([], $this->messagesOfType('error'));
    }

    public function testJobWithoutResultsRecordsError(): void
    {
        $this->rows[6] = $this->job('product', ['results' => []]);

        $this->controller($this->request(['selected' => [6]]))->execute();

        $this->assertSame('No draft results to apply.', $this->updates[0]['error_message']);
        $this->assertArrayNotHasKey('status', $this->updates[0]);
        $this->assertSame(['No matching jobs found for approval.'], $this->messagesOfType('error'));
    }

    public function testMassActionFilterSelectionTakesPrecedence(): void
    {
        $collection = $this->createStub(JobCollection::class);
        $collection->method('getAllIds')->willReturn(['7']);
        $filter = $this->createStub(Filter::class);
        $filter->method('getCollection')->willReturn($collection);
        $this->rows[7] = $this->job('product', [], GenerationJob::STATUS_APPROVED);

        $this->controller($this->request(['selected' => [3]]), ['filter' => $filter])->execute();

        $this->assertStringStartsWith('1 job(s) were skipped', $this->messagesOfType('notice')[0]);
    }

    private function job(string $type, array $options, string $status = GenerationJob::STATUS_DRAFT): array
    {
        return ['entity_type' => $type, 'store_id' => '2', 'status' => $status, 'options' => json_encode($options)];
    }

    private function connection(): AdapterInterface
    {
        $lastId = 0;
        $select = $this->createStub(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('limit')->willReturnSelf();
        $select->method('where')->willReturnCallback(function ($cond, $value = null) use (&$lastId, &$select) {
            $lastId = (int)$value;
            return $select;
        });
        $connection = $this->createStub(AdapterInterface::class);
        $connection->method('select')->willReturn($select);
        $connection->method('fetchRow')->willReturnCallback(function () use (&$lastId) {
            return $this->rows[$lastId] ?? false;
        });
        $connection->method('update')->willReturnCallback(function ($table, array $data) {
            $this->updates[] = $data;
            return 1;
        });
        return $connection;
    }

    private function controller(Http $request, array $deps = [], bool $validKey = true): ApproveBatch
    {
        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturn('2026-01-01 00:00:00');
        $filter = $deps['filter'] ?? null;
        if ($filter === null) {
            $filter = $this->createStub(Filter::class);
            $filter->method('getCollection')->willThrowException(new \RuntimeException('no namespace'));
        }
        $collectionFactory = $this->createStub(JobCollectionFactory::class);
        $collectionFactory->method('create')->willReturn($this->createStub(JobCollection::class));

        return new ApproveBatch(
            $this->context($request),
            $this->resourceStub($this->connection()),
            $dateTime,
            $deps['products'] ?? $this->createStub(ProductRepositoryInterface::class),
            $deps['categories'] ?? $this->createStub(CategoryRepositoryInterface::class),
            $deps['pages'] ?? $this->createStub(PageRepositoryInterface::class),
            $this->formKey($validKey),
            $filter,
            $collectionFactory,
            new NullLogger()
        );
    }
}
