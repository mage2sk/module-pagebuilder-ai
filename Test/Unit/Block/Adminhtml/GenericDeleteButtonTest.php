<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Block\Adminhtml;

use Magento\Framework\App\Request\Http;
use Magento\Framework\UrlInterface;
use Panth\PageBuilderAi\Block\Adminhtml\GenericDeleteButton;
use PHPUnit\Framework\TestCase;

class GenericDeleteButtonTest extends TestCase
{
    public function testNoButtonForNewEntity(): void
    {
        $url = $this->createMock(UrlInterface::class);
        $url->expects($this->never())->method('getUrl');

        $this->assertSame([], (new GenericDeleteButton($url, $this->request(null)))->getButtonData());
    }

    public function testDeleteUrlUsesCurrentRouteAndController(): void
    {
        $url = $this->createMock(UrlInterface::class);
        $url->expects($this->once())
            ->method('getUrl')
            ->with('panth_pagebuilderai/aiprompt/delete', ['id' => 7])
            ->willReturn('https://admin.test/delete/7');

        $data = (new GenericDeleteButton($url, $this->request('7')))->getButtonData();

        $this->assertSame('Delete', (string)$data['label']);
        $this->assertSame('delete', $data['class']);
        $this->assertSame(20, $data['sort_order']);
        $this->assertStringStartsWith("deleteConfirm('Are you sure you want to delete this item?'", $data['on_click']);
        $this->assertStringEndsWith("'https://admin.test/delete/7')", $data['on_click']);
    }

    private function request(?string $id): Http
    {
        $request = $this->createStub(Http::class);
        $request->method('getParam')->willReturn($id);
        $request->method('getRouteName')->willReturn('panth_pagebuilderai');
        $request->method('getControllerName')->willReturn('aiprompt');
        return $request;
    }
}
