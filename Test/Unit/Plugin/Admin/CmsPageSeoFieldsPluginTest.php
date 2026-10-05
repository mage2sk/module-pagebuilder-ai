<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Plugin\Admin;

use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Cms\Model\Page\DataProvider as CmsPageDataProvider;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\Config\Source\MetaRobots;
use Panth\PageBuilderAi\Plugin\Admin\CmsPageSeoFieldsPlugin;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class CmsPageSeoFieldsPluginTest extends TestCase
{
    use DbStubs;

    public function testDisabledModuleLeavesMetaAndDataUntouched(): void
    {
        $plugin = $this->plugin(false, true, $this->connectionStub(['panth_seo_override']));

        $this->assertSame(['content' => []], $plugin->afterGetMeta($this->subject(), ['content' => []]));
        $this->assertSame([1 => ['page_id' => 1]], $plugin->afterGetData($this->subject(), [1 => ['page_id' => 1]]));
    }

    public function testOverrideFieldsAreHiddenWithoutOverrideTable(): void
    {
        $result = $this->plugin(true, false)->afterGetMeta($this->subject(), []);

        $fieldset = $result['search_engine_optimisation'];
        $this->assertSame('fieldset', $fieldset['arguments']['data']['config']['componentType']);
        $this->assertSame([], $fieldset['children']);
    }

    public function testOverrideFieldsAndButtonsAreAdded(): void
    {
        $result = $this->plugin(true, true, $this->connectionStub(['panth_seo_override']))
            ->afterGetMeta($this->subject(), ['content' => ['children' => []]]);

        $children = $result['search_engine_optimisation']['children'];
        $this->assertSame(['meta_robots', 'hreflang_identifier', 'ai_generate_container'], array_keys($children));
        $this->assertStringContainsString(
            "'https://admin.test/generate'",
            $children['ai_generate_container']['arguments']['data']['config']['content']
        );
        $this->assertStringContainsString(
            'panthOpenContentAiPopup()',
            $result['content']['children']['ai_content_container']['arguments']['data']['config']['content']
        );
    }

    public function testContentButtonRequiresContentSection(): void
    {
        $result = $this->plugin(true, true)->afterGetMeta($this->subject(), []);

        $this->assertArrayNotHasKey('content', $result);
        $this->assertArrayHasKey('ai_generate_container', $result['search_engine_optimisation']['children']);
    }

    public function testOverrideValuesAreMergedIntoPageData(): void
    {
        $connection = $this->connectionStub(['panth_seo_override']);
        $connection->method('fetchRow')->willReturnOnConsecutiveCalls(
            ['robots' => 'NOINDEX,FOLLOW', 'hreflang_identifier' => 'about'],
            ['robots' => '', 'hreflang_identifier' => null],
            false
        );

        $result = $this->plugin(true, false, $connection)->afterGetData($this->subject(), [
            1 => ['page_id' => 1, 'store_id' => ['2', '3']],
            2 => ['page_id' => 2, 'store_id' => '1'],
            3 => ['title' => 'No override'],
            'meta' => 'scalar',
        ]);

        $this->assertSame('NOINDEX,FOLLOW', $result[1]['meta_robots']);
        $this->assertSame('about', $result[1]['hreflang_identifier']);
        $this->assertArrayNotHasKey('meta_robots', $result[2]);
        $this->assertSame(['title' => 'No override'], $result[3]);
        $this->assertSame('scalar', $result['meta']);
    }

    public function testDataIsUnchangedWithoutOverrideTableOrRows(): void
    {
        $plugin = $this->plugin(true, false);

        $this->assertSame([], $plugin->afterGetData($this->subject(), []));
        $this->assertSame([1 => ['page_id' => 1]], $plugin->afterGetData($this->subject(), [1 => ['page_id' => 1]]));
    }

    private function subject(): CmsPageDataProvider
    {
        return $this->createStub(CmsPageDataProvider::class);
    }

    private function plugin(bool $enabled, bool $hasKey, ?AdapterInterface $connection = null): CmsPageSeoFieldsPlugin
    {
        $config = $this->createStub(Config::class);
        $config->method('isEnabled')->willReturn($enabled);
        $config->method('hasOwnApiKey')->willReturn($hasKey);
        $url = $this->createStub(BackendUrl::class);
        $url->method('getUrl')->willReturn('https://admin.test/generate');

        return new CmsPageSeoFieldsPlugin(
            new MetaRobots(),
            $this->resourceStub($connection ?? $this->connectionStub()),
            $config,
            $url
        );
    }
}
