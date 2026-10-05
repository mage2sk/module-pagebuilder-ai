<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Setup\Patch\Data;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Setup\Patch\Data\InstallAiKnowledgeBase;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class InstallAiKnowledgeBaseTest extends TestCase
{
    use DbStubs;

    public function testMissingTableSkipsInstall(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('isTableExists')->willReturn(false);
        $connection->expects($this->never())->method('insert');

        $patch = $this->patch($connection);

        $this->assertSame($patch, $patch->apply());
    }

    public function testBundledDataIsInstalled(): void
    {
        $select = $this->createStub(\Magento\Framework\DB\Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('limit')->willReturnSelf();
        $rows = [];
        $connection = $this->createStub(AdapterInterface::class);
        $connection->method('isTableExists')->willReturn(true);
        $connection->method('select')->willReturn($select);
        $connection->method('fetchOne')->willReturn(false);
        $connection->method('insert')->willReturnCallback(function ($table, $row) use (&$rows) {
            $rows[] = $row;
            return 1;
        });

        $this->patch($connection)->apply();

        $this->assertGreaterThan(400, count($rows));
        $this->assertSame('2026-01-01 00:00:00', $rows[0]['created_at']);
    }

    public function testPatchMetadata(): void
    {
        $this->assertSame([], InstallAiKnowledgeBase::getDependencies());
        $this->assertSame([], $this->patch($this->connectionStub())->getAliases());
    }

    private function patch(AdapterInterface $connection): InstallAiKnowledgeBase
    {
        $setup = $this->createMock(ModuleDataSetupInterface::class);
        $setup->expects($this->atMost(1))->method('startSetup');
        $setup->expects($this->atMost(1))->method('endSetup');
        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturn('2026-01-01 00:00:00');

        return new InstallAiKnowledgeBase($setup, $this->resourceStub($connection), $dateTime);
    }
}
