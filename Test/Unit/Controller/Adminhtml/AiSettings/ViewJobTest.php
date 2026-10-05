<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\AiSettings;

use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\UrlInterface;
use Panth\PageBuilderAi\Controller\Adminhtml\AiSettings\ViewJob;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ViewJobTest extends TestCase
{
    use BackendActionStubs;
    use DbStubs;

    public function testInvalidIdRedirectsToJobs(): void
    {
        $this->controller(['job_id' => 'abc'], $this->connectionStub())->execute();

        $this->assertSame(['Invalid job id.'], $this->messagesOfType('error'));
        $this->assertSame(['panth_pagebuilderai/aiSettings/jobs', []], $this->redirect);
    }

    public function testMissingJobRedirectsToJobs(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn(false);

        $this->controller(['job_id' => 9], $connection)->execute();

        $this->assertSame(['Generation job not found.'], $this->messagesOfType('error'));
    }

    #[DataProvider('entityProvider')]
    public function testEntitiesAreResolvedInRequestedOrder(string $type, array $rows, array $expected): void
    {
        $job = [
            'job_id' => 9,
            'entity_type' => $type,
            'options' => json_encode(['entity_ids' => [6, '5', 0], 'results' => [5 => ['draft_title' => 'T']]]),
        ];
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn($job);
        $connection->method('fetchAll')->willReturn($rows);
        $block = new DataObject();

        $this->controller(['job_id' => '9'], $connection, $block)->execute();

        $this->assertSame('Panth_PageBuilderAi::ai_jobs', $this->activeMenu);
        $this->assertSame(['AI Job #9'], $this->titles);
        $this->assertSame($job, $block->getData('job_row'));
        $this->assertSame([5 => ['draft_title' => 'T']], $block->getData('results'));
        $this->assertSame($expected, $block->getData('entities'));
    }

    public static function entityProvider(): array
    {
        $missing = ['id' => 6, 'name' => '#6', 'edit_url' => ''];
        return [
            'product falls back to sku' => [
                'product',
                [['entity_id' => '5', 'sku' => 'SKU5', 'name' => null]],
                [$missing, ['id' => 5, 'name' => 'SKU5', 'edit_url' => 'catalog/product/edit?id=5']],
            ],
            'category' => [
                'category',
                [['entity_id' => 5, 'name' => 'Shoes'], ['entity_id' => 6, 'name' => '']],
                [
                    ['id' => 6, 'name' => '#6', 'edit_url' => 'catalog/category/edit?id=6'],
                    ['id' => 5, 'name' => 'Shoes', 'edit_url' => 'catalog/category/edit?id=5'],
                ],
            ],
            'cms page falls back to identifier' => [
                'cms_page',
                [['page_id' => 5, 'title' => '', 'identifier' => 'about']],
                [$missing, ['id' => 5, 'name' => 'about', 'edit_url' => 'cms/page/edit?page_id=5']],
            ],
            'unsupported type' => [
                'faq',
                [],
                [$missing, ['id' => 5, 'name' => '#5', 'edit_url' => '']],
            ],
        ];
    }

    public function testNameLookupFailureStillListsIds(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn(['entity_type' => 'product', 'options' => '{"entity_ids":[3]}']);
        $connection->method('fetchAll')->willThrowException(new \RuntimeException('db'));
        $block = new DataObject();

        $this->controller(['job_id' => 1], $connection, $block)->execute();

        $this->assertSame([['id' => 3, 'name' => '#3', 'edit_url' => '']], $block->getData('entities'));
        $this->assertSame([], $block->getData('results'));
    }

    private function controller(array $params, AdapterInterface $connection, mixed $block = false): ViewJob
    {
        $url = $this->createStub(UrlInterface::class);
        $url->method('getUrl')->willReturnCallback(static fn ($path, $p = []) => $path . '?' . http_build_query($p));

        return new ViewJob(
            $this->context($this->request($params, false), ['getUrl' => $url]),
            $this->pageFactory($block),
            $this->resourceStub($connection)
        );
    }
}
