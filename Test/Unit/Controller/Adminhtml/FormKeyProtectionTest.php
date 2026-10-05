<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Request\BackendValidator;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Panth\PageBuilderAi\Api\AiGeneratorInterface;
use Panth\PageBuilderAi\Controller\Adminhtml\AiGenerate\Generate;
use Panth\PageBuilderAi\Controller\Adminhtml\Generate\Index;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\AiService;
use Panth\PageBuilderAi\Model\HtmlSanitizer;
use Panth\PageBuilderAi\Model\Score\ContextBuilder;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class FormKeyProtectionTest extends TestCase
{
    use BackendActionStubs;
    use DbStubs;

    public static function controllerProvider(): array
    {
        return [
            'page builder generate' => [Index::class, 'Panth_PageBuilderAi::generate'],
            'entity meta generate' => [Generate::class, 'Panth_PageBuilderAi::ai_generate'],
        ];
    }

    #[DataProvider('controllerProvider')]
    public function testAjaxEndpointsUseTheFrameworkFormKeyCheck(string $class, string $resource): void
    {
        $reflection = new \ReflectionClass($class);

        $this->assertTrue($reflection->isSubclassOf(AbstractAction::class));
        $this->assertTrue($reflection->implementsInterface(HttpPostActionInterface::class));
        $this->assertFalse($reflection->implementsInterface(CsrfAwareActionInterface::class));
        $this->assertSame($resource, $reflection->getConstant('ADMIN_RESOURCE'));
    }

    #[DataProvider('controllerProvider')]
    public function testAclResourceIsChecked(string $class, string $resource): void
    {
        $authorization = $this->createMock(AuthorizationInterface::class);
        $authorization->expects($this->once())->method('isAllowed')->with($resource)->willReturn(false);

        $controller = $this->build($class, false, $authorization, $this->neverCalledService(), $this->neverCalledGenerator());
        $method = new \ReflectionMethod($controller, '_isAllowed');

        $this->assertFalse($method->invoke($controller));
    }

    public static function classProvider(): array
    {
        return [
            'page builder generate' => [Index::class],
            'entity meta generate' => [Generate::class],
        ];
    }

    #[DataProvider('classProvider')]
    public function testInvalidFormKeyStopsTheRequestBeforeAnyProviderCall(string $class): void
    {
        $controller = $this->build($class, false, null, $this->neverCalledService(), $this->neverCalledGenerator());

        $validator = new BackendValidator(
            $this->createStub(Auth::class),
            $this->formKey(false),
            $this->createStub(BackendUrl::class),
            $this->createStub(RedirectFactory::class),
            $this->createStub(RawFactory::class)
        );

        $this->expectException(InvalidRequestException::class);
        $validator->validate($controller->getRequest(), $controller);
    }

    #[DataProvider('classProvider')]
    public function testValidFormKeyPassesTheCheck(string $class): void
    {
        $controller = $this->build($class, true, null, $this->neverCalledService(), $this->neverCalledGenerator());

        $this->assertTrue($controller->_processUrlKeys());
    }

    private function neverCalledService(): AiService
    {
        $service = $this->createMock(AiService::class);
        $service->expects($this->never())->method('generate');
        return $service;
    }

    private function neverCalledGenerator(): AiGeneratorInterface
    {
        $generator = $this->createMock(AiGeneratorInterface::class);
        $generator->expects($this->never())->method('generate');
        return $generator;
    }

    private function build(
        string $class,
        bool $validFormKey,
        ?AuthorizationInterface $authorization,
        AiService $service,
        AiGeneratorInterface $generator
    ): AbstractAction {
        $params = ['custom_prompt' => 'Write a hero', 'entity_type' => 'product', 'entity_id' => 1];
        $request = $this->createStub(Http::class);
        $request->method('isPost')->willReturn(true);
        $request->method('getParams')->willReturn($params);
        $request->method('getPostValue')->willReturn($params);
        $request->method('getQuery')->willReturnCallback(
            static fn ($key, $default = null) => $key === 'isAjax' ? 'true' : $default
        );
        $jsonHelper = $this->createStub(JsonHelper::class);
        $jsonHelper->method('jsonEncode')->willReturn('{}');
        $objectManager = $this->createStub(ObjectManagerInterface::class);
        $objectManager->method('get')->willReturn($jsonHelper);

        $auth = $this->createStub(Auth::class);
        $auth->method('isLoggedIn')->willReturn(true);

        $context = $this->context($request, [
            'getAuth' => $auth,
            'getFormKeyValidator' => $this->formKey($validFormKey),
            'getActionFlag' => $this->createStub(ActionFlag::class),
            'getBackendUrl' => $this->createStub(BackendUrl::class),
            'getRedirect' => $this->createStub(RedirectInterface::class),
            'getResponse' => $this->createStub(HttpResponse::class),
            'getAuthorization' => $authorization ?? $this->createStub(AuthorizationInterface::class),
            'getObjectManager' => $objectManager,
        ]);

        if ($class === Index::class) {
            $config = $this->createStub(Config::class);
            $config->method('isEnabled')->willReturn(true);
            $config->method('hasOwnApiKey')->willReturn(true);
            return new Index(
                $context,
                $this->jsonFactory(),
                $config,
                $service,
                $this->resourceStub($this->connectionStub()),
                new HtmlSanitizer()
            );
        }

        return new Generate(
            $context,
            $this->jsonFactory(),
            $this->createStub(ContextBuilder::class),
            $generator,
            new NullLogger()
        );
    }
}
