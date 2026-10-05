<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Setup\Patch\Data;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Panth\PageBuilderAi\Setup\Patch\Data\InstallDefaultAiPrompts;
use Panth\PageBuilderAi\Setup\Patch\Data\UpgradeDefaultAiPromptsV2;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class UpgradeDefaultAiPromptsV2Test extends TestCase
{
    use DbStubs;

    public function testTemplatesOfInstalledPromptsAreReplaced(): void
    {
        $updates = [];
        $connection = $this->connectionStub(['panth_seo_ai_prompt']);
        $connection->method('update')->willReturnCallback(static function ($table, array $data, array $where) use (&$updates) {
            $updates[$where['name = ?']] = $data;
            return 1;
        });
        $installedNames = $this->installedPromptNames();

        (new UpgradeDefaultAiPromptsV2($this->dataSetup($connection)))->apply();

        $this->assertSame(
            ['Default Product Meta', 'Default Category Meta', 'Default CMS Meta', 'Generic All-Entity Meta', 'Default PageBuilder Content', 'FAQ Section Generator'],
            array_keys($updates)
        );
        foreach ($updates as $name => $data) {
            $this->assertContains($name, $installedNames);
            $this->assertNotSame('', trim($data['prompt_template']));
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data['updated_at']);
        }
        $this->assertStringContainsString('{{name}}', $updates['Default Product Meta']['prompt_template']);
    }

    public function testUpdateFailuresAreIgnored(): void
    {
        $connection = $this->connectionStub(['panth_seo_ai_prompt']);
        $connection->method('update')->willThrowException(new \RuntimeException('locked'));

        $patch = new UpgradeDefaultAiPromptsV2($this->dataSetup($connection));

        $this->assertSame($patch, $patch->apply());
    }

    public function testMissingTableSkipsUpgrade(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('isTableExists')->willReturn(false);
        $connection->expects($this->never())->method('update');

        (new UpgradeDefaultAiPromptsV2($this->dataSetup($connection)))->apply();
    }

    public function testDependsOnInitialInstall(): void
    {
        $this->assertSame([InstallDefaultAiPrompts::class], UpgradeDefaultAiPromptsV2::getDependencies());
        $this->assertSame([], (new UpgradeDefaultAiPromptsV2($this->createStub(ModuleDataSetupInterface::class)))->getAliases());
    }

    private function installedPromptNames(): array
    {
        $names = [];
        $connection = $this->connectionStub(['panth_seo_ai_prompt']);
        $connection->method('fetchOne')->willReturn(false);
        $connection->method('insert')->willReturnCallback(static function ($table, array $row) use (&$names) {
            $names[] = $row['name'];
            return 1;
        });
        $setup = $this->createStub(ModuleDataSetupInterface::class);
        $setup->method('getConnection')->willReturn($connection);
        $setup->method('getTable')->willReturnArgument(0);
        (new InstallDefaultAiPrompts($setup))->apply();
        return $names;
    }

    private function dataSetup(AdapterInterface $connection): ModuleDataSetupInterface
    {
        $setup = $this->createMock(ModuleDataSetupInterface::class);
        $setup->expects($this->once())->method('startSetup');
        $setup->expects($this->once())->method('endSetup');
        $setup->method('getConnection')->willReturn($connection);
        $setup->method('getTable')->willReturnArgument(0);
        return $setup;
    }
}
