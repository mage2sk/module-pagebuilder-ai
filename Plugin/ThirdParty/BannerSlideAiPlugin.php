<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Plugin\ThirdParty;

use Panth\PageBuilderAi\Model\Admin\AiButtonRenderer;

/**
 * Adds AI generate buttons to the BannerSlider Slide edit form.
 *
 * Targets: Panth\BannerSlider\Ui\DataProvider\SlideFormDataProvider (afterGetMeta).
 * SAFE: declaration is conditional on the target class existing; Magento
 * silently skips plugin declarations for missing target classes, and the
 * class_exists() check inside is a further safety net.
 */
class BannerSlideAiPlugin
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
        if (!class_exists(\Panth\BannerSlider\Model\Slide::class, false)
            && !class_exists(\Panth\BannerSlider\Model\Slide::class)
        ) {
            return $result;
        }

        if (!$this->aiButtonRenderer->isAvailable()) {
            return $result;
        }

        $fieldMap = [
            'title'        => 'title',
            'content_html' => 'content_html',
            'alt_text'     => 'alt_text',
        ];

        $perFieldConfig = [
            'title' => [
                'label'  => 'Slide Title',
                'field'  => 'title',
                'prompt' => 'Write a compelling, attention-grabbing banner slide title. Keep it concise (under 60 characters), impactful, and suitable for a hero banner.',
            ],
            'content_html' => [
                'label'  => 'Content Overlay HTML',
                'field'  => 'content_html',
                'prompt' => 'Write HTML content for a banner slide overlay. Include a headline and a short call-to-action paragraph. Use <h2> for the headline and <p> for the body. Keep it brief and visually impactful.',
            ],
            'alt_text' => [
                'label'  => 'Image Alt Text',
                'field'  => 'alt_text',
                'prompt' => 'Write a descriptive, SEO-friendly alt text for this banner slide image. Keep it under 125 characters. Describe what the image shows.',
            ],
        ];

        $result['content']['children']['ai_generate_container'] = $this->aiButtonRenderer->buildContainerMeta(
            'banner',
            'slide_id',
            'store_id',
            $fieldMap,
            $perFieldConfig,
            'banner',
            'The AI will use the saved slide data to generate content.',
            5
        );

        return $result;
    }
}
