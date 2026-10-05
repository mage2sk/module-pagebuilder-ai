<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Plugin\Admin;

use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Catalog\Model\Category\DataProvider as CategoryDataProvider;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\Config\Source\MetaRobots;
use Panth\PageBuilderAi\Plugin\Admin\CategorySeoFieldsPlugin;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class CategorySeoFieldsPluginTest extends TestCase
{
    use DbStubs;

    public function testDisabledModuleLeavesMetaUntouched(): void
    {
        $meta = ['general' => ['children' => []]];

        $this->assertSame($meta, $this->plugin(false, false)->afterGetMeta($this->subject(), $meta));
    }

    public function testSeoFieldsAreAddedToHyphenatedGroupByDefault(): void
    {
        $result = $this->plugin(true, false)->afterGetMeta($this->subject(), []);

        $children = $result['search-engine-optimization']['children'];
        $this->assertSame(['meta_robots', 'og_title', 'og_description', 'og_image', 'exclude_from_sitemap'], array_keys($children));
        $robots = $children['meta_robots']['arguments']['data']['config'];
        $this->assertSame('select', $robots['formElement']);
        $this->assertSame(
            array_column((new MetaRobots())->toOptionArray(), 'value'),
            array_column($robots['options'], 'value')
        );
        $this->assertSame('in_xml_sitemap', $children['exclude_from_sitemap']['arguments']['data']['config']['dataScope']);
        $this->assertArrayNotHasKey('search_engine_optimization', $result);
    }

    public function testExistingUnderscoredGroupIsReused(): void
    {
        $result = $this->plugin(true, false)->afterGetMeta(
            $this->subject(),
            ['search_engine_optimization' => ['children' => ['url_key' => []]]]
        );

        $this->assertArrayHasKey('url_key', $result['search_engine_optimization']['children']);
        $this->assertArrayHasKey('og_title', $result['search_engine_optimization']['children']);
        $this->assertArrayNotHasKey('search-engine-optimization', $result);
    }

    public function testGenerateButtonIsAddedWhenKeyConfigured(): void
    {
        $connection = $this->connectionStub(['panth_seo_ai_prompt']);
        $connection->method('fetchAll')->willReturn([
            ['prompt_id' => '2', 'name' => "Cat's prompt", 'prompt_template' => 'T', 'is_default' => '1'],
        ]);

        $result = $this->plugin(true, true, $connection)->afterGetMeta($this->subject(), []);

        $config = $result['search-engine-optimization']['children']['ai_generate_container']['arguments']['data']['config'];
        $this->assertSame('container', $config['componentType']);
        $this->assertSame(5, $config['sortOrder']);
        $this->assertStringContainsString("'https://admin.test/generate'", $config['content']);
        $this->assertStringContainsString("\"name\":\"Cat\x5Cu0027s prompt\"", $config['content']);
        $this->assertStringContainsString('"is_default":1', $config['content']);
    }

    private function subject(): CategoryDataProvider
    {
        return $this->createStub(CategoryDataProvider::class);
    }

    private function plugin(bool $enabled, bool $hasKey, ?AdapterInterface $connection = null): CategorySeoFieldsPlugin
    {
        $config = $this->createStub(Config::class);
        $config->method('isEnabled')->willReturn($enabled);
        $config->method('hasOwnApiKey')->willReturn($hasKey);
        $url = $this->createStub(BackendUrl::class);
        $url->method('getUrl')->willReturn('https://admin.test/generate');

        return new CategorySeoFieldsPlugin(
            new MetaRobots(),
            $this->resourceStub($connection ?? $this->connectionStub()),
            $config,
            $url
        );
    }
}
