<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Panth\PageBuilderAi\Ui\Component\Listing\Column\AiKnowledgeActions;
use PHPUnit\Framework\TestCase;

class AiKnowledgeActionsTest extends TestCase
{
    public function testEditAndDeleteActionsAreAdded(): void
    {
        $url = $this->createStub(UrlInterface::class);
        $url->method('getUrl')->willReturnCallback(static fn ($path, $params) => $path . '#' . $params['id']);
        $column = new AiKnowledgeActions(
            $this->createStub(ContextInterface::class),
            $this->createStub(UiComponentFactory::class),
            $url,
            [],
            ['name' => 'actions']
        );

        $result = $column->prepareDataSource(['data' => ['items' => [['knowledge_id' => 3], ['title' => 'none']]]]);

        $actions = $result['data']['items'][0]['actions'];
        $this->assertSame(AiKnowledgeActions::URL_PATH_EDIT . '#3', $actions['edit']['href']);
        $this->assertSame(AiKnowledgeActions::URL_PATH_DELETE . '#3', $actions['delete']['href']);
        $this->assertSame('Edit', $actions['edit']['label']);
        $this->assertTrue($actions['delete']['post']);
        $this->assertSame('Delete Knowledge Entry', $actions['delete']['confirm']['title']);
        $this->assertArrayNotHasKey('actions', $result['data']['items'][1]);
    }

    public function testDataSourceWithoutItemsIsUntouched(): void
    {
        $column = new AiKnowledgeActions(
            $this->createStub(ContextInterface::class),
            $this->createStub(UiComponentFactory::class),
            $this->createStub(UrlInterface::class)
        );

        $this->assertSame(['data' => ['totalRecords' => 0]], $column->prepareDataSource(['data' => ['totalRecords' => 0]]));
    }
}
