<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\RequestLog;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Panth\PageBuilderAi\Controller\Adminhtml\RequestLog\Delete;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class DeleteTest extends TestCase
{
    use BackendActionStubs;
    use DbStubs;

    public function testLogEntryIsDeleted(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())->method('delete')
            ->with('panth_pagebuilderai_request_log', ['log_id = ?' => 15])
            ->willReturn(1);

        $this->controller(['log_id' => '15'], $connection)->execute();

        $this->assertSame(['Log entry deleted.'], $this->messagesOfType('success'));
        $this->assertSame(['*/*/', []], $this->redirect);
    }

    public function testMissingIdDoesNothing(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->never())->method('delete');

        $this->controller([], $connection)->execute();

        $this->assertSame([], $this->messages);
    }

    public function testFailureShowsGenericError(): void
    {
        $connection = $this->createStub(AdapterInterface::class);
        $connection->method('delete')->willThrowException(new \RuntimeException('SQLSTATE detail'));

        $this->controller(['log_id' => 1], $connection)->execute();

        $this->assertSame(['Could not delete log entry.'], $this->messagesOfType('error'));
    }

    private function controller(array $params, AdapterInterface $connection): Delete
    {
        return new Delete($this->context($this->request($params)), $this->resourceStub($connection));
    }
}
