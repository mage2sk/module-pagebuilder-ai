<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Support;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\PageFactory;

trait BackendActionStubs
{
    /** @var array<int, array{0: string, 1: string}> */
    protected array $messages = [];

    protected ?array $redirect = null;

    protected ?array $json = null;

    protected ?string $activeMenu = null;

    /** @var string[] */
    protected array $titles = [];

    protected function request(
        array $params = [],
        bool $isPost = true,
        mixed $post = null,
        string $content = '',
        string $contentType = ''
    ): Http {
        $request = $this->createStub(Http::class);
        $request->method('isPost')->willReturn($isPost);
        $request->method('getParam')->willReturnCallback(
            static fn ($key, $default = null) => $params[$key] ?? $default
        );
        $request->method('getParams')->willReturn($params);
        $request->method('getPostValue')->willReturn($post ?? $params);
        $request->method('getContent')->willReturn($content);
        $request->method('getHeader')->willReturnCallback(
            static fn ($name) => $name === 'Content-Type' ? $contentType : false
        );
        return $request;
    }

    /**
     * @param array<string, mixed> $extra context getter => value
     */
    protected function context(RequestInterface $request, array $extra = []): Context
    {
        $messages = $this->createStub(ManagerInterface::class);
        $map = [
            'addErrorMessage' => 'error',
            'addSuccessMessage' => 'success',
            'addNoticeMessage' => 'notice',
            'addWarningMessage' => 'warning',
        ];
        foreach ($map as $method => $type) {
            $messages->method($method)->willReturnCallback(
                function ($message) use ($type, &$messages) {
                    $this->messages[] = [$type, (string)$message];
                    return $messages;
                }
            );
        }

        $redirect = $this->createStub(Redirect::class);
        $redirect->method('setPath')->willReturnCallback(
            function ($path, $params = []) use (&$redirect) {
                $this->redirect = [$path, $params];
                return $redirect;
            }
        );
        $redirectFactory = $this->createStub(RedirectFactory::class);
        $redirectFactory->method('create')->willReturn($redirect);

        $context = $this->createStub(Context::class);
        $context->method('getRequest')->willReturn($request);
        $context->method('getMessageManager')->willReturn($messages);
        $context->method('getResultRedirectFactory')->willReturn($redirectFactory);
        foreach ($extra as $getter => $value) {
            $context->method($getter)->willReturn($value);
        }
        return $context;
    }

    protected function jsonFactory(): JsonFactory
    {
        $json = $this->createStub(Json::class);
        $json->method('setData')->willReturnCallback(
            function ($data) use (&$json) {
                $this->json = $data;
                return $json;
            }
        );
        $factory = $this->createStub(JsonFactory::class);
        $factory->method('create')->willReturn($json);
        return $factory;
    }

    protected function pageFactory(mixed $block = false): PageFactory
    {
        $title = $this->createStub(Title::class);
        $title->method('prepend')->willReturnCallback(function ($text) {
            $this->titles[] = (string)$text;
        });
        $config = $this->createStub(PageConfig::class);
        $config->method('getTitle')->willReturn($title);
        $layout = $this->createStub(LayoutInterface::class);
        $layout->method('getBlock')->willReturn($block);
        $page = $this->createStub(Page::class);
        $page->method('setActiveMenu')->willReturnCallback(function ($menu) use (&$page) {
            $this->activeMenu = $menu;
            return $page;
        });
        $page->method('getConfig')->willReturn($config);
        $page->method('getLayout')->willReturn($layout);
        $factory = $this->createStub(PageFactory::class);
        $factory->method('create')->willReturn($page);
        return $factory;
    }

    protected function formKey(bool $valid): FormKeyValidator
    {
        $validator = $this->createStub(FormKeyValidator::class);
        $validator->method('validate')->willReturn($valid);
        return $validator;
    }

    protected function messagesOfType(string $type): array
    {
        return array_values(array_map(
            static fn (array $m) => $m[1],
            array_filter($this->messages, static fn (array $m) => $m[0] === $type)
        ));
    }
}
