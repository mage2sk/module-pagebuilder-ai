<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\Generate;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\AiService;
use Panth\PageBuilderAi\Model\HtmlSanitizer;

class Index extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::generate';

    private const MAX_PROMPT_LENGTH = 10000;

    private const MAX_IMAGES = 5;

    private const MAX_IMAGE_LENGTH = 4_000_000;

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly Config $config,
        private readonly AiService $aiService,
        private readonly ResourceConnection $resourceConnection,
        private readonly HtmlSanitizer $htmlSanitizer
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        if (!$this->config->isEnabled()) {
            return $result->setData(['success' => false, 'message' => 'PageBuilder AI is disabled.']);
        }

        if (!$this->config->hasOwnApiKey()) {
            return $result->setData([
                'success' => false,
                'message' => 'API key not configured. Go to Stores > Configuration > Panth Extensions > PageBuilder AI to set up your AI provider.',
            ]);
        }

        $request = $this->getRequest();

        $contentType = (string) $request->getHeader('Content-Type');
        if ($contentType !== '' && stripos($contentType, 'application/json') !== false) {
            $body = json_decode((string) $request->getContent(), true) ?: [];
        } else {
            $body = $request->getParams();
        }

        $prompt = (string) ($body['custom_prompt'] ?? '');
        if ($prompt === '') {
            return $result->setData(['success' => false, 'message' => 'Prompt is required.']);
        }
        if (strlen($prompt) > self::MAX_PROMPT_LENGTH) {
            return $result->setData(['success' => false, 'message' => 'Prompt is too long (max ' . self::MAX_PROMPT_LENGTH . ' chars).']);
        }

        $prompt = preg_replace('/[^\P{C}\n\r\t]+/u', '', $prompt) ?? $prompt;

        $images = $body['images'] ?? [];
        if (!is_array($images)) {
            $images = [];
        }
        $images = array_slice(array_filter($images, 'is_string'), 0, self::MAX_IMAGES);
        foreach ($images as $i => $img) {
            if (strlen($img) > self::MAX_IMAGE_LENGTH) {
                return $result->setData(['success' => false, 'message' => 'Image #' . ($i + 1) . ' exceeds size limit.']);
            }

            if (!preg_match('~^data:image/(?:png|jpe?g|gif|webp);base64,[A-Za-z0-9+/=]+$~i', $img)) {
                return $result->setData(['success' => false, 'message' => 'Image #' . ($i + 1) . ' is not a valid image data URI.']);
            }
        }

        $requestedFormat = strtolower((string) ($body['output_format'] ?? ''));
        if (!in_array($requestedFormat, ['pagebuilder_html', 'plain', 'json'], true)) {
            $requestedFormat = $this->promptExpectsJson($prompt) ? 'json' : 'pagebuilder_html';
        }

        $prompt = $this->resolvePlaceholders(
            $prompt,
            (string) ($body['entity_type'] ?? ''),
            (int) ($body['entity_id'] ?? 0),
            (int) ($body['store_id'] ?? 0)
        );

        $rawPrompt = !empty($body['raw_prompt']);
        if ($rawPrompt) {
            $fullPrompt = $prompt;
        } elseif ($requestedFormat === 'plain') {
            $fullPrompt = "You are filling a single admin form input. Output ONLY the bare plain-text "
                . "value for that field - no JSON, no HTML, no markdown, no surrounding quotes, "
                . "no labels, no explanation. If you cannot fulfil the request, output nothing. "
                . "No emojis.\n\n=== FIELD BRIEF ===\n" . $prompt;
        } elseif ($requestedFormat === 'json') {
            $fullPrompt = $prompt;
        } else {
            $systemPrompt = $this->getPageBuilderSystemPrompt();
            $fullPrompt = $systemPrompt . "\n\n=== USER REQUEST ===\n" . $prompt;
        }

        $logContext = [
            'entity_type'   => (string) ($body['entity_type'] ?? '') ?: null,
            'entity_id'     => (int) ($body['entity_id'] ?? 0) ?: null,
            'store_id'      => (int) ($body['store_id'] ?? 0),
            'target_field'  => (string) ($body['target_field'] ?? '') ?: null,
            'output_format' => $requestedFormat,
            'raw_prompt'    => $rawPrompt ? 1 : 0,
        ];

        $response = $this->aiService->generate($fullPrompt, $images, $logContext);

        if (!$response['success']) {
            return $result->setData([
                'success' => false,
                'message' => $response['message'] ?? 'AI generation failed.',
            ]);
        }

        $rawContent = (string) $response['content'];
        $content = $this->htmlSanitizer->sanitize($rawContent);

        if ($requestedFormat === 'json') {
            $parsed = $this->extractJson($rawContent);
            if (is_array($parsed)) {
                return $result->setData([
                    'success' => true,
                    'data' => $this->sanitizeValues($parsed) + ['content' => $content, 'description' => $content],
                ]);
            }
        }

        return $result->setData([
            'success' => true,
            'data' => [
                'content' => $content,
                'description' => $content,
            ],
        ]);
    }

    private function sanitizeValues(array $values): array
    {
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $values[$key] = $this->htmlSanitizer->sanitize($value);
            } elseif (is_array($value)) {
                $values[$key] = $this->sanitizeValues($value);
            }
        }
        return $values;
    }

    private function resolvePlaceholders(string $prompt, string $entityType, int $entityId, int $storeId): string
    {
        if (strpos($prompt, '{{') === false) {
            return $prompt;
        }

        $data = [
            'title'             => '',
            'identifier'        => '',
            'content'           => '',
            'name'              => '',
            'sku'               => '',
            'price'             => '',
            'brand'             => '',
            'category'          => '',
            'description'       => '',
            'short_description' => '',
            'store_name'        => '',
            'url'               => '',
        ];

        try {
            $connection = $this->resourceConnection->getConnection();

            if ($entityType === 'cms_page' && $entityId > 0) {
                $row = $connection->fetchRow(
                    $connection->select()
                        ->from($connection->getTableName('cms_page'),
                               ['title', 'identifier', 'content', 'meta_title', 'meta_description'])
                        ->where('page_id = ?', $entityId)
                        ->limit(1)
                );
                if (is_array($row)) {
                    $data['title'] = (string) ($row['title'] ?? '');
                    $data['identifier'] = (string) ($row['identifier'] ?? '');
                    $data['content'] = mb_substr(strip_tags((string) ($row['content'] ?? '')), 0, 2000);
                    $data['url'] = $data['identifier'];
                }
            } elseif ($entityType === 'product' && $entityId > 0) {
                $data = $this->fetchProductPlaceholders($connection, $entityId, $storeId) + $data;
            } elseif ($entityType === 'category' && $entityId > 0) {
                $data = $this->fetchCategoryPlaceholders($connection, $entityId, $storeId) + $data;
            }
        } catch (\Throwable) {
        }

        return $this->stripUnresolvedPlaceholders($prompt, $data);
    }

    private function fetchProductPlaceholders(\Magento\Framework\DB\Adapter\AdapterInterface $conn, int $entityId, int $storeId): array
    {
        $entity = $conn->fetchRow(
            $conn->select()
                ->from($conn->getTableName('catalog_product_entity'), ['entity_id', 'sku'])
                ->where('entity_id = ?', $entityId)
                ->limit(1)
        );
        if (!is_array($entity)) {
            return [];
        }

        $attrCodes = ['name', 'description', 'short_description', 'price', 'manufacturer'];
        $attrMap = $conn->fetchPairs(
            $conn->select()
                ->from($conn->getTableName('eav_attribute'), ['attribute_code', 'attribute_id'])
                ->where('entity_type_id = (SELECT entity_type_id FROM ' . $conn->quoteIdentifier($conn->getTableName('eav_entity_type')) . ' WHERE entity_type_code = ?)', 'catalog_product')
                ->where('attribute_code IN (?)', $attrCodes)
        );

        $tableByCode = [
            'name'              => 'catalog_product_entity_varchar',
            'description'       => 'catalog_product_entity_text',
            'short_description' => 'catalog_product_entity_text',
            'price'             => 'catalog_product_entity_decimal',
            'manufacturer'      => 'catalog_product_entity_int',
        ];

        $values = [];
        foreach ($tableByCode as $code => $tableAlias) {
            if (empty($attrMap[$code])) {
                continue;
            }
            $values[$code] = (string) $conn->fetchOne(
                $conn->select()
                    ->from($conn->getTableName($tableAlias), ['value'])
                    ->where('entity_id = ?', (int) $entity['entity_id'])
                    ->where('attribute_id = ?', (int) $attrMap[$code])
                    ->where('store_id IN (?)', [0, $storeId])
                    ->order('store_id DESC')
                    ->limit(1)
            );
        }

        $categoryName = '';
        try {
            $cat = $conn->fetchRow(
                $conn->select()
                    ->from(['cp' => $conn->getTableName('catalog_category_product')], [])
                    ->join(
                        ['v' => $conn->getTableName('catalog_category_entity_varchar')],
                        'v.entity_id = cp.category_id',
                        ['value']
                    )
                    ->join(
                        ['a' => $conn->getTableName('eav_attribute')],
                        'a.attribute_id = v.attribute_id AND a.attribute_code = ' . $conn->quote('name'),
                        []
                    )
                    ->where('cp.product_id = ?', (int) $entity['entity_id'])
                    ->where('v.store_id IN (?)', [0, $storeId])
                    ->where('cp.category_id > 2')
                    ->order('v.store_id DESC')
                    ->limit(1)
            );
            if (is_array($cat)) {
                $categoryName = (string) ($cat['value'] ?? '');
            }
        } catch (\Throwable) {
        }

        return [
            'name'              => $values['name'] ?? '',
            'sku'               => (string) ($entity['sku'] ?? ''),
            'price'             => isset($values['price']) ? rtrim(rtrim($values['price'], '0'), '.') : '',
            'brand'             => '',
            'category'          => $categoryName,
            'description'       => mb_substr(strip_tags((string) ($values['description'] ?? '')), 0, 2000),
            'short_description' => mb_substr(strip_tags((string) ($values['short_description'] ?? '')), 0, 2000),
            'title'             => $values['name'] ?? '',
            'identifier'        => (string) ($entity['sku'] ?? ''),
        ];
    }

    private function fetchCategoryPlaceholders(\Magento\Framework\DB\Adapter\AdapterInterface $conn, int $entityId, int $storeId): array
    {
        $entity = $conn->fetchRow(
            $conn->select()
                ->from($conn->getTableName('catalog_category_entity'), ['entity_id'])
                ->where('entity_id = ?', $entityId)
                ->limit(1)
        );
        if (!is_array($entity)) {
            return [];
        }

        $attrMap = $conn->fetchPairs(
            $conn->select()
                ->from($conn->getTableName('eav_attribute'), ['attribute_code', 'attribute_id'])
                ->where('entity_type_id = (SELECT entity_type_id FROM ' . $conn->quoteIdentifier($conn->getTableName('eav_entity_type')) . ' WHERE entity_type_code = ?)', 'catalog_category')
                ->where('attribute_code IN (?)', ['name', 'description', 'url_key'])
        );

        $tableByCode = [
            'name'        => 'catalog_category_entity_varchar',
            'description' => 'catalog_category_entity_text',
            'url_key'     => 'catalog_category_entity_varchar',
        ];

        $values = [];
        foreach ($tableByCode as $code => $tableAlias) {
            if (empty($attrMap[$code])) {
                continue;
            }
            $values[$code] = (string) $conn->fetchOne(
                $conn->select()
                    ->from($conn->getTableName($tableAlias), ['value'])
                    ->where('entity_id = ?', (int) $entity['entity_id'])
                    ->where('attribute_id = ?', (int) $attrMap[$code])
                    ->where('store_id IN (?)', [0, $storeId])
                    ->order('store_id DESC')
                    ->limit(1)
            );
        }

        $name = (string) ($values['name'] ?? '');
        return [
            'name'              => $name,
            'category'          => $name,
            'title'             => $name,
            'identifier'        => (string) ($values['url_key'] ?? ''),
            'url'               => (string) ($values['url_key'] ?? ''),
            'description'       => mb_substr(strip_tags((string) ($values['description'] ?? '')), 0, 2000),
            'content'           => mb_substr(strip_tags((string) ($values['description'] ?? '')), 0, 2000),
        ];
    }

    private function stripUnresolvedPlaceholders(string $prompt, array $data): string
    {
        foreach ($data as $key => $value) {
            $prompt = str_ireplace('{{' . $key . '}}', $value, $prompt);
        }

        return (string) preg_replace('/\{\{[^}]+\}\}/', '', $prompt);
    }

    private function promptExpectsJson(string $prompt): bool
    {
        $needles = [
            'meta_title',
            'meta_description',
            'meta_keyword',
            'Return ONLY valid JSON',
            'valid JSON',
            'return json',
            '"meta_title"',
            '"meta_description"',
        ];
        foreach ($needles as $needle) {
            if (stripos($prompt, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    private function extractJson(string $content): ?array
    {
        $trimmed = trim($content);

        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/is', $trimmed, $m)) {
            $trimmed = $m[1];
        }

        if (!str_starts_with($trimmed, '{')) {
            $start = strpos($trimmed, '{');
            $end   = strrpos($trimmed, '}');
            if ($start === false || $end === false || $end <= $start) {
                return null;
            }
            $trimmed = substr($trimmed, $start, $end - $start + 1);
        }
        $decoded = json_decode($trimmed, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function getPageBuilderSystemPrompt(): string
    {
        return <<<'PROMPT'
You are generating content for Magento 2 PageBuilder. Every fragment MUST use
Magento PageBuilder's HTML structure so the admin can continue editing the
output with drag-and-drop. Return ONLY the HTML - no explanations, no Markdown,
no <html>/<head>/<body> wrappers.

=== OUTPUT ORDER (CRITICAL) ===

Emit visible content (headings, paragraphs, buttons) BEFORE any decorative
styling. A page with only a <style> block and no content types is invalid
and will be rejected. Every response MUST start with a
<div data-content-type="row" ...> containing real, visible content types.
If you run out of room, truncating extra rows is fine - but the first row
with real content MUST be complete.

=== NO CUSTOM CSS ===

Do NOT output <style> tags. Do NOT define CSS classes. Do NOT use class
attributes beyond PageBuilder's own (`pagebuilder-button-primary`,
`pagebuilder-button-secondary`). Do NOT invent class names like
`.hero-section`, `.feature-card`, `.container`, etc. - PageBuilder does not
render them and the token budget is limited. The theme already styles
PageBuilder content types; your job is structure, not aesthetics.

If you need layout / spacing, use minimal inline `style="..."` attributes
ONLY on the attributes PageBuilder itself emits (e.g. the column's
`justify-content`, `display`, `flex-direction`). Do not add decorative
inline styles (gradients, box-shadows, custom colours).

=== PAGEBUILDER HTML CONTRACT ===

Top-level wrapper for every visible block:
  <div data-content-type="row" data-appearance="contained" data-element="main">
    <div data-content-type="column-group" data-grid-size="12" data-element="main">
      <div data-content-type="column" data-appearance="full-height"
           data-background-images="{}" data-element="main"
           style="justify-content: flex-start; display: flex; flex-direction: column;">
        <!-- inner content goes here -->
      </div>
    </div>
  </div>

Inside a column you may only use these content types (one per block):

  Heading:
    <h2 data-content-type="heading" data-element="main">Your text</h2>
    (use h1/h2/h3/h4/h5/h6 as appropriate)

  Paragraph / rich text:
    <div data-content-type="text" data-appearance="default" data-element="main">
      <p>Your paragraph with <strong>inline</strong> formatting.</p>
    </div>

  Button row (one or more buttons):
    <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main">
      <div data-content-type="button-item" data-appearance="default" data-element="main">
        <a href="/link" target="" data-link-type="default" data-element="link" class="pagebuilder-button-primary">
          <span data-element="link_text">Label</span>
        </a>
      </div>
    </div>
    (The <span data-element="link_text"> wrapper is REQUIRED - the storefront
    theme only renders button labels that are inside it. A raw <a>Label</a>
    prints empty pills.)

  Divider:
    <div data-content-type="divider" data-appearance="default" data-element="main">
      <hr data-element="line"/>
    </div>

  Image - DO NOT EMIT:
    Do NOT output <figure data-content-type="image"> blocks. You cannot
    invent a working media path (Magento resolves `{{media url=...}}` against
    real files in pub/media, and guessed filenames produce 404 images on
    the storefront). If the user wants an image, leave a placeholder text
    block (e.g. "[Add product image here]") so the admin can drop one in
    via PageBuilder's built-in image upload UI. Reference images attached
    to the request are CONTEXT for your description - never link to them.

  Arbitrary HTML (for complex layouts that don't map to the above):
    <div data-content-type="html" data-appearance="default" data-element="main">
      <!-- any custom HTML - still no <style> tags, still no custom classes -->
    </div>

=== RULES ===

1. Start output with a <div data-content-type="row" ...> - never with raw
   body content, and never with a <style> block.
2. Use semantic headings <h1>-<h6> wrapped in the heading content type.
3. Wrap every run of paragraphs / lists in a single text content type.
4. NEVER emit <style>, <script>, <iframe>, <form>, <meta>, <link>, or
   inline event handlers (onclick=, onerror=, etc.). Any of those will be
   stripped server-side AND waste your token budget.
5. The only `class` values permitted are `pagebuilder-button-primary` and
   `pagebuilder-button-secondary` (on the <a> inside a button-item). No
   other class attributes anywhere.
6. Every button label MUST be wrapped in
   `<span data-element="link_text">...</span>` - the frontend theme hides
   raw <a>text</a> content, leaving blank button shapes.
7. NEVER emit <img> tags or {{media url=...}} directives. You do not know
   which media files exist on this store; use a text placeholder instead
   and let the admin drop in a real image.
8. Keep content accessible: heading hierarchy, descriptive link labels.
9. All href values must be relative paths or https:// URLs. No javascript:,
   data:, vbscript:, or file: schemes.
10. For long-form content, break into multiple row blocks - each row is its
    own editable section in PageBuilder.

If the user's prompt cannot be fulfilled safely within these rules, output
a single row containing a text content type that politely explains why.
PROMPT;
    }
}
