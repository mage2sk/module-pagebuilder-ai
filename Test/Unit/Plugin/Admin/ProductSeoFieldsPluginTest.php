<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Plugin\Admin;

use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\Config\Source\MetaRobots;
use Panth\PageBuilderAi\Plugin\Admin\ProductSeoFieldsPlugin;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class ProductSeoFieldsPluginTest extends TestCase
{
    use DbStubs;

    public function testDisabledModuleLeavesResultsUntouched(): void
    {
        $plugin = $this->plugin(false, true);
        $data = [5 => ['product' => ['entity_id' => 5]]];

        $this->assertSame(['x' => 1], $plugin->afterGetMeta($this->subject(), ['x' => 1]));
        $this->assertSame($data, $plugin->afterGetData($this->subject(), $data));
    }

    public function testCanonicalFieldRequiresCanonicalTable(): void
    {
        $without = $this->plugin(true, false)->afterGetMeta($this->subject(), []);
        $with = $this->plugin(true, false, $this->connectionStub(['panth_seo_custom_canonical']))
            ->afterGetMeta($this->subject(), []);

        $this->assertSame(
            ['meta_robots', 'og_title', 'og_description', 'og_image', 'exclude_from_sitemap'],
            array_keys($without['search-engine-optimization']['children'])
        );
        $canonical = $with['search-engine-optimization']['children']['custom_canonical_url']['arguments']['data']['config'];
        $this->assertSame(['validate-url' => true], $canonical['validation']);
        $this->assertArrayNotHasKey('ai_generate_container', $with['search-engine-optimization']['children']);
    }

    public function testGenerateButtonUsesProductFieldNames(): void
    {
        $result = $this->plugin(true, true)->afterGetMeta($this->subject(), []);

        $content = $result['search-engine-optimization']['children']['ai_generate_container']['arguments']['data']['config']['content'];
        $this->assertStringContainsString('product[meta_keyword]', $content);
        $this->assertStringContainsString("'https://admin.test/generate'", $content);
    }

    public function testCanonicalUrlIsLoadedIntoProductData(): void
    {
        $connection = $this->connectionStub(['panth_seo_custom_canonical']);
        $connection->method('fetchOne')->willReturnOnConsecutiveCalls('https://example.com/p', '', false);

        $result = $this->plugin(true, false, $connection)->afterGetData($this->subject(), [
            5 => ['product' => ['entity_id' => '5', 'store_id' => '1']],
            6 => ['product' => []],
            7 => ['product' => ['entity_id' => 7]],
            8 => ['no_product' => true],
        ]);

        $this->assertSame('https://example.com/p', $result[5]['product']['custom_canonical_url']);
        $this->assertArrayNotHasKey('custom_canonical_url', $result[6]['product']);
        $this->assertArrayNotHasKey('custom_canonical_url', $result[7]['product']);
        $this->assertSame(['no_product' => true], $result[8]);
    }

    public function testMissingCanonicalTableLeavesDataUntouched(): void
    {
        $data = [5 => ['product' => ['entity_id' => 5]]];

        $this->assertSame($data, $this->plugin(true, false)->afterGetData($this->subject(), $data));
        $this->assertSame([], $this->plugin(true, false)->afterGetData($this->subject(), []));
    }

    private function subject(): ProductDataProvider
    {
        return $this->createStub(ProductDataProvider::class);
    }

    private function plugin(bool $enabled, bool $hasKey, ?AdapterInterface $connection = null): ProductSeoFieldsPlugin
    {
        $config = $this->createStub(Config::class);
        $config->method('isEnabled')->willReturn($enabled);
        $config->method('hasOwnApiKey')->willReturn($hasKey);
        $url = $this->createStub(BackendUrl::class);
        $url->method('getUrl')->willReturn('https://admin.test/generate');

        return new ProductSeoFieldsPlugin(
            new MetaRobots(),
            $this->resourceStub($connection ?? $this->connectionStub()),
            $config,
            $url
        );
    }
}
