<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\AiKnowledge;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Controller\Adminhtml\AbstractAction;

/**
 * Import/refresh all default AI knowledge entries from batch data files.
 * Skips entries that already exist (by title + category).
 */
class Import extends AbstractAction implements HttpGetActionInterface, HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_knowledge';

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
        Context $context,
        private readonly ResourceConnection $resource,
        private readonly DateTime $dateTime
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName('panth_seo_ai_knowledge');
            $now = $this->dateTime->gmtDate();
            $dataDir = dirname(__DIR__, 3) . '/Setup/Data/';

            $inserted = 0;
            $skipped = 0;

            foreach (self::BATCH_FILES as $file) {
                $path = $dataDir . $file;
                if (!file_exists($path)) {
                    continue;
                }

                $entries = include $path; // phpcs:ignore Magento2.Security.IncludeFile
                if (!is_array($entries)) {
                    continue;
                }

                foreach ($entries as $entry) {
                    if (is_array($entry['tags'] ?? null)) {
                        $entry['tags'] = implode(',', $entry['tags']);
                    }

                    $title = substr((string) ($entry['title'] ?? ''), 0, 255);
                    $category = substr((string) ($entry['category'] ?? 'general'), 0, 64);

                    if ($title === '') {
                        continue;
                    }

                    $exists = $connection->fetchOne(
                        $connection->select()
                            ->from($table, ['knowledge_id'])
                            ->where('title = ?', $title)
                            ->where('category = ?', $category)
                            ->limit(1)
                    );

                    if ($exists) {
                        $skipped++;
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

                    $connection->insert($table, $row);
                    $inserted++;
                }
            }

            $total = (int) $connection->fetchOne(
                $connection->select()->from($table, ['c' => 'COUNT(*)'])
            );

            $this->messageManager->addSuccessMessage(
                (string) __('Training data imported: %1 new entries added, %2 already existed. Total: %3 entries.', $inserted, $skipped, $total)
            );
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(
                (string) __('Import failed: %1', $e->getMessage())
            );
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
