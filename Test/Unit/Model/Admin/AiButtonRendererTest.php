<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model\Admin;

use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\Admin\AiButtonRenderer;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AiButtonRendererTest extends TestCase
{
    use DbStubs;

    #[DataProvider('availabilityProvider')]
    public function testAvailabilityRequiresEnabledModuleAndKey(bool $enabled, bool $hasKey, bool $expected): void
    {
        $config = $this->createStub(Config::class);
        $config->method('isEnabled')->willReturn($enabled);
        $config->method('hasOwnApiKey')->willReturn($hasKey);

        $renderer = new AiButtonRenderer($config, $this->createStub(BackendUrl::class), $this->resourceStub($this->connectionStub()));

        $this->assertSame($expected, $renderer->isAvailable());
    }

    public static function availabilityProvider(): array
    {
        return [[true, true, true], [true, false, false], [false, true, false], [false, false, false]];
    }

    public function testContainerMetaWrapsRenderedPanel(): void
    {
        $meta = $this->renderer()->buildContainerMeta(
            'faq',
            'item_id',
            'store_id',
            ['answer' => 'answer'],
            ['answer' => ['label' => 'FAQ Answer', 'field' => 'answer', 'prompt' => 'Write it']],
            'faq',
            'Help text',
            7
        );

        $config = $meta['arguments']['data']['config'];
        $this->assertSame('container', $config['componentType']);
        $this->assertSame('Magento_Ui/js/form/components/html', $config['component']);
        $this->assertSame(7, $config['sortOrder']);
        $this->assertSame('panth-pagebuilderai-ai-generate-wrapper', $config['additionalClasses']);

        $html = $config['content'];
        $this->assertStringContainsString("var generateUrl = 'https://admin.test/generate?a=1&amp;b=2';", $html);
        $this->assertStringContainsString("var entityType = 'faq';", $html);
        $this->assertStringContainsString("var idFieldName = 'item_id';", $html);
        $this->assertStringContainsString("var storeFieldName = 'store_id';", $html);
        $this->assertStringContainsString('var fieldMap = {"answer":"answer"};', $html);
        $this->assertStringContainsString('placeholder="Help text"', $html);
        $this->assertStringContainsString('id="panth-ai-prompt-select-faq"', $html);
        $this->assertStringContainsString('var prompts = [];', $html);
    }

    public function testUserControlledValuesAreEscaped(): void
    {
        $connection = $this->connectionStub(['panth_seo_ai_prompt']);
        $connection->method('fetchAll')->willReturn([
            ['prompt_id' => '3', 'name' => "Bad'</script>", 'prompt_template' => '<b>"x"</b>', 'is_default' => '1'],
        ]);

        $html = $this->renderer($connection)->buildContainerMeta(
            'banner',
            'slide_id',
            '',
            ['title' => 'title'],
            [],
            'ban-ner!<x>',
            '<img src=x>'
        )['arguments']['data']['config']['content'];

        $this->assertStringContainsString("var S = 'bannerx';", $html);
        $this->assertStringContainsString('placeholder="&lt;img src=x&gt;"', $html);
        $this->assertStringNotContainsString('</script>"', $html);
        $this->assertStringContainsString("\x5Cu0027\x5Cu003C\x5C/script\x5Cu003E", $html);
        $this->assertStringContainsString('"id":3', $html);
        $this->assertStringContainsString('"is_default":1', $html);
        $this->assertSame(5, $this->renderer()->buildContainerMeta('x', 'id', '', [], [], 'x')['arguments']['data']['config']['sortOrder']);
    }

    public function testDefaultPlaceholderAndPromptLoadFailure(): void
    {
        $connection = $this->connectionStub(['panth_seo_ai_prompt']);
        $connection->method('fetchAll')->willThrowException(new \RuntimeException('db'));

        $html = $this->renderer($connection)->buildContainerMeta('faq', 'item_id', '', [], [], 'f')
            ['arguments']['data']['config']['content'];

        $this->assertStringContainsString('Type your custom prompt here', $html);
        $this->assertStringContainsString('var prompts = [];', $html);
    }

    private function renderer(?AdapterInterface $connection = null): AiButtonRenderer
    {
        $url = $this->createStub(BackendUrl::class);
        $url->method('getUrl')->willReturn('https://admin.test/generate?a=1&b=2');

        return new AiButtonRenderer(
            $this->createStub(Config::class),
            $url,
            $this->resourceStub($connection ?? $this->connectionStub())
        );
    }
}
