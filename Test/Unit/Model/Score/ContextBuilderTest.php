<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model\Score;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Panth\PageBuilderAi\Model\Score\ContextBuilder;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ContextBuilderTest extends TestCase
{
    use DbStubs;

    public function testProductContext(): void
    {
        $product = new DataObject([
            'meta_title' => 'MT',
            'meta_description' => 'MD',
            'meta_keyword' => 'k1,k2',
            'description' => '<p>Desc</p>',
            'name' => 'Lamp',
            'sku' => 'L1',
            'brand' => 'Acme',
            'image' => '/l/a.jpg',
            'price' => '19.90',
        ]);
        $products = $this->createMock(ProductRepositoryInterface::class);
        $products->expects($this->once())->method('getById')->with(7, false, 2)->willReturn($product);

        $ctx = $this->builder($products)->build('product', 7, 2);

        $this->assertSame('product', $ctx['entity_type']);
        $this->assertSame(7, $ctx['entity_id']);
        $this->assertSame(2, $ctx['store_id']);
        $this->assertSame(['title' => 'MT', 'description' => 'MD', 'keywords' => 'k1,k2'], $ctx['meta']);
        $this->assertSame('<p>Desc</p>', $ctx['content']);
        $this->assertSame(
            ['name' => 'Lamp', 'sku' => 'L1', 'brand' => 'Acme', 'image' => '/l/a.jpg', 'price' => 19.9],
            $ctx['attributes']
        );
        $this->assertSame($product, $ctx['entity']);
    }

    public function testCategoryContext(): void
    {
        $category = new DataObject([
            'meta_title' => 'CT',
            'meta_keywords' => 'ck',
            'description' => 'Cat desc',
            'name' => 'Shoes',
            'image_url' => 'https://example.com/c.jpg',
        ]);
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->expects($this->once())->method('get')->with(4, 1)->willReturn($category);

        $ctx = $this->builder(null, $categories)->build('category', 4, 1);

        $this->assertSame(['title' => 'CT', 'description' => '', 'keywords' => 'ck'], $ctx['meta']);
        $this->assertSame('Cat desc', $ctx['content']);
        $this->assertSame(['name' => 'Shoes', 'image' => 'https://example.com/c.jpg'], $ctx['attributes']);
    }

    public function testCmsPageContext(): void
    {
        $page = new DataObject([
            'meta_title' => 'PT',
            'meta_description' => 'PD',
            'meta_keywords' => 'pk',
            'content' => 'Body',
            'title' => 'About',
        ]);
        $pages = $this->createStub(PageRepositoryInterface::class);
        $pages->method('getById')->willReturn($page);

        $ctx = $this->builder(null, null, $pages)->build('cms_page', 9, 0);

        $this->assertSame(['title' => 'PT', 'description' => 'PD', 'keywords' => 'pk'], $ctx['meta']);
        $this->assertSame('Body', $ctx['content']);
        $this->assertSame(['name' => 'About'], $ctx['attributes']);
    }

    public function testRepositoryFailureReturnsBaseContext(): void
    {
        $products = $this->createStub(ProductRepositoryInterface::class);
        $products->method('getById')->willThrowException(new \RuntimeException('missing'));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('missing'));

        $ctx = $this->builder($products, null, null, null, $logger)->build('product', 1, 0);

        $this->assertSame('', $ctx['content']);
        $this->assertSame([], $ctx['attributes']);
        $this->assertArrayNotHasKey('entity', $ctx);
    }

    public function testFaqRowIsMapped(): void
    {
        $connection = $this->connectionStub(['panth_faq_item']);
        $connection->method('fetchRow')->willReturn([
            'question' => 'How?',
            'answer' => 'Like this.',
            'meta_title' => 'FT',
            'meta_description' => '',
            'meta_keywords' => 'fk',
        ]);

        $ctx = $this->builder(null, null, null, $connection)->build('faq', 3, 0);

        $this->assertSame('Like this.', $ctx['content']);
        $this->assertSame(['name' => 'How?'], $ctx['attributes']);
        $this->assertSame(['title' => 'FT', 'description' => '', 'keywords' => 'fk'], $ctx['meta']);
    }

    public function testDynamicFormExtraColumnsAreCopied(): void
    {
        $connection = $this->connectionStub(['panth_dynamic_form']);
        $connection->method('fetchRow')->willReturn([
            'title' => 'Contact',
            'description' => 'Reach us',
            'content_above' => 'Above',
            'success_message' => 'Thanks',
        ]);

        $ctx = $this->builder(null, null, null, $connection)->build('dynamic_form', 3, 0);

        $this->assertSame(
            ['name' => 'Contact', 'content_above' => 'Above', 'success_message' => 'Thanks'],
            $ctx['attributes']
        );
        $this->assertSame('Reach us', $ctx['content']);
    }

    public function testMissingThirdPartyTableIsSkipped(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info')->with($this->stringContains('panth_banner_slide not found'));

        $ctx = $this->builder(null, null, null, $this->connectionStub(), $logger)->build('banner', 1, 0);

        $this->assertSame('', $ctx['content']);
    }

    public function testMissingThirdPartyRowAndUnknownTypeKeepBaseContext(): void
    {
        $connection = $this->connectionStub(['panth_testimonial']);
        $connection->method('fetchRow')->willReturn(false);
        $builder = $this->builder(null, null, null, $connection);

        $this->assertSame([], $builder->build('testimonial', 1, 0)['attributes']);
        $this->assertSame([], $builder->build('something_else', 1, 0)['attributes']);
    }

    private function builder(
        ?ProductRepositoryInterface $products = null,
        ?CategoryRepositoryInterface $categories = null,
        ?PageRepositoryInterface $pages = null,
        ?AdapterInterface $connection = null,
        ?LoggerInterface $logger = null
    ): ContextBuilder {
        return new ContextBuilder(
            $products ?? $this->createStub(ProductRepositoryInterface::class),
            $categories ?? $this->createStub(CategoryRepositoryInterface::class),
            $pages ?? $this->createStub(PageRepositoryInterface::class),
            $this->resourceStub($connection ?? $this->connectionStub()),
            $logger ?? new NullLogger()
        );
    }
}
