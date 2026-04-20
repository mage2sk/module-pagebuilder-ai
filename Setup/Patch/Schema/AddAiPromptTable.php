<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Creates the panth_seo_ai_prompt table and adds prompt_id column
 * to panth_seo_generation_job via direct SQL.
 *
 * Kept as a setup-patch (in addition to db_schema.xml) so installations that
 * originally had the table created by AdvancedSEO's patch will register this
 * patch as already applied, and fresh installations will still create the
 * schema via db_schema.xml — this is a no-op safety net.
 */
class AddAiPromptTable implements SchemaPatchInterface
{
    public const TABLE = 'panth_seo_ai_prompt';

    public function __construct(
        private readonly SchemaSetupInterface $schemaSetup
    ) {
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $this->schemaSetup->startSetup();
        $conn = $this->schemaSetup->getConnection();

        $table = $this->schemaSetup->getTable(self::TABLE);
        if (!$conn->isTableExists($table)) {
            $definition = $conn->newTable($table)
                ->setComment('AI Prompt Templates for Meta Generation')
                ->addColumn('prompt_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                ], 'Prompt ID')
                ->addColumn('name', Table::TYPE_TEXT, 255, [
                    'nullable' => false,
                ], 'Prompt name')
                ->addColumn('entity_type', Table::TYPE_TEXT, 32, [
                    'nullable' => false,
                    'default'  => 'product',
                ], 'Entity type: product|category|cms_page|all')
                ->addColumn('prompt_template', Table::TYPE_TEXT, Table::MAX_TEXT_SIZE, [
                    'nullable' => false,
                ], 'Prompt template text with placeholders')
                ->addColumn('is_default', Table::TYPE_SMALLINT, null, [
                    'nullable' => false,
                    'default'  => 0,
                ], 'Default prompt for entity type')
                ->addColumn('is_active', Table::TYPE_SMALLINT, null, [
                    'nullable' => false,
                    'default'  => 1,
                ], 'Active')
                ->addColumn('sort_order', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => 0,
                ], 'Sort order')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT,
                ], 'Created at')
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT_UPDATE,
                ], 'Updated at')
                ->addIndex(
                    $this->schemaSetup->getIdxName(self::TABLE, ['entity_type', 'is_active']),
                    ['entity_type', 'is_active']
                )
                ->addIndex(
                    $this->schemaSetup->getIdxName(self::TABLE, ['is_default']),
                    ['is_default']
                );
            $conn->createTable($definition);
        }

        $jobTable = $this->schemaSetup->getTable('panth_seo_generation_job');
        if ($conn->isTableExists($jobTable) && !$conn->tableColumnExists($jobTable, 'prompt_id')) {
            $conn->addColumn($jobTable, 'prompt_id', [
                'type'     => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'comment'  => 'AI prompt used for generation',
            ]);
        }

        $this->schemaSetup->endSetup();
        return $this;
    }
}
