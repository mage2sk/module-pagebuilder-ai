<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\AiPrompt;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Controller\Adminhtml\AiPrompt\Save;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SaveTest extends TestCase
{
    use BackendActionStubs;
    use DbStubs;

    private array $writes = [];

    public function testInvalidFormKeyIsRejected(): void
    {
        $this->controller(['name' => 'x'], $this->recordingConnection(), false)->execute();

        $this->assertSame(['Invalid form key. Please refresh the page.'], $this->messagesOfType('error'));
        $this->assertSame([], $this->writes);
    }

    #[DataProvider('invalidProvider')]
    public function testValidationErrors(array $post, string $message): void
    {
        $this->controller($post, $this->recordingConnection())->execute();

        $this->assertSame([$message], $this->messagesOfType('error'));
        $this->assertSame(['*/*/edit', ['id' => 2]], $this->redirect);
        $this->assertSame([], $this->writes);
    }

    public static function invalidProvider(): array
    {
        return [
            'bad entity type' => [['prompt_id' => 2, 'entity_type' => 'order', 'name' => 'n', 'prompt_template' => 't'], 'Invalid entity type.'],
            'missing template' => [['prompt_id' => 2, 'name' => 'n'], 'Prompt name and prompt template are required.'],
        ];
    }

    public function testDefaultPromptClearsOtherDefaultsBeforeUpdate(): void
    {
        $this->controller([
            'prompt_id' => '2',
            'entity_type' => 'cms_page',
            'name' => 'Landing',
            'prompt_template' => 'Write {{name}}',
            'is_default' => '1',
        ], $this->recordingConnection())->execute();

        $this->assertCount(2, $this->writes);
        $this->assertSame(['update', ['is_default' => 0], ['entity_type = ?' => 'cms_page', 'prompt_id != ?' => 2]], $this->writes[0]);
        [$op, $row, $where] = $this->writes[1];
        $this->assertSame('update', $op);
        $this->assertSame(['prompt_id = ?' => 2], $where);
        $this->assertSame(1, $row['is_default']);
        $this->assertSame('Write {{name}}', $row['prompt_template']);
        $this->assertSame(['AI Prompt saved.'], $this->messagesOfType('success'));
        $this->assertSame(['*/*/', []], $this->redirect);
    }

    public function testNewPromptIsInsertedAndBackUsesNewId(): void
    {
        $this->controller(['name' => 'N', 'prompt_template' => 'T', 'back' => '1'], $this->recordingConnection())->execute();

        $this->assertCount(1, $this->writes);
        [$op, $row] = $this->writes[0];
        $this->assertSame('insert', $op);
        $this->assertSame('product', $row['entity_type']);
        $this->assertSame(0, $row['is_default']);
        $this->assertSame('2026-01-01 00:00:00', $row['created_at']);
        $this->assertSame(['*/*/edit', ['id' => 33]], $this->redirect);
    }

    public function testDatabaseErrorIsReported(): void
    {
        $connection = $this->connectionStub([], Mysql::class);
        $connection->method('update')->willThrowException(new \RuntimeException('Lock wait timeout'));

        $this->controller(['prompt_id' => 2, 'name' => 'N', 'prompt_template' => 'T'], $connection)->execute();

        $this->assertSame(['Lock wait timeout'], $this->messagesOfType('error'));
        $this->assertSame(['*/*/edit', ['id' => 2]], $this->redirect);
    }

    private function recordingConnection(): AdapterInterface
    {
        $connection = $this->connectionStub([], Mysql::class);
        $connection->method('update')->willReturnCallback(function ($table, array $row, $where) {
            $this->writes[] = ['update', $row, $where];
            return 1;
        });
        $connection->method('insert')->willReturnCallback(function ($table, array $row) {
            $this->writes[] = ['insert', $row, null];
            return 1;
        });
        $connection->method('lastInsertId')->willReturn('33');
        return $connection;
    }

    private function controller(array $post, AdapterInterface $connection, bool $validKey = true): Save
    {
        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturn('2026-01-01 00:00:00');

        return new Save(
            $this->context($this->request($post, true, $post)),
            $this->resourceStub($connection),
            $dateTime,
            $this->formKey($validKey)
        );
    }
}
