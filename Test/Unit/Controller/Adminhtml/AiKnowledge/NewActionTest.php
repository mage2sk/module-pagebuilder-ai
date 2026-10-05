<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\AiKnowledge;

use Magento\Backend\Model\View\Result\Forward;
use Magento\Framework\Controller\ResultFactory;
use Panth\PageBuilderAi\Controller\Adminhtml\AiKnowledge\NewAction as KnowledgeNewAction;
use Panth\PageBuilderAi\Controller\Adminhtml\AiPrompt\NewAction as PromptNewAction;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NewActionTest extends TestCase
{
    use BackendActionStubs;

    #[DataProvider('controllerProvider')]
    public function testNewActionForwardsToEdit(string $class): void
    {
        $forward = $this->createMock(Forward::class);
        $forward->expects($this->once())->method('forward')->with('edit')->willReturnSelf();
        $resultFactory = $this->createMock(ResultFactory::class);
        $resultFactory->expects($this->once())->method('create')->with(ResultFactory::TYPE_FORWARD)->willReturn($forward);

        $controller = new $class($this->context($this->request(), ['getResultFactory' => $resultFactory]));

        $this->assertSame($forward, $controller->execute());
    }

    public static function controllerProvider(): array
    {
        return [[KnowledgeNewAction::class], [PromptNewAction::class]];
    }
}
