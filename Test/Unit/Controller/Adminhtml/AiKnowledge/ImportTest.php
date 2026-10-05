<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\AiKnowledge;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Controller\Adminhtml\AiKnowledge\Import;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    use BackendActionStubs;
    use DbStubs;

    public function testAllBundledEntriesAreInsertedWhenTableIsEmpty(): void
    {
        $inserted = [];
        $connection = $this->connectionStub();
        $connection->method('fetchOne')->willReturn(false);
        $connection->method('insert')->willReturnCallback(static function ($table, array $row) use (&$inserted) {
            $inserted[] = $row;
            return 1;
        });

        $this->controller($this->resourceStub($connection))->execute();

        $expected = $this->bundledEntryCount();
        $this->assertGreaterThan(0, $expected);
        $this->assertCount($expected, $inserted);
        foreach ($inserted as $row) {
            $this->assertNotSame('', $row['title']);
            $this->assertLessThanOrEqual(255, strlen($row['title']));
            $this->assertIsString($row['tags']);
            $this->assertLessThanOrEqual(512, strlen($row['tags']));
            $this->assertSame('2026-01-01 00:00:00', $row['created_at']);
        }
        $this->assertSame(
            ['Training data imported: ' . $expected . ' new entries added, 0 already existed. Total: 0 entries.'],
            $this->messagesOfType('success')
        );
        $this->assertSame(['*/*/index', []], $this->redirect);
    }

    public function testExistingEntriesAreSkipped(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchOne')->willReturn('1');
        $connection->method('insert')->willThrowException(new \LogicException('insert must not be called'));

        $this->controller($this->resourceStub($connection))->execute();

        $this->assertSame(
            ['Training data imported: 0 new entries added, ' . $this->bundledEntryCount() . ' already existed. Total: 1 entries.'],
            $this->messagesOfType('success')
        );
    }

    public function testFailureIsReported(): void
    {
        $resource = $this->createStub(ResourceConnection::class);
        $resource->method('getConnection')->willThrowException(new \RuntimeException('boom'));

        $this->controller($resource)->execute();

        $this->assertSame(['Import failed: boom'], $this->messagesOfType('error'));
        $this->assertSame(['*/*/index', []], $this->redirect);
    }

    private function bundledEntryCount(): int
    {
        $count = 0;
        foreach (glob(dirname(__DIR__, 5) . '/Setup/Data/*.php') as $file) {
            foreach ((array)include $file as $entry) {
                if ((string)($entry['title'] ?? '') !== '') {
                    $count++;
                }
            }
        }
        return $count;
    }

    private function controller(ResourceConnection $resource): Import
    {
        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturn('2026-01-01 00:00:00');

        return new Import($this->context($this->request()), $resource, $dateTime);
    }
}
