<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\AiPrompt;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Registry;
use Panth\PageBuilderAi\Controller\Adminhtml\AiPrompt\Edit;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class EditTest extends TestCase
{
    use BackendActionStubs;
    use DbStubs;

    public function testNewPromptTitle(): void
    {
        $registry = $this->createMock(Registry::class);
        $registry->expects($this->once())->method('register')->with('panth_pagebuilderai_ai_prompt', [], true);

        $this->controller([], $this->connectionStub(), $registry)->execute();

        $this->assertSame(['New AI Prompt'], $this->titles);
        $this->assertSame('Panth_PageBuilderAi::ai_prompts', $this->activeMenu);
    }

    public function testExistingPromptIsLoaded(): void
    {
        $row = ['prompt_id' => 2, 'name' => 'Default'];
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn($row);
        $registry = $this->createMock(Registry::class);
        $registry->expects($this->once())->method('register')->with('panth_pagebuilderai_ai_prompt', $row, true);

        $this->controller(['id' => 2], $connection, $registry)->execute();

        $this->assertSame(['Edit AI Prompt'], $this->titles);
    }

    private function controller(array $params, AdapterInterface $connection, Registry $registry): Edit
    {
        return new Edit(
            $this->context($this->request($params, false)),
            $this->pageFactory(),
            $registry,
            $this->resourceStub($connection)
        );
    }
}
