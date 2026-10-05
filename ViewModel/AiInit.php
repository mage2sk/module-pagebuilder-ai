<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\ViewModel;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\ResourceModel\AiPrompt\CollectionFactory as AiPromptCollectionFactory;
use Psr\Log\LoggerInterface;

class AiInit implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly UrlInterface $backendUrl,
        private readonly AiPromptCollectionFactory $promptCollectionFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function isAvailable(): bool
    {
        return $this->config->isEnabled();
    }

    public function getGenerateUrl(): string
    {
        return $this->backendUrl->getUrl('panth_pagebuilderai/generate/index');
    }

    public function getActiveBackend(): string
    {
        return $this->config->getActiveBackend();
    }

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
