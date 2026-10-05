<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Block\Adminhtml;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class GenericDeleteButton implements ButtonProviderInterface
{
    public function __construct(
        private readonly UrlInterface $urlBuilder,
        private readonly RequestInterface $request
    ) {
    }

    public function getButtonData(): array
    {
        $id = (int)$this->request->getParam('id');
        if ($id === 0) {
            return [];
        }

        $deleteUrl = $this->getDeleteUrl($id);

        return [
            'label' => __('Delete'),
            'class' => 'delete',
            'on_click' => 'deleteConfirm(\'' . __('Are you sure you want to delete this item?') . '\', \'' . $deleteUrl . '\')',
            'sort_order' => 20,
        ];
    }

    private function getDeleteUrl(int $id): string
    {
        $routeName = $this->request->getRouteName();
        $controllerName = $this->request->getControllerName();

        return $this->urlBuilder->getUrl(
            $routeName . '/' . $controllerName . '/delete',
            ['id' => $id]
        );
    }
}
