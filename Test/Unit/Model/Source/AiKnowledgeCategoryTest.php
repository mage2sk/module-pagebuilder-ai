<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model\Source;

use Panth\PageBuilderAi\Model\Source\AiKnowledgeCategory;
use PHPUnit\Framework\TestCase;

class AiKnowledgeCategoryTest extends TestCase
{
    public function testCategoriesMatchValuesAcceptedBySaveController(): void
    {
        $values = array_column((new AiKnowledgeCategory())->toOptionArray(), 'value');

        $this->assertSame(
            ['pagebuilder', 'seo', 'ecommerce', 'accessibility', 'html_patterns', 'response_format',
                'category_pages', 'cms_pages', 'conversion_copy', 'homepage_content', 'industry_specific',
                'product_descriptions', 'panth_infrastructure', 'panth_module', 'panth_modules'],
            $values
        );
    }

    public function testEveryBundledKnowledgeCategoryIsSelectable(): void
    {
        $values = array_column((new AiKnowledgeCategory())->toOptionArray(), 'value');
        $dataDir = dirname(__DIR__, 4) . '/Setup/Data';
        $bundled = [];
        foreach (glob($dataDir . '/*.php') as $file) {
            foreach ((array) include $file as $entry) {
                $bundled[(string) ($entry['category'] ?? '')] = true;
            }
        }

        $this->assertNotEmpty($bundled);
        $this->assertSame([], array_values(array_diff(array_keys($bundled), $values)));
    }
}
