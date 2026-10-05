<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Block\Adminhtml;

use Magento\Framework\UrlInterface;
use Panth\PageBuilderAi\Block\Adminhtml\AiKnowledge\Edit\BackButton as KnowledgeBackButton;
use Panth\PageBuilderAi\Block\Adminhtml\AiPrompt\Edit\BackButton as PromptBackButton;
use Panth\PageBuilderAi\Block\Adminhtml\GenericBackButton;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GenericBackButtonTest extends TestCase
{
    #[DataProvider('buttonProvider')]
    public function testBackButtonPointsToGrid(string $class): void
    {
        $url = $this->createMock(UrlInterface::class);
        $url->expects($this->once())->method('getUrl')->with('*/*/')->willReturn('https://admin.test/grid/');

        $data = (new $class($url))->getButtonData();

        $this->assertSame('Back', (string)$data['label']);
        $this->assertSame("location.href = 'https://admin.test/grid/';", $data['on_click']);
        $this->assertSame('back', $data['class']);
        $this->assertSame(10, $data['sort_order']);
    }

    public static function buttonProvider(): array
    {
        return [[GenericBackButton::class], [KnowledgeBackButton::class], [PromptBackButton::class]];
    }
}
