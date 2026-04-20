<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AiKnowledgeCategory implements OptionSourceInterface
{
    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'pagebuilder',     'label' => __('PageBuilder')],
            ['value' => 'seo',             'label' => __('SEO')],
            ['value' => 'ecommerce',       'label' => __('E-Commerce')],
            ['value' => 'accessibility',   'label' => __('Accessibility')],
            ['value' => 'html_patterns',   'label' => __('HTML Patterns')],
            ['value' => 'response_format', 'label' => __('Response Format')],
        ];
    }
}
