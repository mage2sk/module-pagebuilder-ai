<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\AiKnowledge;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Panth\PageBuilderAi\Controller\Adminhtml\AiKnowledge\Delete;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class DeleteTest extends TestCase
{
    use BackendActionStubs;
    use DbStubs;

    public function testInvalidFormKeyDoesNotDelete(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->never())->method('delete');

        $this->controller(['id' => 4], $connection, false)->execute();

        $this->assertSame(['Invalid form key. Please refresh the page.'], $this->messagesOfType('error'));
        $this->assertSame(['*/*/', []], $this->redirect);
    }

    public function testMissingIdDoesNothing(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->never())->method('delete');

        $this->controller([], $connection)->execute();

        $this->assertSame([], $this->messages);
        $this->assertSame(['*/*/', []], $this->redirect);
    }

    public function testEntryIsDeleted(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())->method('delete')
            ->with('panth_seo_ai_knowledge', ['knowledge_id = ?' => 4])
            ->willReturn(1);

        $this->controller(['id' => '4'], $connection)->execute();

        $this->assertSame(['Knowledge entry deleted.'], $this->messagesOfType('success'));
    }

    public function testDeleteFailureIsReported(): void
    {
        $connection = $this->createStub(AdapterInterface::class);
        $connection->method('delete')->willThrowException(new \RuntimeException('FK constraint'));

        $this->controller(['id' => 4], $connection)->execute();

        $this->assertSame(['FK constraint'], $this->messagesOfType('error'));
    }

    private function controller(array $params, AdapterInterface $connection, bool $validKey = true): Delete
    {
        return new Delete($this->context($this->request($params)), $this->resourceStub($connection), $this->formKey($validKey));
    }
}
