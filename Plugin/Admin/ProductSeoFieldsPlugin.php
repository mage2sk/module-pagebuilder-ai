<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Plugin\Admin;

use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider;
use Magento\Framework\App\ResourceConnection;
use Panth\PageBuilderAi\Helper\Config as SeoConfig;
use Panth\PageBuilderAi\Model\Config\Source\MetaRobots;

/**
 * Adds meta_robots (select), custom_canonical_url (text), and
 * exclude_from_sitemap (toggle) fields to the product edit form's
 * "search-engine-optimization" fieldset.
 *
 * - meta_robots and in_xml_sitemap are EAV attributes, so Magento
 *   persists them automatically on save.
 * - custom_canonical_url is loaded from panth_seo_custom_canonical;
 *   saving is handled by {@see ProductSeoFieldsSavePlugin}.
 */
class ProductSeoFieldsPlugin
{
    private const CANONICAL_TABLE = 'panth_seo_custom_canonical';
    private const ENTITY_TYPE     = 'product';

    public function __construct(
        private readonly MetaRobots $metaRobotsSource,
        private readonly ResourceConnection $resource,
        private readonly SeoConfig $seoConfig,
        private readonly BackendUrl $backendUrl
    ) {
    }

    /**
     * Inject SEO fields into the product form meta.
     *
     * @param ProductDataProvider   $subject
     * @param array<string, mixed>  $result
     * @return array<string, mixed>
     */
    public function afterGetMeta(ProductDataProvider $subject, array $result): array
    {
        if (!$this->seoConfig->isEnabled()) {
            return $result;
        }

        // -- Meta Robots select --
        $result['search-engine-optimization']['children']['meta_robots'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'formElement'   => 'select',
                        'dataType'      => 'text',
                        'label'         => __('Meta Robots'),
                        'options'       => $this->metaRobotsSource->toOptionArray(),
                        'sortOrder'     => 30,
                        'dataScope'     => 'meta_robots',
                    ],
                ],
            ],
        ];

        // -- Custom Canonical URL text --
        $result['search-engine-optimization']['children']['custom_canonical_url'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'formElement'   => 'input',
                        'dataType'      => 'text',
                        'label'         => __('Custom Canonical URL'),
                        'notice'        => __('Leave empty to use auto-generated canonical'),
                        'sortOrder'     => 35,
                        'dataScope'     => 'custom_canonical_url',
                        'validation'    => [
                            'validate-url' => true,
                        ],
                    ],
                ],
            ],
        ];

        // -- OG Title --
        $result['search-engine-optimization']['children']['og_title'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'formElement'   => 'input',
                        'dataType'      => 'text',
                        'label'         => __('OG Title'),
                        'notice'        => __('Open Graph title for social sharing (Facebook, LinkedIn, etc.). Leave empty to use Meta Title.'),
                        'sortOrder'     => 50,
                        'dataScope'     => 'og_title',
                    ],
                ],
            ],
        ];

        // -- OG Description --
        $result['search-engine-optimization']['children']['og_description'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'formElement'   => 'textarea',
                        'dataType'      => 'text',
                        'label'         => __('OG Description'),
                        'notice'        => __('Open Graph description for social sharing. Leave empty to use Meta Description.'),
                        'sortOrder'     => 55,
                        'dataScope'     => 'og_description',
                    ],
                ],
            ],
        ];

        // -- OG Image URL --
        $result['search-engine-optimization']['children']['og_image'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'formElement'   => 'input',
                        'dataType'      => 'text',
                        'label'         => __('OG Image URL'),
                        'notice'        => __('Open Graph image URL for social sharing. Leave empty to use product image.'),
                        'sortOrder'     => 58,
                        'dataScope'     => 'og_image',
                    ],
                ],
            ],
        ];

        // -- Exclude from Sitemap toggle --
        $result['search-engine-optimization']['children']['exclude_from_sitemap'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'dataType'      => 'boolean',
                        'formElement'   => 'checkbox',
                        'componentType' => 'field',
                        'label'         => __('Exclude from XML Sitemap'),
                        'notice'        => __('Exclude this product from XML sitemap'),
                        'prefer'        => 'toggle',
                        'valueMap'      => [
                            'true'  => '0',
                            'false' => '1',
                        ],
                        'default'       => '1',
                        'dataScope'     => 'in_xml_sitemap',
                        'sortOrder'     => 40,
                        'switcherConfig' => [
                            'enabled' => false,
                        ],
                    ],
                ],
            ],
        ];

        // -- AI Generate Meta button --
        if ($this->seoConfig->isAiEnabled()) {
            $generateUrl = $this->backendUrl->getUrl('panth_seo/aigenerate/generate');
            $result['search-engine-optimization']['children']['ai_generate_container'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'container',
                            'component'     => 'Magento_Ui/js/form/components/html',
                            'content'       => $this->buildAiButtonHtml($generateUrl, 'product'),
                            'sortOrder'     => 5,
                            'additionalClasses' => 'panth-seo-ai-generate-wrapper',
                        ],
                    ],
                ],
            ];
        }

        return $result;
    }

    /**
     * Pre-fill custom_canonical_url from the database.
     *
     * @param ProductDataProvider   $subject
     * @param array<string, mixed>  $result
     * @return array<string, mixed>
     */
    public function afterGetData(ProductDataProvider $subject, array $result): array
    {
        if (!$this->seoConfig->isEnabled()) {
            return $result;
        }

        if (empty($result)) {
            return $result;
        }

        foreach ($result as $productId => &$productData) {
            if (!is_array($productData) || !isset($productData['product'])) {
                continue;
            }

            $entityId = (int) ($productData['product']['entity_id'] ?? $productId);
            $storeId  = (int) ($productData['product']['store_id'] ?? 0);

            $targetUrl = $this->loadCanonicalUrl(self::ENTITY_TYPE, $entityId, $storeId);
            if ($targetUrl !== null) {
                $productData['product']['custom_canonical_url'] = $targetUrl;
            }
        }

        return $result;
    }

    /**
     * Load saved prompts for dropdown.
     */
    private function loadPrompts(string $entityType): array
    {
        try {
            $conn = $this->resource->getConnection();
            $table = $this->resource->getTableName('panth_seo_ai_prompt');
            if (!$conn->isTableExists($table)) {
                return [];
            }
            return $conn->fetchAll(
                $conn->select()
                    ->from($table, ['prompt_id', 'name', 'prompt_template', 'is_default'])
                    ->where('is_active = 1')
                    ->where('entity_type IN (?)', [$entityType, 'all'])
                    ->order('is_default DESC')
                    ->order('sort_order ASC')
            );
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Build inline HTML + JS for the AI Generate button with prompt selector
     * and per-field AI generation buttons.
     */
    private function buildAiButtonHtml(string $generateUrl, string $entityType): string
    {
        $fieldMap = [
            'meta_title'        => 'product[meta_title]',
            'meta_description'  => 'product[meta_description]',
            'meta_keywords'     => 'product[meta_keyword]',
            'og_title'          => 'product[og_title]',
            'og_description'    => 'product[og_description]',
            'short_description' => 'product[short_description]',
        ];
        $fieldMapJson = json_encode($fieldMap, JSON_UNESCAPED_UNICODE);

        $prompts = $this->loadPrompts($entityType);
        $promptsJson = json_encode(array_map(function ($p) {
            return [
                'id' => (int) $p['prompt_id'],
                'name' => $p['name'],
                'template' => $p['prompt_template'],
                'is_default' => (int) $p['is_default'],
            ];
        }, $prompts), JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

        $perFieldConfig = [
            'product[name]'              => ['label' => 'Product Name',      'field' => 'name',              'prompt' => "Suggest a better, SEO-friendly product name for this item currently named '{{name}}' (SKU: {{sku}}). Keep it concise but descriptive."],
            'product[description]'       => ['label' => 'Description',       'field' => 'description',       'prompt' => "Write a detailed, SEO-optimized HTML product description for '{{name}}'. Include features, benefits, and use cases. Use <p>, <ul>, <li> tags. 2-4 paragraphs."],
            'product[short_description]' => ['label' => 'Short Description', 'field' => 'short_description', 'prompt' => "Write a compelling short product description for '{{name}}' (SKU: {{sku}}, Price: {{price}}). 1-3 sentences, max 250 characters. Highlight key benefits."],
            'product[meta_title]'        => ['label' => 'Meta Title',        'field' => 'meta_title',        'prompt' => "Write an SEO-optimized meta title for the product '{{name}}' (SKU: {{sku}}, Price: {{price}}). Must be 50-60 characters. Include the product name and a compelling modifier."],
            'product[meta_description]'  => ['label' => 'Meta Description',  'field' => 'meta_description',  'prompt' => "Write a compelling meta description for the product '{{name}}' (SKU: {{sku}}, Price: {{price}}). Must be 140-156 characters with a clear CTA."],
            'product[meta_keyword]'      => ['label' => 'Meta Keywords',     'field' => 'meta_keywords',     'prompt' => "Generate 5-10 comma-separated SEO keywords for the product '{{name}}' (SKU: {{sku}}, Category: {{category}})."],
            'product[og_title]'          => ['label' => 'OG Title',          'field' => 'og_title',          'prompt' => "Write an Open Graph title (60-90 characters) for social sharing of the product '{{name}}'."],
            'product[og_description]'    => ['label' => 'OG Description',    'field' => 'og_description',    'prompt' => "Write an Open Graph description (100-200 characters) for social media sharing of the product '{{name}}'."],
        ];
        $perFieldConfigJson = json_encode($perFieldConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

        return <<<HTML
<div style="margin:8px 0 12px;padding:12px 15px;background:#f0f7ff;border:1px solid #c8ddf4;border-radius:4px;">
    <strong style="font-size:14px;color:#1565C0;">AI Meta Generation</strong>
    <div style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#555;">Select Prompt:</label>
        <select id="panth-ai-prompt-select" onchange="panthSelectPrompt(this)" style="width:100%;max-width:400px;padding:4px 8px;margin-top:4px;">
            <option value="0">-- Write Custom Prompt --</option>
        </select>
    </div>
    <div style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#555;">Prompt (editable before generating):</label>
        <textarea id="panth-ai-prompt-text" rows="5" style="width:100%;margin-top:4px;padding:8px;font-size:12px;border:1px solid #ccc;border-radius:3px;font-family:monospace;" placeholder="Type your custom prompt here or select a saved one above..."></textarea>
        <div style="font-size:11px;color:#888;margin-top:2px;">Placeholders: {{name}}, {{sku}}, {{price}}, {{brand}}, {{category}}, {{short_description}}, {{description}}, {{store_name}}, {{url}}</div>
    </div>
    <div style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#555;">Upload Images (optional):</label>
        <input type="file" id="panth-ai-images" multiple accept="image/*"
               style="margin-top:4px;font-size:12px;"
               onchange="panthPreviewImages(this, 'panth-ai-image-preview')"/>
        <div id="panth-ai-image-preview" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px;"></div>
        <div style="font-size:11px;color:#888;margin-top:2px;">Upload product images for AI to analyze and generate better descriptions. Max 5 images.</div>
    </div>
    <div style="margin-top:10px;">
        <button type="button" id="panth-seo-ai-generate-btn"
            onclick="panthSeoAiGenerate(this)"
            style="background:#1979c3;color:#fff;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;font-size:13px;font-weight:600;">
            &#9733; Generate All Fields with AI
        </button>
        <span id="panth-seo-ai-status" style="margin-left:12px;font-size:12px;color:#666;"></span>
    </div>
    <div style="margin-top:8px;font-size:12px;color:#666;border-top:1px solid #d8e8f8;padding-top:8px;">
        Or use the <strong style="color:#1979c3;">AI</strong> buttons next to individual fields to generate one field at a time.
    </div>
</div>

<!-- Per-field AI popup (shared, created once) -->
<div id="panth-ai-field-backdrop" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);z-index:9999;" onclick="panthCloseFieldAiPopup()"></div>
<div id="panth-ai-field-popup" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:10000;background:#fff;border:2px solid #1979c3;border-radius:8px;padding:20px;width:600px;max-width:90vw;box-shadow:0 10px 40px rgba(0,0,0,0.3);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <strong id="panth-ai-field-popup-title" style="font-size:14px;color:#1565C0;">Generate: Field</strong>
        <button type="button" onclick="panthCloseFieldAiPopup()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#999;line-height:1;" title="Close">&times;</button>
    </div>
    <div>
        <label style="font-size:12px;font-weight:600;color:#555;">Prompt:</label>
        <textarea id="panth-ai-field-prompt" rows="4" style="width:100%;padding:8px;font-size:12px;border:1px solid #ccc;border-radius:3px;font-family:monospace;margin-top:4px;box-sizing:border-box;"></textarea>
    </div>
    <div style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#555;">Upload Images (optional):</label>
        <input type="file" id="panth-ai-field-images" multiple accept="image/*"
               style="margin-top:4px;font-size:12px;"
               onchange="panthPreviewImages(this, 'panth-ai-field-image-preview')"/>
        <div id="panth-ai-field-image-preview" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px;"></div>
    </div>
    <div style="margin-top:10px;display:flex;gap:10px;align-items:center;">
        <button type="button" id="panth-ai-field-generate-btn" onclick="panthGenerateField()" style="background:#1979c3;color:#fff;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;font-weight:600;">Generate</button>
        <button type="button" onclick="panthCloseFieldAiPopup()" style="background:#eee;color:#333;border:1px solid #ccc;padding:8px 20px;border-radius:4px;cursor:pointer;">Cancel</button>
        <span id="panth-ai-field-status" style="font-size:12px;color:#666;"></span>
    </div>
</div>

<script>
var panthAiPrompts = {$promptsJson};
var panthAiGenerateUrl = '{$generateUrl}';
var panthAiEntityType = '{$entityType}';
var panthAiFieldMap = {$fieldMapJson};
var panthAiPerFieldConfig = {$perFieldConfigJson};
var panthAiCurrentFieldInputName = '';
var panthAiCurrentFieldKey = '';

function panthPreviewImages(input, previewId) {
    var preview = document.getElementById(previewId);
    if (!preview) return;
    preview.innerHTML = '';
    var files = Array.from(input.files).slice(0, 5);
    files.forEach(function(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'width:60px;height:60px;object-fit:cover;border:1px solid #ccc;border-radius:4px;';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
    if (input.files.length > 5) {
        var note = document.createElement('span');
        note.textContent = 'Max 5 images. Only first 5 will be used.';
        note.style.cssText = 'font-size:11px;color:#c00;align-self:center;';
        preview.appendChild(note);
    }
}

function panthGetUploadedImages(inputId) {
    var input = document.getElementById(inputId);
    if (!input || !input.files.length) return Promise.resolve([]);
    var files = Array.from(input.files).slice(0, 5);
    var promises = files.map(function(file) {
        return new Promise(function(resolve) {
            if (file.size > 5 * 1024 * 1024) { resolve(null); return; }
            var reader = new FileReader();
            reader.onload = function(e) { resolve(e.target.result); };
            reader.onerror = function() { resolve(null); };
            reader.readAsDataURL(file);
        });
    });
    return Promise.all(promises).then(function(results) {
        return results.filter(function(r) { return r !== null; });
    });
}

function panthSendAiRequest(url, payload, images) {
    if (images && images.length > 0) {
        payload.images = images;
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });
    }
    return fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: new URLSearchParams(payload)
    });
}

(function(){
    var sel = document.getElementById('panth-ai-prompt-select');
    var ta = document.getElementById('panth-ai-prompt-text');
    if (sel && panthAiPrompts) {
        panthAiPrompts.forEach(function(p) {
            var opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.name + (p.is_default ? ' (default)' : '');
            sel.appendChild(opt);
            if (p.is_default && ta) { sel.value = p.id; ta.value = p.template; }
        });
    }
})();

function panthSelectPrompt(sel) {
    var ta = document.getElementById('panth-ai-prompt-text');
    if (!ta) return;
    if (sel.value === '0') { ta.value = ''; ta.focus(); return; }
    var found = panthAiPrompts.find(function(p) { return p.id == sel.value; });
    if (found) ta.value = found.template;
}

function panthGetEntityId() {
    var entityIdInput = document.querySelector('input[name="product[entity_id]"]')
        || document.querySelector('[name="id"]');
    var entityId = entityIdInput ? entityIdInput.value : 0;
    if (!entityId || entityId === '0') {
        var m = window.location.href.match(/\/id\/(\d+)/);
        if (m) entityId = m[1];
    }
    return entityId;
}

function panthGetStoreId() {
    var storeInput = document.querySelector('input[name="product[store_id]"]')
        || document.querySelector('[name="store_id"]')
        || document.querySelector('select[name="store_id"]');
    return storeInput ? storeInput.value : 0;
}

function panthReplacePlaceholders(text) {
    var replacements = {
        '{{name}}': 'product[name]',
        '{{sku}}': 'product[sku]',
        '{{price}}': 'product[price]',
        '{{short_description}}': 'product[short_description]',
        '{{description}}': 'product[description]',
        '{{category}}': null
    };
    Object.keys(replacements).forEach(function(placeholder) {
        var inputName = replacements[placeholder];
        var val = '';
        if (inputName) {
            var input = document.querySelector('[name="' + inputName + '"]');
            if (input) val = input.value || '';
        }
        text = text.split(placeholder).join(val || placeholder);
    });
    return text;
}

function panthSetFieldValue(inputName, value) {
    var input = document.querySelector('[name="' + inputName + '"]');
    if (input) {
        input.value = value;
        input.dispatchEvent(new Event('input', {bubbles: true}));
        input.dispatchEvent(new Event('change', {bubbles: true}));
        // Handle TinyMCE editors
        if (typeof tinyMCE !== 'undefined') {
            var editorId = input.id;
            if (editorId) {
                var editor = tinyMCE.get(editorId);
                if (editor) { editor.setContent(value); }
            }
        }
        return true;
    }
    return false;
}

/* --- Generate All Fields --- */
function panthSeoAiGenerate(btn) {
    var entityId = panthGetEntityId();
    var storeId = panthGetStoreId();
    var statusEl = document.getElementById('panth-seo-ai-status');

    if (!entityId || entityId === '0') {
        if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'Please save the product first.'; }
        return;
    }

    var promptText = document.getElementById('panth-ai-prompt-text');
    var promptSelect = document.getElementById('panth-ai-prompt-select');

    btn.disabled = true;
    btn.textContent = 'Generating...';
    if (statusEl) statusEl.textContent = 'Calling AI provider...';

    var payload = {
        form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
        entity_type: panthAiEntityType,
        entity_id: entityId,
        store_id: storeId
    };
    if (promptText && promptText.value.trim()) {
        payload.custom_prompt = promptText.value.trim();
    }
    if (promptSelect && promptSelect.value > 0) {
        payload.prompt_id = parseInt(promptSelect.value);
    }

    panthGetUploadedImages('panth-ai-images').then(function(images) {
        panthSendAiRequest(panthAiGenerateUrl, payload, images)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                var filled = [];
                Object.keys(data.data).forEach(function(fieldName) {
                    var inputName = panthAiFieldMap[fieldName] || fieldName;
                    var ok = panthSetFieldValue(inputName, data.data[fieldName]);
                    if (!ok) { ok = panthSetFieldValue('product[' + fieldName + ']', data.data[fieldName]); }
                    if (ok) filled.push(fieldName);
                });
                var msg = 'AI generated ' + filled.length + ' field(s)';
                if (data.provider) msg += ' via ' + data.provider;
                if (data.tokens_used) msg += ' (' + data.tokens_used + ' tokens)';
                msg += '. Review and save.';
                if (statusEl) { statusEl.style.color = '#006400'; statusEl.textContent = msg; }
            } else {
                if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'Failed: ' + (data.message || 'Unknown error'); }
            }
            btn.disabled = false;
            btn.innerHTML = '&#9733; Generate All Fields with AI';
        })
        .catch(function(e) {
            if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'Error: ' + e.message; }
            btn.disabled = false;
            btn.innerHTML = '&#9733; Generate All Fields with AI';
        });
    });
}

/* --- Per-field AI popup --- */
function panthOpenFieldAiPopup(inputName, config) {
    panthAiCurrentFieldInputName = inputName;
    panthAiCurrentFieldKey = config.field;
    var popup = document.getElementById('panth-ai-field-popup');
    var backdrop = document.getElementById('panth-ai-field-backdrop');
    var title = document.getElementById('panth-ai-field-popup-title');
    var prompt = document.getElementById('panth-ai-field-prompt');
    var status = document.getElementById('panth-ai-field-status');

    if (title) title.textContent = 'Generate: ' + config.label;
    if (prompt) prompt.value = panthReplacePlaceholders(config.prompt);
    if (status) { status.textContent = ''; status.style.color = '#666'; }
    if (popup) popup.style.display = 'block';
    if (backdrop) backdrop.style.display = 'block';
    if (prompt) prompt.focus();
}

function panthCloseFieldAiPopup() {
    var popup = document.getElementById('panth-ai-field-popup');
    var backdrop = document.getElementById('panth-ai-field-backdrop');
    if (popup) popup.style.display = 'none';
    if (backdrop) backdrop.style.display = 'none';
    panthAiCurrentFieldInputName = '';
    panthAiCurrentFieldKey = '';
}

function panthGenerateField() {
    var entityId = panthGetEntityId();
    var storeId = panthGetStoreId();
    var statusEl = document.getElementById('panth-ai-field-status');
    var generateBtn = document.getElementById('panth-ai-field-generate-btn');

    if (!entityId || entityId === '0') {
        if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'Please save the entity first.'; }
        return;
    }

    var promptText = document.getElementById('panth-ai-field-prompt');
    if (generateBtn) { generateBtn.disabled = true; generateBtn.textContent = 'Generating...'; }
    if (statusEl) { statusEl.style.color = '#666'; statusEl.textContent = 'Calling AI provider...'; }

    var payload = {
        form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
        entity_type: panthAiEntityType,
        entity_id: entityId,
        store_id: storeId,
        target_field: panthAiCurrentFieldKey,
        custom_prompt: promptText ? promptText.value.trim() : ''
    };

    panthGetUploadedImages('panth-ai-field-images').then(function(images) {
        panthSendAiRequest(panthAiGenerateUrl, payload, images)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                var value = data.data[panthAiCurrentFieldKey] || '';
                if (!value) {
                    var keys = Object.keys(data.data);
                    if (keys.length > 0) value = data.data[keys[0]];
                }
                if (value) {
                    panthSetFieldValue(panthAiCurrentFieldInputName, value);
                    var msg = 'Done';
                    if (data.provider) msg += ' via ' + data.provider;
                    if (data.tokens_used) msg += ' (' + data.tokens_used + ' tokens)';
                    if (statusEl) { statusEl.style.color = '#006400'; statusEl.textContent = msg; }
                    setTimeout(function() { panthCloseFieldAiPopup(); }, 1200);
                } else {
                    if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'AI returned empty result.'; }
                }
            } else {
                if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'Failed: ' + (data.message || 'Unknown error'); }
            }
            if (generateBtn) { generateBtn.disabled = false; generateBtn.textContent = 'Generate'; }
        })
        .catch(function(e) {
            if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'Error: ' + e.message; }
            if (generateBtn) { generateBtn.disabled = false; generateBtn.textContent = 'Generate'; }
        });
    });
}

/* Escape key closes popup */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') panthCloseFieldAiPopup();
});

/* --- Inject per-field AI buttons after UI components render --- */
setTimeout(function() {
    var fieldConfigs = panthAiPerFieldConfig;

    Object.keys(fieldConfigs).forEach(function(inputName) {
        var input = document.querySelector('[name="' + inputName + '"]');
        if (!input) return;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:3px;"><path d="M12 2l2.09 6.26L20 10l-5.91 1.74L12 18l-2.09-6.26L4 10l5.91-1.74z"/></svg>AI';
        btn.title = 'Generate ' + fieldConfigs[inputName].label + ' with AI';
        btn.style.cssText = 'margin-left:8px;background:#1979c3;color:#fff;border:none;padding:3px 10px;border-radius:3px;cursor:pointer;font-size:11px;vertical-align:middle;line-height:18px;';
        btn.onmouseover = function() { this.style.background = '#1565C0'; };
        btn.onmouseout = function() { this.style.background = '#1979c3'; };
        btn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            panthOpenFieldAiPopup(inputName, fieldConfigs[inputName]);
        };

        var container = input.closest('.admin__field-control') || input.closest('.admin__field') || input.parentNode;
        if (container) {
            container.appendChild(btn);
        }
    });
}, 3000);
</script>
HTML;
    }

    /**
     * Load the custom canonical URL from panth_seo_custom_canonical.
     */
    private function loadCanonicalUrl(string $entityType, int $entityId, int $storeId): ?string
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName(self::CANONICAL_TABLE);

        $select = $connection->select()
            ->from($table, ['target_url'])
            ->where('source_entity_type = ?', $entityType)
            ->where('source_entity_id = ?', $entityId)
            ->where('store_id IN (?)', [0, $storeId])
            ->order('store_id DESC')
            ->limit(1);

        $value = $connection->fetchOne($select);

        return $value !== false && $value !== '' ? (string) $value : null;
    }
}
