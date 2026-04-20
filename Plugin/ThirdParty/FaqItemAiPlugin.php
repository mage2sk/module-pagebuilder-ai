<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Plugin\ThirdParty;

use Panth\PageBuilderAi\Model\Admin\AiButtonRenderer;

/**
 * Adds AI generate buttons to the FAQ Item edit form.
 *
 * Targets: Panth\Faq\Model\Item\DataProvider (afterGetMeta).
 */
class FaqItemAiPlugin
{
    public function __construct(
        private readonly AiButtonRenderer $aiButtonRenderer
    ) {
    }

    /**
     * @param mixed               $subject
     * @param array<string,mixed> $result
     * @return array<string,mixed>
     */
    public function afterGetMeta($subject, array $result): array
    {
        if (!class_exists(\Panth\Faq\Model\Item::class, false)
            && !class_exists(\Panth\Faq\Model\Item::class)
        ) {
            return $result;
        }

        if (!$this->aiButtonRenderer->isAvailable()) {
            return $result;
        }

        $fieldMap = [
            'answer'           => 'answer',
            'meta_title'       => 'meta_title',
            'meta_description' => 'meta_description',
            'meta_keywords'    => 'meta_keywords',
        ];

        $perFieldConfig = [
            'answer' => [
                'label'  => 'FAQ Answer',
                'field'  => 'answer',
                'prompt' => 'Write a detailed, helpful FAQ answer for the question. Use clear language, be informative, and format with HTML paragraphs. Keep it concise but thorough.',
            ],
            'meta_title' => [
                'label'  => 'Meta Title',
                'field'  => 'meta_title',
                'prompt' => 'Write an SEO-optimized meta title for this FAQ item. Must be 50-60 characters. Include the main topic of the question.',
            ],
            'meta_description' => [
                'label'  => 'Meta Description',
                'field'  => 'meta_description',
                'prompt' => 'Write a compelling meta description for this FAQ item. Must be 140-156 characters with a clear summary of the answer.',
            ],
            'meta_keywords' => [
                'label'  => 'Meta Keywords',
                'field'  => 'meta_keywords',
                'prompt' => 'Generate 5-10 comma-separated SEO keywords relevant to this FAQ question and answer.',
            ],
        ];

        $result['general']['children']['ai_generate_container'] = $this->aiButtonRenderer->buildContainerMeta(
            'faq',
            'item_id',
            'store_id',
            $fieldMap,
            $perFieldConfig,
            'faq',
            'Placeholders: The AI will use the saved FAQ question and answer to generate content.',
            5
        );

        return $result;
    }
}
