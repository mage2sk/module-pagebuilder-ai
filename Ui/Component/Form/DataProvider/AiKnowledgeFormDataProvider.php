<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Ui\Component\Form\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Panth\PageBuilderAi\Model\ResourceModel\AiKnowledge\CollectionFactory;

class AiKnowledgeFormDataProvider extends AbstractDataProvider
{
    /**
     * @var array<int,array<string,mixed>>|null
     */
    private ?array $loadedData = null;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        $items = $this->collection->getItems();

        foreach ($items as $item) {
            $this->loadedData[$item->getId()] = $item->getData();
        }

        if (empty($this->loadedData)) {
            $this->loadedData[''] = [
                'category'    => 'seo',
                'subcategory' => '',
                'is_active'   => '1',
                'sort_order'  => '0',
            ];
        }

        return $this->loadedData;
    }
}
