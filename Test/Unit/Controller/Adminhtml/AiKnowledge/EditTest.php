<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\AiKnowledge;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Registry;
use Panth\PageBuilderAi\Controller\Adminhtml\AiKnowledge\Edit;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class EditTest extends TestCase
{
    use BackendActionStubs;
    use DbStubs;

    public function testNewEntryRegistersEmptyRow(): void
    {
        $registry = $this->createMock(Registry::class);
        $registry->expects($this->once())->method('register')->with('panth_pagebuilderai_ai_knowledge', [], true);

        $this->controller([], $this->connectionStub(), $registry)->execute();

        $this->assertSame(['New Knowledge Entry'], $this->titles);
        $this->assertSame('Panth_PageBuilderAi::ai_knowledge', $this->activeMenu);
    }

    public function testExistingEntryIsLoaded(): void
    {
        $row = ['knowledge_id' => 4, 'title' => 'Rule'];
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn($row);
        $registry = $this->createMock(Registry::class);
        $registry->expects($this->once())->method('register')->with('panth_pagebuilderai_ai_knowledge', $row, true);

        $this->controller(['id' => '4'], $connection, $registry)->execute();

        $this->assertSame(['Edit Knowledge Entry'], $this->titles);
    }

    public function testUnknownIdRegistersEmptyRow(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn(false);
        $registry = $this->createMock(Registry::class);
        $registry->expects($this->once())->method('register')->with('panth_pagebuilderai_ai_knowledge', [], true);

        $this->controller(['id' => 99], $connection, $registry)->execute();
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
