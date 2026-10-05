<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\Generate;

use Magento\Framework\App\Request\Http;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Panth\PageBuilderAi\Controller\Adminhtml\Generate\Index;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\AiService;
use Panth\PageBuilderAi\Model\HtmlSanitizer;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    use BackendActionStubs;
    use DbStubs;

    private ?string $sentPrompt = null;

    private ?array $sentImages = null;

    private ?array $sentLogContext = null;

    public function testDisabledModuleIsRejected(): void
    {
        $this->controller($this->request(['custom_prompt' => 'x']), $this->service(), false)->execute();

        $this->assertSame(['success' => false, 'message' => 'PageBuilder AI is disabled.'], $this->json);
    }

    public function testMissingApiKeyIsRejected(): void
    {
        $this->controller($this->request(['custom_prompt' => 'x']), $this->service(), true, false)->execute();

        $this->assertFalse($this->json['success']);
        $this->assertStringContainsString('API key not configured', $this->json['message']);
    }

    #[DataProvider('invalidInputProvider')]
    public function testInvalidInputIsRejectedBeforeGeneration(array $params, string $message): void
    {
        $service = $this->createMock(AiService::class);
        $service->expects($this->never())->method('generate');

        $this->controller($this->request($params), $service)->execute();

        $this->assertSame(['success' => false, 'message' => $message], $this->json);
    }

    public static function invalidInputProvider(): array
    {
        return [
            'no prompt' => [[], 'Prompt is required.'],
            'too long' => [['custom_prompt' => str_repeat('a', 10001)], 'Prompt is too long (max 10000 chars).'],
            'bad image' => [
                ['custom_prompt' => 'x', 'images' => ['javascript:alert(1)']],
                'Image #1 is not a valid image data URI.',
            ],
            'huge image' => [
                ['custom_prompt' => 'x', 'images' => ['data:image/png;base64,' . str_repeat('A', 4000000)]],
                'Image #1 exceeds size limit.',
            ],
        ];
    }

    public function testJsonFormatIsDetectedAndParsed(): void
    {
        $body = json_encode(['custom_prompt' => 'Return meta_title only', 'entity_type' => 'product', 'store_id' => 3]);
        $service = $this->service(['success' => true, 'content' => "```json\n{\"meta_title\":\"T\"}\n```"]);

        $this->controller($this->request([], true, null, $body, 'application/json; charset=UTF-8'), $service)->execute();

        $this->assertSame('Return meta_title only', $this->sentPrompt);
        $this->assertSame('json', $this->sentLogContext['output_format']);
        $this->assertSame('product', $this->sentLogContext['entity_type']);
        $this->assertNull($this->sentLogContext['entity_id']);
        $this->assertSame(3, $this->sentLogContext['store_id']);
        $this->assertTrue($this->json['success']);
        $this->assertSame('T', $this->json['data']['meta_title']);
        $this->assertSame("```json\n{\"meta_title\":\"T\"}\n```", $this->json['data']['content']);
    }

    public function testUnparseableJsonFallsBackToContent(): void
    {
        $service = $this->service(['success' => true, 'content' => 'no braces here']);

        $this->controller($this->request(['custom_prompt' => 'x', 'output_format' => 'JSON']), $service)->execute();

        $this->assertSame(['success' => true, 'data' => ['content' => 'no braces here', 'description' => 'no braces here']], $this->json);
    }

    public function testPlainFormatWrapsBrief(): void
    {
        $this->controller(
            $this->request(['custom_prompt' => 'Write a tagline', 'output_format' => 'plain', 'target_field' => 'title']),
            $this->service()
        )->execute();

        $this->assertStringStartsWith('You are filling a single admin form input.', $this->sentPrompt);
        $this->assertStringEndsWith("=== FIELD BRIEF ===\nWrite a tagline", $this->sentPrompt);
        $this->assertSame('title', $this->sentLogContext['target_field']);
        $this->assertSame(0, $this->sentLogContext['raw_prompt']);
    }

    public function testDefaultFormatPrependsPageBuilderContract(): void
    {
        $this->controller($this->request(['custom_prompt' => 'Hero section', 'images' => ['data:image/gif;base64,R0lG']]), $this->service())
            ->execute();

        $this->assertStringContainsString('data-content-type="row"', $this->sentPrompt);
        $this->assertStringEndsWith("=== USER REQUEST ===\nHero section", $this->sentPrompt);
        $this->assertSame('pagebuilder_html', $this->sentLogContext['output_format']);
        $this->assertSame(['data:image/gif;base64,R0lG'], $this->sentImages);
        $this->assertSame(['content' => 'generated', 'description' => 'generated'], $this->json['data']);
    }

    public function testRawPromptIsSentVerbatimWithoutControlCharacters(): void
    {
        $this->controller($this->request(['custom_prompt' => "Hello\x07World\n", 'raw_prompt' => '1']), $this->service())
            ->execute();

        $this->assertSame("HelloWorld\n", $this->sentPrompt);
        $this->assertSame(1, $this->sentLogContext['raw_prompt']);
    }

    public function testServiceFailureIsReturned(): void
    {
        $this->controller(
            $this->request(['custom_prompt' => 'x']),
            $this->service(['success' => false, 'content' => '', 'message' => 'quota'])
        )->execute();

        $this->assertSame(['success' => false, 'message' => 'quota'], $this->json);
    }

    public function testGeneratedHtmlIsSanitisedBeforeItIsReturned(): void
    {
        $service = $this->service([
            'success' => true,
            'content' => '<div data-content-type="row"><p>Hi <img src=x onerror=alert(1)>'
                . '<a href="javascript:alert(2)">x</a></p><script>alert(3)</script></div>',
        ]);

        $this->controller($this->request(['custom_prompt' => 'Hero']), $service)->execute();

        $this->assertTrue($this->json['success']);
        $html = $this->json['data']['content'];
        $this->assertStringContainsString('data-content-type="row"', $html);
        $this->assertStringContainsString('<p>Hi <img src="x">', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertSame($html, $this->json['data']['description']);
    }

    public function testJsonFieldValuesAreSanitised(): void
    {
        $service = $this->service([
            'success' => true,
            'content' => '{"meta_title":"Safe title","answer":"<p onclick=\\"x()\\">Answer</p>"}',
        ]);

        $this->controller($this->request(['custom_prompt' => 'x', 'output_format' => 'json']), $service)->execute();

        $this->assertSame('Safe title', $this->json['data']['meta_title']);
        $this->assertSame('<p>Answer</p>', $this->json['data']['answer']);
    }

    public function testCmsPagePlaceholdersAreResolved(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn([
            'title' => 'About Us',
            'identifier' => 'about-us',
            'content' => '<p>' . str_repeat('c', 2100) . '</p>',
        ]);

        $this->controller(
            $this->request([
                'custom_prompt' => 'About {{title}} at {{URL}} {{unknown}}!',
                'raw_prompt' => 1,
                'entity_type' => 'cms_page',
                'entity_id' => 4,
            ]),
            $this->service(),
            true,
            true,
            $connection
        )->execute();

        $this->assertSame('About About Us at about-us !', $this->sentPrompt);
    }

    public function testProductPlaceholdersAreResolved(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturnOnConsecutiveCalls(
            ['entity_id' => 5, 'sku' => 'SKU5'],
            ['value' => 'Lights']
        );
        $connection->method('fetchPairs')->willReturn(['name' => 1, 'description' => 3, 'price' => 2]);
        $connection->method('fetchOne')->willReturnOnConsecutiveCalls('Lamp', '<p>Bright</p>', '19.5000');

        $this->controller(
            $this->request([
                'custom_prompt' => '{{name}}|{{sku}}|{{price}}|{{description}}|{{category}}|{{brand}}|{{short_description}}',
                'raw_prompt' => 1,
                'entity_type' => 'product',
                'entity_id' => 5,
            ]),
            $this->service(),
            true,
            true,
            $connection
        )->execute();

        $this->assertSame('Lamp|SKU5|19.5|Bright|Lights||', $this->sentPrompt);
    }

    public function testCategoryPlaceholdersAreResolved(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn(['entity_id' => 3]);
        $connection->method('fetchPairs')->willReturn(['name' => 1, 'url_key' => 2]);
        $connection->method('fetchOne')->willReturnOnConsecutiveCalls('Shoes', 'shoes');

        $this->controller(
            $this->request([
                'custom_prompt' => '{{category}} /{{url}} {{title}}',
                'raw_prompt' => 1,
                'entity_type' => 'category',
                'entity_id' => 3,
            ]),
            $this->service(),
            true,
            true,
            $connection
        )->execute();

        $this->assertSame('Shoes /shoes Shoes', $this->sentPrompt);
    }

    public function testPlaceholderLookupFailureStripsTokens(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willThrowException(new \RuntimeException('db'));

        $this->controller(
            $this->request(['custom_prompt' => 'Name: {{name}}.', 'raw_prompt' => 1, 'entity_type' => 'product', 'entity_id' => 1]),
            $this->service(),
            true,
            true,
            $connection
        )->execute();

        $this->assertSame('Name: .', $this->sentPrompt);
    }

    private function service(array $response = ['success' => true, 'content' => 'generated']): AiService
    {
        $service = $this->createStub(AiService::class);
        $service->method('generate')->willReturnCallback(
            function (string $prompt, array $images, array $logContext) use ($response) {
                $this->sentPrompt = $prompt;
                $this->sentImages = $images;
                $this->sentLogContext = $logContext;
                return $response;
            }
        );
        return $service;
    }

    private function controller(
        Http $request,
        AiService $service,
        bool $enabled = true,
        bool $hasKey = true,
        ?AdapterInterface $connection = null
    ): Index {
        $config = $this->createStub(Config::class);
        $config->method('isEnabled')->willReturn($enabled);
        $config->method('hasOwnApiKey')->willReturn($hasKey);

        return new Index(
            $this->context($request),
            $this->jsonFactory(),
            $config,
            $service,
            $this->resourceStub($connection ?? $this->connectionStub()),
            new HtmlSanitizer()
        );
    }
}
