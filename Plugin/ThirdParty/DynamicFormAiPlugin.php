<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Plugin\ThirdParty;

use Panth\PageBuilderAi\Model\Admin\AiButtonRenderer;

/**
 * Adds AI generate buttons to the DynamicForms Form edit page.
 *
 * Targets: Panth\DynamicForms\Ui\DataProvider\FormDataProvider (afterGetMeta).
 */
class DynamicFormAiPlugin
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
        if (!class_exists(\Panth\DynamicForms\Model\Form::class, false)
            && !class_exists(\Panth\DynamicForms\Model\Form::class)
        ) {
            return $result;
        }

        if (!$this->aiButtonRenderer->isAvailable()) {
            return $result;
        }

        $fieldMap = [
            'description'      => 'description',
            'content_above'    => 'content_above',
            'content_below'    => 'content_below',
            'success_message'  => 'success_message',
            'meta_title'       => 'meta_title',
            'meta_description' => 'meta_description',
            'meta_keywords'    => 'meta_keywords',
        ];

        $perFieldConfig = [
            'description' => [
                'label'  => 'Form Description',
                'field'  => 'description',
                'prompt' => 'Write a brief, user-friendly description for this form page. Explain what the form is for and encourage users to fill it out. 1-2 sentences.',
            ],
            'content_above' => [
                'label'  => 'Content Above Form',
                'field'  => 'content_above',
                'prompt' => 'Write introductory HTML content to display above the form. Include a welcoming message and brief instructions. Use <p> tags. Keep it concise and helpful.',
            ],
            'content_below' => [
                'label'  => 'Content Below Form',
                'field'  => 'content_below',
                'prompt' => 'Write HTML content to display below the form. Include a reassurance message about data privacy or expected response time. Use <p> tags.',
            ],
            'success_message' => [
                'label'  => 'Success Message',
                'field'  => 'success_message',
                'prompt' => 'Write a friendly, professional success message shown after form submission. Thank the user and set expectations for next steps. 1-2 sentences.',
            ],
            'meta_title' => [
                'label'  => 'Meta Title',
                'field'  => 'meta_title',
                'prompt' => 'Write an SEO-optimized meta title for this form page. Must be 50-60 characters.',
            ],
            'meta_description' => [
                'label'  => 'Meta Description',
                'field'  => 'meta_description',
                'prompt' => 'Write a compelling meta description for this form page. Must be 140-156 characters with a clear CTA.',
            ],
            'meta_keywords' => [
                'label'  => 'Meta Keywords',
                'field'  => 'meta_keywords',
                'prompt' => 'Generate 5-10 comma-separated SEO keywords relevant to this form page.',
            ],
        ];

        $result['general']['children']['ai_generate_container'] = $this->aiButtonRenderer->buildContainerMeta(
            'dynamic_form',
            'form_id',
            'store_id',
            $fieldMap,
            $perFieldConfig,
            'dynform',
            'The AI will use the saved form data to generate or improve content and SEO meta.',
            5
        );

        return $result;
    }
}
