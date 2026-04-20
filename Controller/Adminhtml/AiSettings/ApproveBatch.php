<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\AiSettings;

use Panth\PageBuilderAi\Controller\Adminhtml\AbstractAction;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Panth\PageBuilderAi\Model\GenerationJob;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

/**
 * Approves a batch of draft generation jobs, copying draft_title/description
 * onto the underlying entity's meta fields.
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
        private readonly FormKeyValidator $formKeyValidator
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->getRequest()->isPost() || !$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page.'));
            return $resultRedirect->setPath('*/*/');
        }

        $jobIds = (array)$this->getRequest()->getParam('job_ids', []);
        $jobIds = array_filter(array_map('intval', $jobIds));
        if (!$jobIds) {
            $this->messageManager->addErrorMessage(__('No jobs selected.'));
            return $resultRedirect->setPath('*/*/');
        }

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('panth_seo_generation_job');
        $approved = 0;
        foreach ($jobIds as $jobId) {
            $row = $connection->fetchRow(
                $connection->select()->from($table)->where('job_id = ?', $jobId)->limit(1)
            );
            if (!$row || ($row['status'] ?? '') !== GenerationJob::STATUS_DRAFT) {
                continue;
            }
            try {
                $options = json_decode((string)($row['options'] ?? '{}'), true) ?: [];
                $this->applyToEntity(
                    (string)$row['entity_type'],
                    (int)($options['entity_id'] ?? 0),
                    (int)$row['store_id'],
                    (string)($options['draft_title'] ?? ''),
                    (string)($options['draft_description'] ?? '')
                );
                $connection->update(
                    $table,
                    ['status' => GenerationJob::STATUS_APPROVED, 'updated_at' => $this->dateTime->gmtDate()],
                    ['job_id = ?' => $jobId]
                );
                $approved++;
            } catch (\Throwable $e) {
                $connection->update(
                    $table,
                    ['error_message' => $e->getMessage(), 'updated_at' => $this->dateTime->gmtDate()],
                    ['job_id = ?' => $jobId]
                );
            }
        }

        $this->messageManager->addSuccessMessage(__('%1 job(s) approved.', $approved));
        return $resultRedirect->setPath('*/*/');
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
