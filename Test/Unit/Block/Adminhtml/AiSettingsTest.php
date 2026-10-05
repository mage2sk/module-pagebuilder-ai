<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Block\Adminhtml;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\System\Store as StoreSystem;
use Panth\PageBuilderAi\Block\Adminhtml\AiSettings;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class AiSettingsTest extends TestCase
{
    use DbStubs;

    public function testActionUrls(): void
    {
        $block = $this->block();

        $this->assertSame('adminhtml/system_config/edit|{"section":"panth_pagebuilderai"}', $block->getConfigUrl());
        $this->assertSame('panth_pagebuilderai/aisettings/jobs|[]', $block->getJobsUrl());
        $this->assertSame('panth_pagebuilderai/aisettings/generateBatch|[]', $block->getGenerateBatchUrl());
        $this->assertSame('panth_pagebuilderai/aisettings/approveBatch|[]', $block->getApproveBatchUrl());
    }

    public function testStoreOptionsStartWithAllStoreViews(): void
    {
        $storeSystem = $this->createMock(StoreSystem::class);
        $storeSystem->expects($this->once())->method('getStoreValuesForForm')->with(false, true)->willReturn([
            ['value' => '', 'label' => 'Main Website'],
            ['value' => '1', 'label' => 'Default Store View'],
            ['value' => 2],
            ['label' => 'No value'],
        ]);

        $this->assertSame([
            ['value' => 0, 'label' => 'All Store Views'],
            ['value' => 1, 'label' => 'Default Store View'],
            ['value' => 2, 'label' => '2'],
        ], $this->block(null, $storeSystem)->getStoreOptions());
    }

    public function testJobCountsDefaultMissingStatusesToZero(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchPairs')->willReturn(['pending' => '3', 'draft' => 1, 'unknown' => 9]);

        $this->assertSame(
            ['pending' => 3, 'processing' => 0, 'draft' => 1, 'approved' => 0, 'failed' => 0],
            $this->block($connection)->getJobCounts()
        );
    }

    public function testJobCountsSurviveDatabaseErrors(): void
    {
        $resource = $this->createStub(ResourceConnection::class);
        $resource->method('getConnection')->willThrowException(new \RuntimeException('down'));

        $this->assertSame(
            ['pending' => 0, 'processing' => 0, 'draft' => 0, 'approved' => 0, 'failed' => 0],
            $this->block(null, null, $resource)->getJobCounts()
        );
    }

    public function testEntityCounts(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchOne')->willReturnOnConsecutiveCalls('120', '14', '6');
        $block = $this->block($connection);

        $this->assertSame(120, $block->getEntityCount('product'));
        $this->assertSame(14, $block->getEntityCount('category'));
        $this->assertSame(6, $block->getEntityCount('cms_page'));
        $this->assertSame(0, $block->getEntityCount('faq'));
    }

    public function testEntityCountFailureReturnsZero(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchOne')->willThrowException(new \RuntimeException('missing table'));

        $this->assertSame(0, $this->block($connection)->getEntityCount('product'));
    }

    public function testFormKeyIsExposed(): void
    {
        $this->assertSame('fk123', $this->block()->getFormKey());
    }

    private function block(
        ?AdapterInterface $connection = null,
        ?StoreSystem $storeSystem = null,
        ?ResourceConnection $resource = null
    ): AiSettings {
        $resource ??= $this->resourceStub($connection ?? $this->connectionStub());
        $storeSystem ??= $this->createStub(StoreSystem::class);
        $formKey = $this->createStub(FormKey::class);
        $formKey->method('getFormKey')->willReturn('fk123');
        $url = $this->createStub(UrlInterface::class);
        $url->method('getUrl')->willReturnCallback(
            static fn ($route, $params = []) => $route . '|' . json_encode($params)
        );

        $block = (new \ReflectionClass(AiSettings::class))->newInstanceWithoutConstructor();
        (function () use ($resource, $storeSystem, $formKey, $url) {
            $this->resource = $resource;
            $this->storeSystem = $storeSystem;
            $this->formKeyService = $formKey;
            $this->_urlBuilder = $url;
        })->call($block);

        return $block;
    }
}
