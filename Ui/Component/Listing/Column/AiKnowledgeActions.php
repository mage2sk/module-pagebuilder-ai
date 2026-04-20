<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class AiKnowledgeActions extends Column
{
    public const URL_PATH_EDIT = 'panth_pagebuilderai/aiknowledge/edit';
    public const URL_PATH_DELETE = 'panth_pagebuilderai/aiknowledge/delete';

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
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        $name = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            $id = $item['knowledge_id'] ?? null;
            if ($id === null) {
                continue;
            }
            $item[$name]['edit'] = [
                'href' => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['id' => $id]),
                'label' => (string) __('Edit'),
            ];
            $item[$name]['delete'] = [
                'href' => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['id' => $id]),
                'label' => (string) __('Delete'),
                'confirm' => [
                    'title' => (string) __('Delete Knowledge Entry'),
                    'message' => (string) __('Are you sure you want to delete this knowledge entry?'),
                ],
                'post' => true,
            ];
        }
        return $dataSource;
    }
}
