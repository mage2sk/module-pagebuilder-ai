<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\Generate;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\AiService;

class Index extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::generate';

    /** Hard cap on admin-supplied prompt length — 10 KB is plenty for any reasonable brief. */
    private const MAX_PROMPT_LENGTH = 10000;

    /** Max images per request (also enforced in AiService). */
    private const MAX_IMAGES = 5;

    /** Hard cap on a single base64 image payload (~3 MB decoded). */
    private const MAX_IMAGE_LENGTH = 4_000_000;

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly Config $config,
        private readonly AiService $aiService
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

        // Accept either JSON body or form-encoded params. Both paths are treated as untrusted admin input.
        $contentType = (string) $request->getHeader('Content-Type');
        if ($contentType !== '' && stripos($contentType, 'application/json') !== false) {
            $body = json_decode((string) $request->getContent(), true) ?: [];
        } else {
            $body = $request->getParams();
        }

        // Validate user prompt.
        $prompt = (string) ($body['custom_prompt'] ?? '');
        if ($prompt === '') {
            return $result->setData(['success' => false, 'message' => 'Prompt is required.']);
        }
        if (strlen($prompt) > self::MAX_PROMPT_LENGTH) {
            return $result->setData(['success' => false, 'message' => 'Prompt is too long (max ' . self::MAX_PROMPT_LENGTH . ' chars).']);
        }
        // Strip null bytes and control chars except \n, \r, \t — defense against binary injection into the LLM prompt.
        $prompt = preg_replace('/[^\P{C}\n\r\t]+/u', '', $prompt) ?? $prompt;

        // Validate images array.
        $images = $body['images'] ?? [];
        if (!is_array($images)) {
            $images = [];
        }
        $images = array_slice(array_filter($images, 'is_string'), 0, self::MAX_IMAGES);
        foreach ($images as $i => $img) {
            if (strlen($img) > self::MAX_IMAGE_LENGTH) {
                return $result->setData(['success' => false, 'message' => 'Image #' . ($i + 1) . ' exceeds size limit.']);
            }
            // Accept only base64 data URIs or http(s) URLs.
            if (!preg_match('~^(data:image/(?:png|jpe?g|gif|webp);base64,[A-Za-z0-9+/=]+|https?://[^\s"<>]+)$~i', $img)) {
                return $result->setData(['success' => false, 'message' => 'Image #' . ($i + 1) . ' is not a valid data URI or URL.']);
            }
        }

        // Prepend PageBuilder training context so the model emits Magento PageBuilder-compliant markup
        // (wrapped in data-content-type="row|column-group|column|heading|text|html|buttons|divider|image|video|banner|slider|map|block|products")
        // rather than raw HTML. The admin's own prompt is appended AFTER this contract so the model can't
        // override the output format through prompt injection.
        $systemPrompt = $this->getPageBuilderSystemPrompt();
        $fullPrompt = $systemPrompt . "\n\n=== USER REQUEST ===\n" . $prompt;

        $response = $this->aiService->generate($fullPrompt, $images);

        if ($response['success']) {
            return $result->setData([
                'success' => true,
                'data' => [
                    'content' => (string) $response['content'],
                    'description' => (string) $response['content'],
                ],
            ]);
        }

        return $result->setData([
            'success' => false,
            'message' => $response['message'] ?? 'AI generation failed.',
        ]);
    }

    /**
     * System prompt that teaches the LLM to output PageBuilder-compliant markup.
     *
     * Magento PageBuilder persists content as HTML with specific `data-content-type` /
     * `data-appearance` / `data-element` attributes. When the admin clicks the AI toolbar
     * button we set the generated HTML as the stage's underlying textarea value, so it MUST
     * follow this structure or PageBuilder will either ignore it (content invisible) or
     * fall back to a single "HTML Code" block (no drag-and-drop editability).
     */
    private function getPageBuilderSystemPrompt(): string
    {
        return <<<'PROMPT'
You are generating content for Magento 2 PageBuilder. Every fragment MUST use
Magento PageBuilder's HTML structure so the admin can continue editing the
output with drag-and-drop. Return ONLY the HTML — no explanations, no Markdown,
no <html>/<head>/<body> wrappers.

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
        <a href="/link" data-element="link" class="pagebuilder-button-primary">Label</a>
      </div>
    </div>

  Divider:
    <div data-content-type="divider" data-appearance="default" data-element="main">
      <hr data-element="line"/>
    </div>

  Image:
    <figure data-content-type="image" data-appearance="full-width" data-element="main">
      <img src="{{media-url}}" alt="descriptive alt" data-element="desktop_image"/>
    </figure>

  Arbitrary HTML (for complex layouts that don't map to the above):
    <div data-content-type="html" data-appearance="default" data-element="main">
      <!-- any custom HTML / style / class -->
    </div>

=== RULES ===

1. Start output with a <div data-content-type="row" ...> — never with raw
   body content.
2. Use semantic headings <h1>-<h6> wrapped in the heading content type.
3. Wrap every run of paragraphs / lists in a single text content type.
4. NEVER emit <script> tags, inline event handlers (onclick=, onerror=, etc.),
   external stylesheets, <iframe>, <form>, or <meta>. Any of those will be
   stripped server-side.
5. Keep content accessible: heading hierarchy, alt text on images,
   descriptive link labels.
6. All href values must be relative paths or https:// URLs. No javascript:,
   data:, vbscript:, or file: schemes.
7. For long-form content, break into multiple row blocks — each row is its
   own editable section in PageBuilder.

If the user's prompt cannot be fulfilled safely within these rules, output
a single row containing a text content type that politely explains why.
PROMPT;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
