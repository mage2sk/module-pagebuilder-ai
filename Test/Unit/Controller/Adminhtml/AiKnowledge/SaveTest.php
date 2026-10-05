<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\AiKnowledge;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Controller\Adminhtml\AiKnowledge\Save;
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
        $this->controller(['title' => 'x'], $this->connectionStub(), false)->execute();

        $this->assertSame(['Invalid form key. Please refresh the page.'], $this->messagesOfType('error'));
        $this->assertSame(['*/*/', []], $this->redirect);
    }

    public function testEmptyPostRedirectsSilently(): void
    {
        $this->controller([], $this->connectionStub())->execute();

        $this->assertSame([], $this->messages);
        $this->assertSame(['*/*/', []], $this->redirect);
    }

    #[DataProvider('invalidProvider')]
    public function testValidationErrorsReturnToEditForm(array $post, string $message): void
    {
        $this->controller($post, $this->recordingConnection())->execute();

        $this->assertSame([$message], $this->messagesOfType('error'));
        $this->assertSame(['*/*/edit', ['id' => 3]], $this->redirect);
        $this->assertSame([], $this->writes);
    }

    public static function invalidProvider(): array
    {
        return [
            'unknown category' => [['knowledge_id' => 3, 'category' => 'not_a_category', 'title' => 't', 'content' => 'c'], 'Invalid category.'],
            'blank title' => [['knowledge_id' => 3, 'category' => 'seo', 'title' => '  ', 'content' => 'c'], 'Title and content are required.'],
            'blank content' => [['knowledge_id' => 3, 'title' => 't'], 'Title and content are required.'],
        ];
    }

    public function testExistingEntryIsUpdatedAndBackReturnsToEdit(): void
    {
        $this->controller([
            'knowledge_id' => '3',
            'category' => 'html_patterns',
            'title' => str_repeat('t', 300),
            'content' => 'Body',
            'tags' => str_repeat('g', 600),
            'subcategory' => str_repeat('s', 200),
            'is_active' => '0',
            'sort_order' => '4',
            'back' => 'edit',
        ], $this->recordingConnection())->execute();

        $this->assertCount(1, $this->writes);
        [$op, $row, $where] = $this->writes[0];
        $this->assertSame('update', $op);
        $this->assertSame(['knowledge_id = ?' => 3], $where);
        $this->assertSame(255, strlen($row['title']));
        $this->assertSame(512, strlen($row['tags']));
        $this->assertSame(128, strlen($row['subcategory']));
        $this->assertSame(0, $row['is_active']);
        $this->assertSame(4, $row['sort_order']);
        $this->assertArrayNotHasKey('created_at', $row);
        $this->assertSame(['Knowledge entry saved.'], $this->messagesOfType('success'));
        $this->assertSame(['*/*/edit', ['id' => 3]], $this->redirect);
    }

    public function testNewEntryIsInsertedWithDefaults(): void
    {
        $this->controller(['title' => 'Rule', 'content' => 'Body'], $this->recordingConnection())->execute();

        [$op, $row] = $this->writes[0];
        $this->assertSame('insert', $op);
        $this->assertSame('seo', $row['category']);
        $this->assertSame(1, $row['is_active']);
        $this->assertSame('2026-01-01 00:00:00', $row['created_at']);
        $this->assertSame(['*/*/', []], $this->redirect);
    }

    public function testBundledKnowledgeCategoryIsAccepted(): void
    {
        $this->controller(
            ['title' => 'Rule', 'content' => 'Body', 'category' => 'panth_modules'],
            $this->recordingConnection()
        )->execute();

        $this->assertSame('panth_modules', $this->writes[0][1]['category']);
        $this->assertSame([], $this->messagesOfType('error'));
    }

    public function testDatabaseErrorIsReported(): void
    {
        $connection = $this->connectionStub([], Mysql::class);
        $connection->method('insert')->willThrowException(new \RuntimeException('Duplicate entry'));

        $this->controller(['title' => 'Rule', 'content' => 'Body', 'back' => 1], $connection)->execute();

        $this->assertSame(['Duplicate entry'], $this->messagesOfType('error'));
        $this->assertSame(['*/*/edit', ['id' => 0]], $this->redirect);
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
        $connection->method('lastInsertId')->willReturn('21');
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
