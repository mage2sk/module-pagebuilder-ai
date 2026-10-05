<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\AiGenerate;

use Magento\Framework\App\Request\Http;
use Panth\PageBuilderAi\Api\AiGeneratorInterface;
use Panth\PageBuilderAi\Controller\Adminhtml\AiGenerate\Generate;
use Panth\PageBuilderAi\Model\Score\ContextBuilder;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class GenerateTest extends TestCase
{
    use BackendActionStubs;

    private ?array $sentContext = null;

    private ?array $sentFields = null;

    public function testGetRequestIsRejected(): void
    {
        $this->controller($this->request(['entity_type' => 'product', 'entity_id' => 1], false))->execute();

        $this->assertSame(['success' => false, 'message' => 'POST request required.'], $this->json);
    }

    #[DataProvider('invalidRequestProvider')]
    public function testInvalidInputIsRejected(array $params, string $expectedMessage): void
    {
        $generator = $this->createMock(AiGeneratorInterface::class);
        $generator->expects($this->never())->method('generate');

        $this->controller($this->request($params), $generator)->execute();

        $this->assertFalse($this->json['success']);
        $this->assertStringContainsString($expectedMessage, $this->json['message']);
    }

    public static function invalidRequestProvider(): array
    {
        return [
            'bad image' => [
                ['entity_type' => 'product', 'entity_id' => 1, 'images' => ['data:text/plain;base64,AAAA']],
                'Images must be PNG, JPEG, GIF or WebP',
            ],
            'unknown entity type' => [['entity_type' => 'order', 'entity_id' => 1], 'Invalid entity_type'],
            'unsaved entity' => [['entity_type' => 'product', 'entity_id' => 0], 'Please save first'],
        ];
    }

    public function testTargetFieldAppendsFieldInstruction(): void
    {
        $generator = $this->generator(['title' => 'Generated title']);

        $this->controller($this->request([
            'entity_type' => 'product',
            'entity_id' => '5',
            'store_id' => '2',
            'target_field' => 'meta_title',
            'custom_prompt' => 'Base brief',
            'prompt_id' => 9,
        ]), $generator)->execute();

        $this->assertSame(['meta_title'], $this->sentFields);
        $this->assertSame(['meta_title'], $this->sentContext['fields']);
        $this->assertSame(
            "Base brief\n\nGenerate ONLY a meta title (50-60 characters) optimized for search engines.",
            $this->sentContext['custom_prompt']
        );
        $this->assertArrayNotHasKey('prompt_id', $this->sentContext);
        $this->assertSame(['product', 5, 2], $this->sentContext['built']);
        $this->assertSame([
            'success' => true,
            'data' => ['meta_title' => 'Generated title'],
            'tokens_used' => 42,
            'provider' => 'test',
        ], $this->json);
    }

    public function testJsonBodyFieldsAreFilteredAndPromptIdForwarded(): void
    {
        $generator = $this->generator(['answer' => 'An answer', 'meta_title' => 'not requested']);
        $body = json_encode(['entity_type' => 'faq', 'entity_id' => 3, 'fields' => ['answer', 'bogus'], 'prompt_id' => 4]);

        $this->controller($this->request([], true, null, $body), $generator)->execute();

        $this->assertSame(['answer'], $this->sentFields);
        $this->assertSame(4, $this->sentContext['prompt_id']);
        $this->assertArrayNotHasKey('custom_prompt', $this->sentContext);
        $this->assertSame(['answer' => 'An answer'], $this->json['data']);
    }

    public function testDefaultFieldsDependOnEntityType(): void
    {
        $generator = $this->generator(['meta_keywords' => 'a,b']);

        $this->controller($this->request(['entity_type' => 'cms_page', 'entity_id' => 1]), $generator)->execute();

        $this->assertSame(['meta_title', 'meta_description', 'meta_keywords'], $this->sentFields);
        $this->assertSame(['meta_keywords' => 'a,b'], $this->json['data']);
    }

    public function testValidImagesAreForwarded(): void
    {
        $generator = $this->generator(['description' => 'From image']);
        $images = ['data:image/png;base64,AAAA', 12, 'data:image/jpeg;base64,BBBB'];

        $this->controller($this->request(['entity_type' => 'product', 'entity_id' => 1, 'images' => $images]), $generator)
            ->execute();

        $this->assertSame(['data:image/png;base64,AAAA', 'data:image/jpeg;base64,BBBB'], array_values($this->sentContext['images']));
        $this->assertSame(['meta_description' => 'From image'], $this->json['data']);
    }

    public function testEmptyGenerationIsReportedAsFailure(): void
    {
        $this->controller(
            $this->request(['entity_type' => 'banner', 'entity_id' => 1]),
            $this->generator(['title' => '', 'confidence' => 0.0])
        )->execute();

        $this->assertFalse($this->json['success']);
        $this->assertStringContainsString('empty results', $this->json['message']);
    }

    #[DataProvider('exceptionProvider')]
    public function testExceptionMessagesAreFiltered(string $error, string $expected): void
    {
        $generator = $this->createStub(AiGeneratorInterface::class);
        $generator->method('generate')->willThrowException(new \RuntimeException($error));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');

        $this->controller($this->request(['entity_type' => 'product', 'entity_id' => 1]), $generator, $logger)->execute();

        $this->assertSame(['success' => false, 'message' => $expected], $this->json);
    }

    public static function exceptionProvider(): array
    {
        $generic = 'An unexpected error occurred. Please check the system logs.';
        return [
            ['Invalid API key supplied', 'Invalid API key supplied'],
            ['Monthly budget reached', 'Monthly budget reached'],
            ['SQLSTATE[HY000] connection refused', $generic],
        ];
    }

    private function generator(array $result): AiGeneratorInterface
    {
        $generator = $this->createStub(AiGeneratorInterface::class);
        $generator->method('generate')->willReturnCallback(function (array $context, array $fields) use ($result) {
            $this->sentContext = $context;
            $this->sentFields = $fields;
            return $result;
        });
        $generator->method('getLastUsageTokens')->willReturn(42);
        $generator->method('getProvider')->willReturn('test');
        return $generator;
    }

    private function controller(
        Http $request,
        ?AiGeneratorInterface $generator = null,
        ?LoggerInterface $logger = null
    ): Generate {
        $contextBuilder = $this->createStub(ContextBuilder::class);
        $contextBuilder->method('build')->willReturnCallback(
            static fn (string $type, int $id, int $store) => ['built' => [$type, $id, $store]]
        );

        return new Generate(
            $this->context($request),
            $this->jsonFactory(),
            $contextBuilder,
            $generator ?? $this->createStub(AiGeneratorInterface::class),
            $logger ?? new NullLogger()
        );
    }
}
