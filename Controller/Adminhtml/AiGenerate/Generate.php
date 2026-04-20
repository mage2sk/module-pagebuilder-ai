<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\AiGenerate;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Panth\PageBuilderAi\Api\AiGeneratorInterface;
use Panth\PageBuilderAi\Model\Score\ContextBuilder;
use Psr\Log\LoggerInterface;

/**
 * AJAX controller for on-page AI meta / content generation.
 *
 * Ported from Panth\AdvancedSEO\Controller\Adminhtml\AiGenerate\Generate.
 * Enforces the new Panth_PageBuilderAi::ai_generate ACL resource.
 */
class Generate extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::ai_generate';

    /** Default fields to generate per entity type */
    private const DEFAULT_FIELDS = [
        'product'      => ['meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description', 'short_description'],
        'category'     => ['meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description'],
        'cms_page'     => ['meta_title', 'meta_description', 'meta_keywords'],
        'faq'          => ['answer', 'meta_title', 'meta_description', 'meta_keywords'],
        'testimonial'  => ['content', 'short_content', 'title'],
        'banner'       => ['title', 'content_html', 'alt_text'],
        'dynamic_form' => ['description', 'content_above', 'content_below', 'success_message', 'meta_title', 'meta_description', 'meta_keywords'],
    ];

    /** Field-specific prompt suffixes for single-field generation */
    private const FIELD_PROMPT_SUFFIX = [
        'meta_title'        => 'Generate ONLY a meta title (50-60 characters) optimized for search engines.',
        'meta_description'  => 'Generate ONLY a meta description (140-156 characters) with compelling CTA.',
        'meta_keywords'     => 'Generate ONLY 5-10 comma-separated SEO keywords.',
        'og_title'          => 'Generate ONLY an Open Graph title (60-90 characters) for social sharing.',
        'og_description'    => 'Generate ONLY an Open Graph description (100-200 characters) for social media.',
        'short_description' => 'Generate ONLY a short product description (1-3 sentences, max 250 characters).',
        'description'       => 'Generate ONLY a detailed description (2-4 paragraphs, SEO-optimized, HTML formatted with <p> tags).',
        'name'              => 'Generate ONLY an SEO-optimized name (concise, includes key features).',
        'answer'            => 'Generate ONLY a detailed, helpful FAQ answer. Use clear language, HTML formatted with <p> tags.',
        'content'           => 'Generate ONLY polished content text. 2-4 sentences, professional and engaging.',
        'short_content'     => 'Generate ONLY a short excerpt (1-2 sentences, max 150 characters).',
        'title'             => 'Generate ONLY a compelling, concise title (under 60 characters).',
        'content_html'      => 'Generate ONLY HTML content with a headline (<h2>) and short body paragraph (<p>). Keep it brief and impactful.',
        'alt_text'          => 'Generate ONLY descriptive, SEO-friendly image alt text (under 125 characters).',
        'content_above'     => 'Generate ONLY introductory HTML content with <p> tags. Welcoming and informative.',
        'content_below'     => 'Generate ONLY closing HTML content with <p> tags. Include a reassurance or next-steps message.',
        'success_message'   => 'Generate ONLY a friendly success message for form submission (1-2 sentences).',
    ];

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly ContextBuilder $contextBuilder,
        private readonly AiGeneratorInterface $generator,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        if (!$this->getRequest()->isPost()) {
            return $result->setData([
                'success' => false,
                'message' => 'POST request required.',
            ]);
        }

        $params = $this->getRequest()->getParams();
        $postParams = $this->getRequest()->getPostValue();
        if (is_array($postParams)) {
            $params = array_merge($params, $postParams);
        }
        $rawBody = (string)$this->getRequest()->getContent();
        if ($rawBody !== '' && $rawBody[0] === '{') {
            $jsonParams = json_decode($rawBody, true);
            if (is_array($jsonParams)) {
                $params = array_merge($params, $jsonParams);
            }
        }

        $entityType    = (string)($params['entity_type'] ?? '');
        $entityId      = (int)($params['entity_id'] ?? 0);
        $storeId       = (int)($params['store_id'] ?? 0);
        $promptId      = (int)($params['prompt_id'] ?? 0);
        $customPrompt  = (string)($params['custom_prompt'] ?? '');
        $fields        = $params['fields'] ?? null;
        $targetField   = (string)($params['target_field'] ?? '');
        $images        = $params['images'] ?? [];

        if (!is_array($images)) {
            $images = [];
        }
        $images = array_slice(array_filter($images, 'is_string'), 0, 5);

        $allowedTypes = [
            'product', 'category', 'cms_page',
            'faq', 'testimonial', 'banner', 'dynamic_form',
        ];
        if (!in_array($entityType, $allowedTypes, true)) {
            return $result->setData([
                'success' => false,
                'message' => 'Invalid entity_type. Allowed: ' . implode(', ', $allowedTypes),
            ]);
        }

        if ($entityId <= 0) {
            return $result->setData([
                'success' => false,
                'message' => 'Entity must be saved before generating AI meta. Please save first.',
            ]);
        }

        $allKnownFields = [
            'meta_title', 'meta_description', 'meta_keywords',
            'og_title', 'og_description', 'short_description',
            'description', 'name', 'content_heading',
            'answer', 'content', 'short_content', 'title',
            'content_html', 'alt_text',
            'content_above', 'content_below', 'success_message',
        ];

        if ($targetField !== '' && in_array($targetField, $allKnownFields, true)) {
            $fields = [$targetField];
            $suffix = self::FIELD_PROMPT_SUFFIX[$targetField] ?? '';
            if ($suffix !== '') {
                $customPrompt = trim($customPrompt . "\n\n" . $suffix);
            }
        } elseif (is_array($fields) && !empty($fields)) {
            $fields = array_values(array_intersect($fields, $allKnownFields));
        }

        if (empty($fields)) {
            $fields = self::DEFAULT_FIELDS[$entityType] ?? self::DEFAULT_FIELDS['product'];
        }

        try {
            $context = $this->contextBuilder->build($entityType, $entityId, $storeId);

            if ($customPrompt !== '') {
                $context['custom_prompt'] = $customPrompt;
            } elseif ($promptId > 0) {
                $context['prompt_id'] = $promptId;
            }

            $context['fields'] = $fields;

            if (!empty($images)) {
                $context['images'] = $images;
            }

            $generated = $this->generator->generate($context, $fields);
            $tokensUsed = $this->generator->getLastUsageTokens();
            $provider   = $this->generator->getProvider();

            $data = [];
            foreach ($fields as $field) {
                if (isset($generated[$field]) && $generated[$field] !== '') {
                    $data[$field] = $generated[$field];
                }
            }

            if (empty($data['meta_title']) && !empty($generated['title'])) {
                $data['meta_title'] = $generated['title'];
            }
            if (empty($data['meta_description']) && !empty($generated['description'])) {
                $data['meta_description'] = $generated['description'];
            }

            if (empty($data)) {
                return $result->setData([
                    'success' => false,
                    'message' => 'AI generation returned empty results. Check AI provider configuration.',
                ]);
            }

            return $result->setData([
                'success'     => true,
                'data'        => $data,
                'tokens_used' => $tokensUsed,
                'provider'    => $provider,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Panth PageBuilderAi Generate controller error: ' . $e->getMessage(), [
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'exception'   => $e,
            ]);

            $safeMessage = 'An unexpected error occurred. Please check the system logs.';
            $msg = $e->getMessage();
            if (str_contains($msg, 'API key') || str_contains($msg, 'budget')
                || str_contains($msg, 'rate limit') || str_contains($msg, 'quota')
                || str_contains($msg, 'configuration')
            ) {
                $safeMessage = $msg;
            }

            return $result->setData([
                'success' => false,
                'message' => $safeMessage,
            ]);
        }
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(static::ADMIN_RESOURCE);
    }
}
