<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\ViewModel;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\ResourceModel\AiPrompt\CollectionFactory as AiPromptCollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * ViewModel for pagebuilder-ai-init.phtml.
 *
 * After the AdvancedSEO AI merge, PageBuilderAi is the sole owner of the AI
 * backend. This class only deals with its own endpoint + its own prompt
 * collection.
 */
class AiInit implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly UrlInterface $backendUrl,
        private readonly AiPromptCollectionFactory $promptCollectionFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Returns true when the module is enabled.
     * API key validation happens at generation time, not at render time.
     */
    public function isAvailable(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * AI generation endpoint URL.
     */
    public function getGenerateUrl(): string
    {
        return $this->backendUrl->getUrl('panth_pagebuilderai/generate/index');
    }

    /**
     * Kept for backward compatibility: 'own' or 'none'.
     */
    public function getActiveBackend(): string
    {
        return $this->config->getActiveBackend();
    }

    /**
     * Loads saved prompt templates for cms_page / pagebuilder / all entity types.
     *
     * Uses the Repository-style Collection instead of raw SQL so the read path
     * is clean and refactor-safe.
     *
     * @return array<int, array{id:int, name:string, template:string}>
     */
    public function getSavedPrompts(): array
    {
        try {
            $collection = $this->promptCollectionFactory->create();
            $collection->addFieldToFilter('is_active', 1);
            $collection->addFieldToFilter('entity_type', ['in' => ['cms_page', 'all', 'pagebuilder']]);
            $collection->setOrder('is_default', 'DESC');
            $collection->setOrder('sort_order', 'ASC');

            $prompts = [];
            foreach ($collection as $item) {
                $prompts[] = [
                    'id'       => (int) $item->getData('prompt_id'),
                    'name'     => (string) $item->getData('name'),
                    'template' => (string) $item->getData('prompt_template'),
                ];
            }
            return $prompts;
        } catch (\Throwable $e) {
            $this->logger->warning('Panth PageBuilderAi: failed to load saved prompts: ' . $e->getMessage());
            return [];
        }
    }
}
