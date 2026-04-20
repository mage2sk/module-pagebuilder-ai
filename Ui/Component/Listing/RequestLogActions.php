<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Ui\Component\Listing;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Per-row action column for the request-log grid: View and Delete.
 */
class RequestLogActions extends Column
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
            if (empty($item['log_id'])) {
                continue;
            }
            $item[$this->getData('name')] = [
                'view' => [
                    'href'  => $this->urlBuilder->getUrl(
                        'panth_pagebuilderai/requestLog/view',
                        ['log_id' => (int) $item['log_id']]
                    ),
                    'label' => __('View'),
                ],
                'delete' => [
                    'href' => $this->urlBuilder->getUrl(
                        'panth_pagebuilderai/requestLog/delete',
                        ['log_id' => (int) $item['log_id']]
                    ),
                    'label'   => __('Delete'),
                    'confirm' => [
                        'title'   => __('Delete log #%1', $item['log_id']),
                        'message' => __('Permanently delete this AI request log entry?'),
                    ],
                ],
            ];
        }
        return $dataSource;
    }
}
