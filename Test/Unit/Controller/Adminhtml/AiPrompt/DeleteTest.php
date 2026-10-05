<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\AiPrompt;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Panth\PageBuilderAi\Controller\Adminhtml\AiPrompt\Delete;
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
    }

    public function testPromptIsDeleted(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())->method('delete')
            ->with('panth_seo_ai_prompt', ['prompt_id = ?' => 7])
            ->willReturn(1);

        $this->controller(['id' => '7'], $connection)->execute();

        $this->assertSame(['AI Prompt deleted.'], $this->messagesOfType('success'));
        $this->assertSame(['*/*/', []], $this->redirect);
    }

    public function testNonNumericIdIsIgnored(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->never())->method('delete');

        $this->controller(['id' => 'abc'], $connection)->execute();

        $this->assertSame([], $this->messages);
    }

    public function testDeleteFailureIsReported(): void
    {
        $connection = $this->createStub(AdapterInterface::class);
        $connection->method('delete')->willThrowException(new \RuntimeException('gone away'));

        $this->controller(['id' => 7], $connection)->execute();

        $this->assertSame(['gone away'], $this->messagesOfType('error'));
    }

    private function controller(array $params, AdapterInterface $connection, bool $validKey = true): Delete
    {
        return new Delete($this->context($this->request($params)), $this->resourceStub($connection), $this->formKey($validKey));
    }
}
