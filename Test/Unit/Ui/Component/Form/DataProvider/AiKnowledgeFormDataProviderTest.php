<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Ui\Component\Form\DataProvider;

use Magento\Framework\DataObject;
use Panth\PageBuilderAi\Model\ResourceModel\AiKnowledge\Collection;
use Panth\PageBuilderAi\Model\ResourceModel\AiKnowledge\CollectionFactory;
use Panth\PageBuilderAi\Ui\Component\Form\DataProvider\AiKnowledgeFormDataProvider;
use PHPUnit\Framework\TestCase;

class AiKnowledgeFormDataProviderTest extends TestCase
{
    public function testItemsAreKeyedByIdAndCached(): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())->method('getItems')->willReturn([
            new DataObject(['id' => 4, 'title' => 'Rule', 'category' => 'seo']),
        ]);

        $provider = $this->provider($collection);
        $data = $provider->getData();

        $this->assertSame([4 => ['id' => 4, 'title' => 'Rule', 'category' => 'seo']], $data);
        $this->assertSame($data, $provider->getData());
    }

    public function testDefaultsAreProvidedForNewEntry(): void
    {
        $collection = $this->createStub(Collection::class);
        $collection->method('getItems')->willReturn([]);

        $this->assertSame(
            ['' => ['category' => 'seo', 'subcategory' => '', 'is_active' => '1', 'sort_order' => '0']],
            $this->provider($collection)->getData()
        );
    }

    private function provider(Collection $collection): AiKnowledgeFormDataProvider
    {
        $factory = $this->createStub(CollectionFactory::class);
        $factory->method('create')->willReturn($collection);

        return new AiKnowledgeFormDataProvider('knowledge_form_data_source', 'knowledge_id', 'id', $factory);
    }
}
