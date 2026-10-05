<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Setup\Patch\Data;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Panth\PageBuilderAi\Setup\Patch\Data\InstallDefaultAiPrompts;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class InstallDefaultAiPromptsTest extends TestCase
{
    use DbStubs;

    public function testMissingTableSkipsInstall(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('isTableExists')->willReturn(false);
        $connection->expects($this->never())->method('insert');

        $patch = new InstallDefaultAiPrompts($this->dataSetup($connection, 1));

        $this->assertSame($patch, $patch->apply());
    }

    public function testOnlyMissingPromptsAreInserted(): void
    {
        $inserted = [];
        $connection = $this->connectionStub(['panth_seo_ai_prompt']);
        $connection->method('fetchOne')->willReturnOnConsecutiveCalls('1', false, false, false, false, false, false, false, false, false, false);
        $connection->method('insert')->willReturnCallback(static function ($table, array $row) use (&$inserted) {
            $inserted[] = $row;
            return 1;
        });

        (new InstallDefaultAiPrompts($this->dataSetup($connection, 1)))->apply();

        $this->assertNotEmpty($inserted);
        $names = array_column($inserted, 'name');
        $this->assertNotContains('Default Product Meta', $names);
        $this->assertSame($names, array_unique($names));
        $defaults = [];
        foreach ($inserted as $row) {
            $this->assertContains($row['entity_type'], ['product', 'category', 'cms_page', 'all', 'pagebuilder']);
            $this->assertSame(1, $row['is_active']);
            $this->assertNotSame('', trim($row['prompt_template']));
            if ($row['is_default']) {
                $defaults[] = $row['entity_type'];
            }
        }
        $this->assertSame($defaults, array_unique($defaults));
    }

    public function testPatchMetadata(): void
    {
        $patch = new InstallDefaultAiPrompts($this->createStub(ModuleDataSetupInterface::class));

        $this->assertSame([], InstallDefaultAiPrompts::getDependencies());
        $this->assertSame([], $patch->getAliases());
    }

    private function dataSetup(AdapterInterface $connection, int $expectedCycles): ModuleDataSetupInterface
    {
        $setup = $this->createMock(ModuleDataSetupInterface::class);
        $setup->expects($this->exactly($expectedCycles))->method('startSetup');
        $setup->expects($this->exactly($expectedCycles))->method('endSetup');
        $setup->method('getConnection')->willReturn($connection);
        $setup->method('getTable')->willReturnArgument(0);
        return $setup;
    }
}
