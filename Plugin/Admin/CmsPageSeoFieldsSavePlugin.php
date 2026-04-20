<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Plugin\Admin;

use Magento\Cms\Controller\Adminhtml\Page\Save as CmsPageSaveController;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Panth\PageBuilderAi\Helper\Config as SeoConfig;
use Psr\Log\LoggerInterface;

/**
 * After the CMS page save controller completes, persist meta_robots and
 * hreflang_identifier values to the panth_seo_override table.
 *
 * CMS pages do not use EAV, so these values must be stored manually.
 */
class CmsPageSeoFieldsSavePlugin
{
    private const TABLE       = 'panth_seo_override';
    private const ENTITY_TYPE = 'cms_page';

    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly RequestInterface $request,
        private readonly LoggerInterface $logger,
        private readonly SeoConfig $seoConfig
    ) {
    }

    /**
     * @param CmsPageSaveController $subject
     * @param mixed                 $result
     * @return mixed
     */
    public function afterExecute(CmsPageSaveController $subject, mixed $result): mixed
    {
        if (!$this->seoConfig->isEnabled()) {
            return $result;
        }

        try {
            $postData            = (array) $this->request->getPostValue();
            $metaRobots          = trim((string) ($postData['meta_robots'] ?? ''));
            $hreflangIdentifier  = trim((string) ($postData['hreflang_identifier'] ?? ''));
            $pageId              = (int) ($postData['page_id'] ?? $this->request->getParam('page_id', 0));
            $storeId             = $this->resolveStoreId($postData);

            if ($pageId <= 0) {
                return $result;
            }

            $this->persistOverride($pageId, $storeId, $metaRobots, $hreflangIdentifier);
        } catch (\Throwable $e) {
            $this->logger->warning('[Panth_PageBuilderAi] CMS page SEO fields save failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    /**
     * Resolve the store ID from post data.
     *
     * @param array<string, mixed> $postData
     */
    private function resolveStoreId(array $postData): int
    {
        if (isset($postData['store_id'])) {
            $stores = is_array($postData['store_id']) ? $postData['store_id'] : [$postData['store_id']];
            return (int) reset($stores);
        }

        return (int) $this->request->getParam('store', 0);
    }

    /**
     * Upsert or delete the override row for this CMS page.
     */
    private function persistOverride(
        int $pageId,
        int $storeId,
        string $metaRobots,
        string $hreflangIdentifier
    ): void {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName(self::TABLE);
        $existingId = $this->findExistingId($pageId, $storeId);

        // If both fields are empty, remove the override row entirely.
        if ($metaRobots === '' && $hreflangIdentifier === '') {
            if ($existingId !== null) {
                // Only delete if other columns are also empty to avoid removing
                // overrides set by other subsystems (e.g. meta_title, canonical).
                $row = $this->loadFullRow($existingId);
                if ($row !== null && $this->isRowEmptyExceptSeoFields($row)) {
                    $connection->delete($table, ['override_id = ?' => $existingId]);
                } else {
                    // Clear just our fields.
                    $connection->update($table, [
                        'robots'              => null,
                        'hreflang_identifier' => null,
                    ], ['override_id = ?' => $existingId]);
                }
            }
            return;
        }

        $data = [
            'robots'              => $metaRobots !== '' ? $metaRobots : null,
            'hreflang_identifier' => $hreflangIdentifier !== '' ? $hreflangIdentifier : null,
        ];

        if ($existingId !== null) {
            $connection->update($table, $data, ['override_id = ?' => $existingId]);
        } else {
            $data['entity_type'] = self::ENTITY_TYPE;
            $data['entity_id']   = $pageId;
            $data['store_id']    = $storeId;
            $connection->insert($table, $data);
        }
    }

    /**
     * Find an existing override_id for the entity + store combination.
     */
    private function findExistingId(int $entityId, int $storeId): ?int
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName(self::TABLE);

        $select = $connection->select()
            ->from($table, ['override_id'])
            ->where('entity_type = ?', self::ENTITY_TYPE)
            ->where('entity_id = ?', $entityId)
            ->where('store_id = ?', $storeId)
            ->limit(1);

        $id = $connection->fetchOne($select);

        return $id !== false ? (int) $id : null;
    }

    /**
     * Load the full override row to check whether it can be safely deleted.
     *
     * @return array<string, mixed>|null
     */
    private function loadFullRow(int $overrideId): ?array
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName(self::TABLE);

        $row = $connection->fetchRow(
            $connection->select()->from($table)->where('override_id = ?', $overrideId)
        );

        return $row !== false ? $row : null;
    }

    /**
     * Check whether the row has no meaningful data beyond our SEO fields,
     * entity_type, entity_id, and store_id.
     *
     * @param array<string, mixed> $row
     */
    private function isRowEmptyExceptSeoFields(array $row): bool
    {
        $ignoredKeys = [
            'override_id', 'entity_type', 'entity_id', 'store_id',
            'robots', 'hreflang_identifier',
            'ai_generated', 'ai_approved',
            'created_at', 'updated_at',
        ];

        foreach ($row as $key => $value) {
            if (in_array($key, $ignoredKeys, true)) {
                continue;
            }
            if ($value !== null && $value !== '' && $value !== '0') {
                return false;
            }
        }

        return true;
    }
}
