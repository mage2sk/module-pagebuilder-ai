<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Ui\Component\Form\DataProvider;

use Magento\Framework\DataObject;
use Panth\PageBuilderAi\Model\ResourceModel\AiPrompt\Collection;
use Panth\PageBuilderAi\Model\ResourceModel\AiPrompt\CollectionFactory;
use Panth\PageBuilderAi\Ui\Component\Form\DataProvider\AiPromptFormDataProvider;
use PHPUnit\Framework\TestCase;

class AiPromptFormDataProviderTest extends TestCase
{
    public function testItemsAreKeyedById(): void
    {
        $collection = $this->createStub(Collection::class);
        $collection->method('getItems')->willReturn([
            new DataObject(['id' => 2, 'name' => 'A']),
            new DataObject(['id' => 7, 'name' => 'B']),
        ]);

        $data = $this->provider($collection)->getData();

        $this->assertSame([2, 7], array_keys($data));
        $this->assertSame('B', $data[7]['name']);
    }

    public function testDefaultsAreProvidedForNewPrompt(): void
    {
        $collection = $this->createStub(Collection::class);
        $collection->method('getItems')->willReturn([]);

        $this->assertSame(
            ['' => ['entity_type' => 'product', 'is_active' => '1', 'is_default' => '0', 'sort_order' => '0']],
            $this->provider($collection)->getData()
        );
    }

    private function provider(Collection $collection): AiPromptFormDataProvider
    {
        $factory = $this->createStub(CollectionFactory::class);
        $factory->method('create')->willReturn($collection);

        return new AiPromptFormDataProvider('prompt_form_data_source', 'prompt_id', 'id', $factory);
    }
}
