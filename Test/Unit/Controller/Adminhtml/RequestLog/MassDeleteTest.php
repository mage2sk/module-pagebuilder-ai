<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\RequestLog;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Panth\PageBuilderAi\Controller\Adminhtml\RequestLog\MassDelete;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class MassDeleteTest extends TestCase
{
    use BackendActionStubs;
    use DbStubs;

    public function testInvalidFormKeyIsRejected(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->never())->method('delete');

        $this->controller(['selected' => [1]], $connection, false)->execute();

        $this->assertSame(['Invalid form key. Please refresh and try again.'], $this->messagesOfType('error'));
    }

    public function testEmptySelectionIsRejected(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->never())->method('delete');

        $this->controller(['selected' => ['0', 'x']], $connection)->execute();

        $this->assertSame(['Please select at least one log entry.'], $this->messagesOfType('error'));
    }

    public function testSelectedEntriesAreDeleted(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())->method('delete')
            ->with('panth_pagebuilderai_request_log', ['log_id IN (?)' => [0 => 3, 2 => 8]])
            ->willReturn(2);

        $this->controller(['selected' => ['3', '0', '8']], $connection)->execute();

        $this->assertSame(['Deleted 2 log entr(ies).'], $this->messagesOfType('success'));
        $this->assertSame(['*/*/', []], $this->redirect);
    }

    public function testFailureShowsGenericError(): void
    {
        $connection = $this->createStub(AdapterInterface::class);
        $connection->method('delete')->willThrowException(new \RuntimeException('detail'));

        $this->controller(['selected' => [3]], $connection)->execute();

        $this->assertSame(['Could not delete the selected entries.'], $this->messagesOfType('error'));
    }

    private function controller(array $params, AdapterInterface $connection, bool $validKey = true): MassDelete
    {
        return new MassDelete($this->context($this->request($params)), $this->resourceStub($connection), $this->formKey($validKey));
    }
}
