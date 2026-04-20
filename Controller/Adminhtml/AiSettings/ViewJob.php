<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\AiSettings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Result\PageFactory;
use Panth\PageBuilderAi\Controller\Adminhtml\AbstractAction;

/**
 * Detail view for a single AI generation job — shows the full job row plus
 * the entities (products / categories / CMS pages) the job targeted, with
 * names resolved for readability.
 */
class ViewJob extends AbstractAction
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_jobs';

    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory,
        private readonly ResourceConnection $resource
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $jobId = (int) $this->getRequest()->getParam('job_id');
        if ($jobId <= 0) {
            $this->messageManager->addErrorMessage(__('Invalid job id.'));
            return $this->resultRedirectFactory->create()->setPath('panth_pagebuilderai/aiSettings/jobs');
        }

        $conn = $this->resource->getConnection();
        $row = $conn->fetchRow(
            $conn->select()
                ->from($this->resource->getTableName('panth_seo_generation_job'))
                ->where('job_id = ?', $jobId)
        );
        if (!$row) {
            $this->messageManager->addErrorMessage(__('Generation job not found.'));
            return $this->resultRedirectFactory->create()->setPath('panth_pagebuilderai/aiSettings/jobs');
        }

        $entityType = (string) ($row['entity_type'] ?? '');
        $options    = json_decode((string) ($row['options'] ?? '{}'), true) ?: [];
        $entityIds  = array_values(array_filter(array_map('intval', (array) ($options['entity_ids'] ?? []))));
        $results    = (array) ($options['results'] ?? []);

        $entities = $this->loadEntityNames($entityType, $entityIds);

        $page = $this->pageFactory->create();
        $page->setActiveMenu('Panth_PageBuilderAi::ai_jobs');
        $page->getConfig()->getTitle()->prepend(__('AI Job #%1', $jobId));

        $block = $page->getLayout()->getBlock('panth_pagebuilderai.job.view');
        if ($block) {
            $block->setData('job_row', $row);
            $block->setData('entities', $entities);
            $block->setData('results', $results);
        }

        return $page;
    }

    /**
     * @param int[] $ids
     * @return array<int, array{id:int,name:string,edit_url:string}>
     */
    private function loadEntityNames(string $entityType, array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $conn = $this->resource->getConnection();
        $out  = [];

        try {
            switch ($entityType) {
                case 'product':
                    $rows = $conn->fetchAll(
                        $conn->select()
                            ->from(
                                ['p' => $this->resource->getTableName('catalog_product_entity')],
                                ['entity_id', 'sku']
                            )
                            ->joinLeft(
                                ['v' => $this->resource->getTableName('catalog_product_entity_varchar')],
                                'v.entity_id = p.entity_id AND v.attribute_id = (SELECT attribute_id FROM '
                                . $this->resource->getTableName('eav_attribute')
                                . ' WHERE attribute_code = "name" AND entity_type_id = (SELECT entity_type_id FROM '
                                . $this->resource->getTableName('eav_entity_type') . ' WHERE entity_type_code = "catalog_product"))',
                                ['name' => 'value']
                            )
                            ->where('p.entity_id IN (?)', $ids)
                    );
                    foreach ($rows as $r) {
                        $id = (int) $r['entity_id'];
                        $out[] = [
                            'id'       => $id,
                            'name'     => ($r['name'] ?: $r['sku']) ?: ('#' . $id),
                            'edit_url' => $this->buildEntityUrl('catalog/product/edit', ['id' => $id]),
                        ];
                    }
                    break;

                case 'category':
                    $rows = $conn->fetchAll(
                        $conn->select()
                            ->from(['c' => $this->resource->getTableName('catalog_category_entity')], ['entity_id'])
                            ->joinLeft(
                                ['v' => $this->resource->getTableName('catalog_category_entity_varchar')],
                                'v.entity_id = c.entity_id AND v.attribute_id = (SELECT attribute_id FROM '
                                . $this->resource->getTableName('eav_attribute')
                                . ' WHERE attribute_code = "name" AND entity_type_id = (SELECT entity_type_id FROM '
                                . $this->resource->getTableName('eav_entity_type') . ' WHERE entity_type_code = "catalog_category"))',
                                ['name' => 'value']
                            )
                            ->where('c.entity_id IN (?)', $ids)
                    );
                    foreach ($rows as $r) {
                        $id = (int) $r['entity_id'];
                        $out[] = [
                            'id'       => $id,
                            'name'     => $r['name'] ?: ('#' . $id),
                            'edit_url' => $this->buildEntityUrl('catalog/category/edit', ['id' => $id]),
                        ];
                    }
                    break;

                case 'cms_page':
                    $rows = $conn->fetchAll(
                        $conn->select()
                            ->from($this->resource->getTableName('cms_page'), ['page_id', 'title', 'identifier'])
                            ->where('page_id IN (?)', $ids)
                    );
                    foreach ($rows as $r) {
                        $id = (int) $r['page_id'];
                        $out[] = [
                            'id'       => $id,
                            'name'     => ($r['title'] ?: $r['identifier']) ?: ('#' . $id),
                            'edit_url' => $this->buildEntityUrl('cms/page/edit', ['page_id' => $id]),
                        ];
                    }
                    break;
            }
        } catch (\Throwable) {
            // Degrade gracefully — still show ids even if name lookup failed.
        }

        // Preserve original order and fill in any ids that didn't come back from the query.
        $byId = [];
        foreach ($out as $o) {
            $byId[$o['id']] = $o;
        }
        $final = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $final[] = $byId[$id];
            } else {
                $final[] = [
                    'id'       => $id,
                    'name'     => '#' . $id,
                    'edit_url' => '',
                ];
            }
        }
        return $final;
    }

    private function buildEntityUrl(string $path, array $params = []): string
    {
        return $this->_url->getUrl($path, $params);
    }
}
