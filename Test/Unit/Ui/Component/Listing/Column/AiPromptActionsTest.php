<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Panth\PageBuilderAi\Ui\Component\Listing\Column\AiPromptActions;
use PHPUnit\Framework\TestCase;

class AiPromptActionsTest extends TestCase
{
    public function testEditAndDeleteActionsAreAdded(): void
    {
        $url = $this->createStub(UrlInterface::class);
        $url->method('getUrl')->willReturnCallback(static fn ($path, $params) => $path . '#' . $params['id']);
        $column = new AiPromptActions(
            $this->createStub(ContextInterface::class),
            $this->createStub(UiComponentFactory::class),
            $url,
            [],
            ['name' => 'row_actions']
        );

        $result = $column->prepareDataSource(['data' => ['items' => [['prompt_id' => '8'], ['knowledge_id' => 1]]]]);

        $actions = $result['data']['items'][0]['row_actions'];
        $this->assertSame('panth_pagebuilderai/aiprompt/edit#8', $actions['edit']['href']);
        $this->assertSame('panth_pagebuilderai/aiprompt/delete#8', $actions['delete']['href']);
        $this->assertSame('Delete AI Prompt', $actions['delete']['confirm']['title']);
        $this->assertArrayNotHasKey('row_actions', $result['data']['items'][1]);
    }
}
