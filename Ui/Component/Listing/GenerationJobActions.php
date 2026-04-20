<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Ui\Component\Listing;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Per-row View link on the AI Generation Jobs grid so the admin can drill into
 * the exact entity list a job queued / is processing.
 */
class GenerationJobActions extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }
        foreach ($dataSource['data']['items'] as &$item) {
            if (empty($item['job_id'])) {
                continue;
            }
            $item[$this->getData('name')] = [
                'view' => [
                    'href'  => $this->urlBuilder->getUrl(
                        'panth_pagebuilderai/aiSettings/viewJob',
                        ['job_id' => (int) $item['job_id']]
                    ),
                    'label' => __('View Details'),
                ],
            ];
        }
        return $dataSource;
    }
}
