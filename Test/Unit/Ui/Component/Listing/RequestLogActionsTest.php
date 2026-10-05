<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Ui\Component\Listing;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Panth\PageBuilderAi\Ui\Component\Listing\RequestLogActions;
use PHPUnit\Framework\TestCase;

class RequestLogActionsTest extends TestCase
{
    public function testViewAndPostDeleteLinksAreAdded(): void
    {
        $url = $this->createStub(UrlInterface::class);
        $url->method('getUrl')->willReturnCallback(
            static fn ($path, $params) => $path . '/' . http_build_query($params)
        );
        $column = new RequestLogActions(
            $this->createStub(ContextInterface::class),
            $this->createStub(UiComponentFactory::class),
            $url,
            [],
            ['name' => 'actions']
        );

        $result = $column->prepareDataSource(['data' => ['items' => [['log_id' => '5'], ['log_id' => null]]]]);

        $actions = $result['data']['items'][0]['actions'];
        $this->assertSame('panth_pagebuilderai/requestLog/view/log_id=5', $actions['view']['href']);
        $this->assertSame('panth_pagebuilderai/requestLog/delete/log_id=5', $actions['delete']['href']);
        $this->assertTrue($actions['delete']['post']);
        $this->assertSame('Delete log #5', (string)$actions['delete']['confirm']['title']);
        $this->assertArrayNotHasKey('actions', $result['data']['items'][1]);
    }

    public function testMissingItemsAreIgnored(): void
    {
        $column = new RequestLogActions(
            $this->createStub(ContextInterface::class),
            $this->createStub(UiComponentFactory::class),
            $this->createStub(UrlInterface::class)
        );

        $this->assertSame(['data' => []], $column->prepareDataSource(['data' => []]));
    }
}
