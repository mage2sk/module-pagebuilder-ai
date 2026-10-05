<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\ViewModel;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\ResourceModel\AiPrompt\Collection;
use Panth\PageBuilderAi\Model\ResourceModel\AiPrompt\CollectionFactory;
use Panth\PageBuilderAi\ViewModel\AiInit;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AiInitTest extends TestCase
{
    public function testDelegatesToConfigAndUrlBuilder(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('isEnabled')->willReturn(true);
        $config->method('getActiveBackend')->willReturn('own');
        $url = $this->createMock(UrlInterface::class);
        $url->expects($this->once())->method('getUrl')->with('panth_pagebuilderai/generate/index')->willReturn('/gen');

        $viewModel = new AiInit($config, $url, $this->createStub(CollectionFactory::class), new NullLogger());

        $this->assertTrue($viewModel->isAvailable());
        $this->assertSame('/gen', $viewModel->getGenerateUrl());
        $this->assertSame('own', $viewModel->getActiveBackend());
    }

    public function testSavedPromptsAreFilteredAndMapped(): void
    {
        $filters = [];
        $collection = $this->createStub(Collection::class);
        $collection->method('addFieldToFilter')->willReturnCallback(
            function ($field, $condition) use (&$filters, &$collection) {
                $filters[$field] = $condition;
                return $collection;
            }
        );
        $collection->method('setOrder')->willReturnSelf();
        $collection->method('getIterator')->willReturn(new \ArrayIterator([
            new DataObject(['prompt_id' => '4', 'name' => 'Hero', 'prompt_template' => 'Write {{name}}']),
            new DataObject(['prompt_id' => 9, 'name' => null]),
        ]));
        $factory = $this->createStub(CollectionFactory::class);
        $factory->method('create')->willReturn($collection);

        $prompts = (new AiInit($this->createStub(Config::class), $this->createStub(UrlInterface::class), $factory, new NullLogger()))
            ->getSavedPrompts();

        $this->assertSame([
            ['id' => 4, 'name' => 'Hero', 'template' => 'Write {{name}}'],
            ['id' => 9, 'name' => '', 'template' => ''],
        ], $prompts);
        $this->assertSame(1, $filters['is_active']);
        $this->assertSame(['in' => ['cms_page', 'all', 'pagebuilder']], $filters['entity_type']);
    }

    public function testPromptLoadFailureReturnsEmptyList(): void
    {
        $factory = $this->createStub(CollectionFactory::class);
        $factory->method('create')->willThrowException(new \RuntimeException('no table'));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('no table'));

        $viewModel = new AiInit($this->createStub(Config::class), $this->createStub(UrlInterface::class), $factory, $logger);

        $this->assertSame([], $viewModel->getSavedPrompts());
    }
}
