<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Plugin\ThirdParty;

use Panth\PageBuilderAi\Model\Admin\AiButtonRenderer;

/**
 * Adds AI generate buttons to the Testimonial edit form.
 *
 * Targets: Panth\Testimonials\Model\Testimonial\DataProvider (afterGetMeta).
 */
class TestimonialAiPlugin
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
        if (!class_exists(\Panth\Testimonials\Model\Testimonial::class, false)
            && !class_exists(\Panth\Testimonials\Model\Testimonial::class)
        ) {
            return $result;
        }

        if (!$this->aiButtonRenderer->isAvailable()) {
            return $result;
        }

        $fieldMap = [
            'content'       => 'content',
            'short_content' => 'short_content',
            'title'         => 'title',
        ];

        $perFieldConfig = [
            'content' => [
                'label'  => 'Testimonial Content',
                'field'  => 'content',
                'prompt' => "Write a polished, authentic-sounding testimonial based on the existing content. Keep the customer's voice and sentiment. 2-4 sentences, professional but personal.",
            ],
            'short_content' => [
                'label'  => 'Short Excerpt',
                'field'  => 'short_content',
                'prompt' => 'Write a short testimonial excerpt suitable for a card display. 1-2 sentences, max 150 characters. Capture the key sentiment.',
            ],
            'title' => [
                'label'  => 'Testimonial Title',
                'field'  => 'title',
                'prompt' => 'Write a compelling, concise testimonial headline/title that captures the key sentiment. Keep it under 60 characters.',
            ],
        ];

        $result['general']['children']['ai_generate_container'] = $this->aiButtonRenderer->buildContainerMeta(
            'testimonial',
            'testimonial_id',
            '',
            $fieldMap,
            $perFieldConfig,
            'testimonial',
            'The AI will use the saved testimonial data to generate or improve content.',
            5
        );

        return $result;
    }
}
