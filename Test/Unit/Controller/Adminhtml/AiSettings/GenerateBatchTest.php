<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\AiSettings;

use Magento\Framework\App\Request\Http;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Controller\Adminhtml\AiSettings\GenerateBatch;
use Panth\PageBuilderAi\Model\GenerationJob;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GenerateBatchTest extends TestCase
{
    use BackendActionStubs;
    use DbStubs;

    #[DataProvider('rejectedProvider')]
    public function testInvalidRequestsAreRejected(array $params, bool $isPost, bool $validKey, string $message): void
    {
        $publisher = $this->createMock(PublisherInterface::class);
        $publisher->expects($this->never())->method('publish');

        $this->controller($this->request($params, $isPost), $this->connectionStub(), $publisher, $validKey)->execute();

        $this->assertSame([$message], $this->messagesOfType('error'));
        $this->assertSame(['*/*/', []], $this->redirect);
    }

    public static function rejectedProvider(): array
    {
        return [
            'get request' => [['ids' => [1]], false, true, 'Invalid form key. Please refresh the page.'],
            'bad form key' => [['ids' => [1]], true, false, 'Invalid form key. Please refresh the page.'],
            'bad entity type' => [['entity_type' => 'faq', 'ids' => [1]], true, true, 'Invalid entity type.'],
            'no ids' => [['ids' => ['0', 'abc', -3]], true, true, 'No entities selected.'],
            'too many ids' => [['ids' => range(1, 501)], true, true, 'A job can contain at most 500 entities.'],
        ];
    }

    public function testJobIsInsertedAndPublished(): void
    {
        $row = null;
        $connection = $this->connectionStub([], Mysql::class);
        $connection->method('insert')->willReturnCallback(static function ($table, array $data) use (&$row) {
            $row = $data;
            return 1;
        });
        $connection->method('lastInsertId')->willReturn('77');
        $publisher = $this->createMock(PublisherInterface::class);
        $publisher->expects($this->once())->method('publish')
            ->with('panth_pagebuilderai.generate_meta', '{"job_id":77}');

        $this->controller(
            $this->request(['entity_type' => 'category', 'store_id' => '2', 'ids' => ['3', '3', '9', '0']]),
            $connection,
            $publisher
        )->execute();

        $this->assertSame('category', $row['entity_type']);
        $this->assertSame(2, $row['store_id']);
        $this->assertSame(2, $row['total']);
        $this->assertSame(GenerationJob::STATUS_PENDING, $row['status']);
        $this->assertSame('{"entity_ids":[3,9]}', $row['options']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $row['uuid']);
        $this->assertSame(['Generation job queued for 2 entities.'], $this->messagesOfType('success'));
    }

    public function testDatabaseFailureShowsGenericError(): void
    {
        $connection = $this->connectionStub();
        $connection->method('insert')->willThrowException(new \RuntimeException('SQLSTATE secret'));

        $this->controller($this->request(['ids' => [1]]), $connection, $this->createStub(PublisherInterface::class))->execute();

        $errors = $this->messagesOfType('error');
        $this->assertCount(1, $errors);
        $this->assertStringNotContainsString('SQLSTATE', $errors[0]);
        $this->assertSame(['*/*/', []], $this->redirect);
    }

    private function controller(
        Http $request,
        AdapterInterface $connection,
        PublisherInterface $publisher,
        bool $validKey = true
    ): GenerateBatch {
        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturn('2026-01-01 00:00:00');

        return new GenerateBatch(
            $this->context($request),
            $this->resourceStub($connection),
            $dateTime,
            $publisher,
            $this->formKey($validKey)
        );
    }
}
