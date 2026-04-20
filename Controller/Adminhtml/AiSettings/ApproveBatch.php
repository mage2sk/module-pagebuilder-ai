<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\AiSettings;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Ui\Component\MassAction\Filter;
use Panth\PageBuilderAi\Controller\Adminhtml\AbstractAction;
use Panth\PageBuilderAi\Model\GenerationJob;
use Panth\PageBuilderAi\Model\ResourceModel\GenerationJob\Grid\CollectionFactory as JobCollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Approves a batch of draft generation jobs, copying draft_title/description
 * onto the underlying entity's meta fields.
 *
 * Accepts ids from three places, in priority order:
 *   1. Magento UI mass-action via `Filter::getCollection()` (selected, excluded, filters, namespace).
 *   2. Flat `selected[]` POST — legacy / non-UI grid callers.
 *   3. Flat `job_ids[]` POST — direct API / CLI callers.
 */
class ApproveBatch extends AbstractAction implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_jobs';

    public function __construct(
        Context $context,
        private readonly ResourceConnection $resource,
        private readonly DateTime $dateTime,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly PageRepositoryInterface $pageRepository,
        private readonly FormKeyValidator $formKeyValidator,
        private readonly Filter $filter,
        private readonly JobCollectionFactory $jobCollectionFactory,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->getRequest()->isPost() || !$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page.'));
            return $resultRedirect->setPath('panth_pagebuilderai/aiSettings/jobs');
        }

        $jobIds = $this->resolveJobIds();

        if (!$jobIds) {
            $this->messageManager->addErrorMessage(__('No jobs selected.'));
            return $resultRedirect->setPath('panth_pagebuilderai/aiSettings/jobs');
        }

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('panth_seo_generation_job');
        $approved = 0;
        $skippedNotDraft = 0;

        foreach ($jobIds as $jobId) {
            $row = $connection->fetchRow(
                $connection->select()->from($table)->where('job_id = ?', $jobId)->limit(1)
            );
            if (!$row) {
                continue;
            }
            if (($row['status'] ?? '') !== GenerationJob::STATUS_DRAFT) {
                $skippedNotDraft++;
                continue;
            }
            try {
                $options = json_decode((string)($row['options'] ?? '{}'), true) ?: [];
                $results = (array) ($options['results'] ?? []);
                $storeId = (int) $row['store_id'];
                $type    = (string) $row['entity_type'];
                $applied = 0;

                foreach ($results as $entityId => $draft) {
                    if (!is_array($draft)) {
                        continue;
                    }
                    $title = (string) ($draft['draft_title'] ?? '');
                    $desc  = (string) ($draft['draft_description'] ?? '');
                    if ($title === '' && $desc === '') {
                        continue;
                    }
                    $this->applyToEntity($type, (int) $entityId, $storeId, $title, $desc);
                    $applied++;
                }

                if ($applied === 0 && !empty($options['entity_id'])) {
                    $this->applyToEntity(
                        $type,
                        (int) $options['entity_id'],
                        $storeId,
                        (string) ($options['draft_title'] ?? ''),
                        (string) ($options['draft_description'] ?? '')
                    );
                    $applied++;
                }

                if ($applied === 0) {
                    throw new \RuntimeException('No draft results to apply.');
                }

                $connection->update(
                    $table,
                    ['status' => GenerationJob::STATUS_APPROVED, 'updated_at' => $this->dateTime->gmtDate()],
                    ['job_id = ?' => $jobId]
                );
                $approved++;
            } catch (\Throwable $e) {
                $this->logger->warning('[Panth PageBuilderAi] approve failed for job ' . $jobId . ': ' . $e->getMessage());
                $connection->update(
                    $table,
                    ['error_message' => $e->getMessage(), 'updated_at' => $this->dateTime->gmtDate()],
                    ['job_id = ?' => $jobId]
                );
            }
        }

        if ($approved > 0) {
            $this->messageManager->addSuccessMessage(__('%1 job(s) approved.', $approved));
        }
        if ($skippedNotDraft > 0) {
            $this->messageManager->addNoticeMessage(
                __('%1 job(s) were skipped because they are not in "draft" status yet — only jobs whose results are ready for review can be approved.', $skippedNotDraft)
            );
        }
        if ($approved === 0 && $skippedNotDraft === 0) {
            $this->messageManager->addErrorMessage(__('No matching jobs found for approval.'));
        }
        return $resultRedirect->setPath('panth_pagebuilderai/aiSettings/jobs');
    }

    /**
     * @return int[]
     */
    private function resolveJobIds(): array
    {
        // 1) Magento UI mass-action — uses Filter to merge selected/excluded/filters into a collection.
        try {
            $collection = $this->filter->getCollection($this->jobCollectionFactory->create());
            $ids = array_map('intval', (array) $collection->getAllIds());
            $ids = array_filter($ids);
            if ($ids) {
                return array_values($ids);
            }
        } catch (\Throwable) {
            // Filter throws if the request doesn't carry UI mass-action params — fall through.
        }

        // 2) Flat `selected[]` fallback.
        $selected = (array) $this->getRequest()->getParam('selected', []);
        $ids = array_filter(array_map('intval', $selected));
        if ($ids) {
            return array_values($ids);
        }

        // 3) Flat `job_ids[]` fallback (direct API callers, CLI).
        $jobIds = (array) $this->getRequest()->getParam('job_ids', []);
        $ids = array_filter(array_map('intval', $jobIds));
        return array_values($ids);
    }

    private function applyToEntity(string $type, int $id, int $storeId, string $title, string $description): void
    {
        switch ($type) {
            case 'product':
                $product = $this->productRepository->getById($id, true, $storeId);
                if ($title !== '') {
                    $product->setMetaTitle($title);
                }
                if ($description !== '') {
                    $product->setMetaDescription($description);
                }
                $product->setStoreId($storeId);
                $this->productRepository->save($product);
                break;
            case 'category':
                $category = $this->categoryRepository->get($id, $storeId);
                if ($title !== '') {
                    $category->setMetaTitle($title);
                }
                if ($description !== '') {
                    $category->setMetaDescription($description);
                }
                $this->categoryRepository->save($category);
                break;
            case 'cms_page':
                $page = $this->pageRepository->getById($id);
                if ($title !== '') {
                    $page->setMetaTitle($title);
                }
                if ($description !== '') {
                    $page->setMetaDescription($description);
                }
                $this->pageRepository->save($page);
                break;
        }
    }
}
