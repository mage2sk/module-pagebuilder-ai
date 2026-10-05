<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class InstallAiKnowledgeBase implements DataPatchInterface
{
    private const BATCH_FILES = [
        'panth_modules_knowledge_batch1.php',
        'panth_modules_knowledge_batch2.php',
        'panth_modules_knowledge_batch3.php',
        'panth_modules_knowledge_batch4.php',
        'pagebuilder_knowledge.php',
        'ecommerce_knowledge.php',
        'seo_technical_knowledge.php',
        'accessibility_html_knowledge.php',
        'response_format_knowledge.php',
        'conversion_copywriting_knowledge.php',
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly ResourceConnection $resource,
        private readonly DateTime $dateTime
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
        $this->moduleDataSetup->startSetup();

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('panth_seo_ai_knowledge');

        if (!$connection->isTableExists($table)) {
            $this->moduleDataSetup->endSetup();
            return $this;
        }

        $now = $this->dateTime->gmtDate();
        $dataDir = dirname(__DIR__, 2) . '/Data/';

        $allEntries = [];
        foreach (self::BATCH_FILES as $file) {
            $path = $dataDir . $file;
            if (!file_exists($path)) {
                continue;
            }
            $batchEntries = include $path;
            if (is_array($batchEntries)) {
                $allEntries = array_merge($allEntries, $batchEntries);
            }
        }

        foreach ($allEntries as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            if (is_array($entry['tags'] ?? null)) {
                $entry['tags'] = implode(',', $entry['tags']);
            }
            $title = substr((string) ($entry['title'] ?? ''), 0, 255);
            $category = substr((string) ($entry['category'] ?? 'general'), 0, 64);
            if ($title === '') {
                continue;
            }
            $row = [
                'category'    => $category,
                'subcategory' => substr((string) ($entry['subcategory'] ?? ''), 0, 128),
                'title'       => $title,
                'content'     => (string) ($entry['content'] ?? ''),
                'tags'        => substr((string) ($entry['tags'] ?? ''), 0, 512),
                'is_active'   => (int) ($entry['is_active'] ?? 1),
                'sort_order'  => (int) ($entry['sort_order'] ?? 0),
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            $exists = $connection->fetchOne(
                $connection->select()
                    ->from($table, ['knowledge_id'])
                    ->where('title = ?', $row['title'])
                    ->where('category = ?', $row['category'])
                    ->limit(1)
            );
            if ($exists) {
                continue;
            }
            try {
                $connection->insert($table, $row);
            } catch (\Throwable) {
            }
        }

        $this->moduleDataSetup->endSetup();
        return $this;
    }
}
