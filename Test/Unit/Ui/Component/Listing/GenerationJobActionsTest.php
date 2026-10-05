<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Ui\Component\Listing;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Panth\PageBuilderAi\Ui\Component\Listing\GenerationJobActions;
use PHPUnit\Framework\TestCase;

class GenerationJobActionsTest extends TestCase
{
    public function testViewLinkIsAddedForRowsWithJobId(): void
    {
        $url = $this->createStub(UrlInterface::class);
        $url->method('getUrl')->willReturnCallback(
            static fn ($path, $params) => $path . '/' . http_build_query($params)
        );
        $column = new GenerationJobActions(
            $this->createStub(ContextInterface::class),
            $this->createStub(UiComponentFactory::class),
            $url,
            [],
            ['name' => 'actions']
        );

        $result = $column->prepareDataSource(['data' => ['items' => [['job_id' => '12'], ['job_id' => 0], ['x' => 1]]]]);

        $items = $result['data']['items'];
        $this->assertSame('panth_pagebuilderai/aiSettings/viewJob/job_id=12', $items[0]['actions']['view']['href']);
        $this->assertSame('View Details', (string)$items[0]['actions']['view']['label']);
        $this->assertArrayNotHasKey('actions', $items[1]);
        $this->assertArrayNotHasKey('actions', $items[2]);
    }

    public function testEmptyDataSourceIsReturnedUnchanged(): void
    {
        $column = new GenerationJobActions(
            $this->createStub(ContextInterface::class),
            $this->createStub(UiComponentFactory::class),
            $this->createStub(UrlInterface::class)
        );

        $this->assertSame(['data' => ['items' => []]], $column->prepareDataSource(['data' => ['items' => []]]));
        $this->assertSame([], $column->prepareDataSource([]));
    }
}
