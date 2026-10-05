<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AiKnowledgeCategory implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'pagebuilder',     'label' => __('PageBuilder')],
            ['value' => 'seo',             'label' => __('SEO')],
            ['value' => 'ecommerce',       'label' => __('E-Commerce')],
            ['value' => 'accessibility',   'label' => __('Accessibility')],
            ['value' => 'html_patterns',   'label' => __('HTML Patterns')],
            ['value' => 'response_format', 'label' => __('Response Format')],
            ['value' => 'category_pages', 'label' => __('Category Pages')],
            ['value' => 'cms_pages', 'label' => __('CMS Pages')],
            ['value' => 'conversion_copy', 'label' => __('Conversion Copy')],
            ['value' => 'homepage_content', 'label' => __('Homepage Content')],
            ['value' => 'industry_specific', 'label' => __('Industry Specific')],
            ['value' => 'product_descriptions', 'label' => __('Product Descriptions')],
            ['value' => 'panth_infrastructure', 'label' => __('Panth Infrastructure')],
            ['value' => 'panth_module', 'label' => __('Panth Module')],
            ['value' => 'panth_modules', 'label' => __('Panth Modules')],
        ];
    }
}
