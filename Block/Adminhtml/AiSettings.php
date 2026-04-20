<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Form\FormKey;
use Magento\Store\Model\System\Store as StoreSystem;

class AiSettings extends Template
{
    protected $_template = 'Panth_PageBuilderAi::ai_settings.phtml';

    public function __construct(
        Context $context,
        private readonly ResourceConnection $resource,
        private readonly StoreSystem $storeSystem,
        private readonly FormKey $formKeyService,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getConfigUrl(): string
    {
        return $this->getUrl('adminhtml/system_config/edit', ['section' => 'panth_pagebuilderai']);
    }

    public function getJobsUrl(): string
    {
        return $this->getUrl('panth_pagebuilderai/aisettings/jobs');
    }

    public function getGenerateBatchUrl(): string
    {
        return $this->getUrl('panth_pagebuilderai/aisettings/generateBatch');
    }

    public function getApproveBatchUrl(): string
    {
        return $this->getUrl('panth_pagebuilderai/aisettings/approveBatch');
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public function getStoreOptions(): array
    {
        $options = [['value' => 0, 'label' => (string) __('All Store Views')]];
        foreach ($this->storeSystem->getStoreValuesForForm(false, true) as $row) {
            if (!isset($row['value']) || $row['value'] === '') {
                continue;
            }
            $options[] = ['value' => (int) $row['value'], 'label' => (string) ($row['label'] ?? $row['value'])];
        }
        return $options;
    }

    /**
     * @return array<string, int>
     */
    public function getJobCounts(): array
    {
        try {
            $conn = $this->resource->getConnection();
            $table = $this->resource->getTableName('panth_seo_generation_job');
            $select = $conn->select()->from($table, ['status', 'count' => 'COUNT(*)'])->group('status');
            $rows = $conn->fetchPairs($select);
        } catch (\Throwable) {
            $rows = [];
        }
        return [
            'pending' => (int) ($rows['pending'] ?? 0),
            'processing' => (int) ($rows['processing'] ?? 0),
            'draft' => (int) ($rows['draft'] ?? 0),
            'approved' => (int) ($rows['approved'] ?? 0),
            'failed' => (int) ($rows['failed'] ?? 0),
        ];
    }

    public function getEntityCount(string $entityType): int
    {
        $conn = $this->resource->getConnection();
        try {
            switch ($entityType) {
                case 'product':
                    return (int) $conn->fetchOne(
                        $conn->select()->from($this->resource->getTableName('catalog_product_entity'), 'COUNT(*)')
                    );
                case 'category':
                    return (int) $conn->fetchOne(
                        $conn->select()
                            ->from($this->resource->getTableName('catalog_category_entity'), 'COUNT(*)')
                            ->where('level > ?', 1)
                    );
                case 'cms_page':
                    return (int) $conn->fetchOne(
                        $conn->select()
                            ->from($this->resource->getTableName('cms_page'), 'COUNT(*)')
                            ->where('is_active = ?', 1)
                    );
            }
        } catch (\Throwable) {
            return 0;
        }
        return 0;
    }

    public function getFormKey(): string
    {
        return $this->formKeyService->getFormKey();
    }
}
