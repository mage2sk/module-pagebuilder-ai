<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Plugin\Admin;

use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Catalog\Model\Category\DataProvider as CategoryDataProvider;
use Magento\Framework\App\ResourceConnection;
use Panth\PageBuilderAi\Helper\Config as SeoConfig;
use Panth\PageBuilderAi\Model\Config\Source\MetaRobots;

/**
 * Adds meta_robots (select) and exclude_from_sitemap (toggle) fields to the
 * category edit form's "search_engine_optimization" fieldset.
 *
 * Both fields map to EAV attributes (`meta_robots` and `in_xml_sitemap`),
 * so Magento persists them automatically on category save.
 */
class CategorySeoFieldsPlugin
{
    public function __construct(
        private readonly MetaRobots $metaRobotsSource,
        private readonly ResourceConnection $resource,
        private readonly SeoConfig $seoConfig,
        private readonly BackendUrl $backendUrl
    ) {
    }

    /**
     * Inject SEO fields into the category form meta.
     *
     * @param CategoryDataProvider  $subject
     * @param array<string, mixed>  $result
     * @return array<string, mixed>
     */
    public function afterGetMeta(CategoryDataProvider $subject, array $result): array
    {
        if (!$this->seoConfig->isEnabled()) {
            return $result;
        }

        /*
         * Magento category form uses "search_engine_optimization" (underscores)
         * while the product form uses "search-engine-optimization" (hyphens).
         */
        $seoGroupKey = 'search_engine_optimization';
        if (!isset($result[$seoGroupKey])) {
            $seoGroupKey = 'search-engine-optimization';
        }

        // -- Meta Robots select --
        $result[$seoGroupKey]['children']['meta_robots'] = [
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

        // -- OG Title --
        $result[$seoGroupKey]['children']['og_title'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'formElement'   => 'input',
                        'dataType'      => 'text',
                        'label'         => __('OG Title'),
                        'notice'        => __('Open Graph title for social sharing. Leave empty to use Meta Title.'),
                        'sortOrder'     => 50,
                        'dataScope'     => 'og_title',
                    ],
                ],
            ],
        ];

        // -- OG Description --
        $result[$seoGroupKey]['children']['og_description'] = [
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
        $result[$seoGroupKey]['children']['og_image'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'field',
                        'formElement'   => 'input',
                        'dataType'      => 'text',
                        'label'         => __('OG Image URL'),
                        'notice'        => __('Open Graph image URL for social sharing. Leave empty to use category image.'),
                        'sortOrder'     => 58,
                        'dataScope'     => 'og_image',
                    ],
                ],
            ],
        ];

        // -- Exclude from Sitemap toggle --
        $result[$seoGroupKey]['children']['exclude_from_sitemap'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'dataType'      => 'boolean',
                        'formElement'   => 'checkbox',
                        'componentType' => 'field',
                        'label'         => __('Exclude from XML Sitemap'),
                        'notice'        => __('Exclude this category from XML sitemap'),
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
        if ($this->seoConfig->isEnabled() && $this->seoConfig->hasOwnApiKey()) {
            $generateUrl = $this->backendUrl->getUrl('panth_pagebuilderai/generate/index');
            $result[$seoGroupKey]['children']['ai_generate_container'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'container',
                            'component'     => 'Magento_Ui/js/form/components/html',
                            'content'       => $this->buildAiButtonHtml($generateUrl, 'category'),
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
     * and per-field AI generation buttons (category form).
     */
    private function buildAiButtonHtml(string $generateUrl, string $entityType): string
    {
        $fieldMap = [
            'meta_title'       => 'meta_title',
            'meta_description' => 'meta_description',
            'meta_keywords'    => 'meta_keywords',
            'og_title'         => 'og_title',
            'og_description'   => 'og_description',
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
            'name'             => ['label' => 'Category Name',   'field' => 'name',             'prompt' => "Suggest a better, SEO-friendly category name for the category currently named '{{name}}'. Keep it concise but descriptive."],
            'meta_title'       => ['label' => 'Meta Title',      'field' => 'meta_title',       'prompt' => "Write an SEO-optimized meta title for the category '{{name}}'. Must be 50-60 characters. Include the category name and a compelling modifier."],
            'meta_description' => ['label' => 'Meta Description','field' => 'meta_description', 'prompt' => "Write a compelling meta description for the category '{{name}}'. Must be 140-156 characters with a clear CTA."],
            'meta_keywords'    => ['label' => 'Meta Keywords',   'field' => 'meta_keywords',    'prompt' => "Generate 5-10 comma-separated SEO keywords for the category '{{name}}'."],
            'og_title'         => ['label' => 'OG Title',        'field' => 'og_title',         'prompt' => "Write an Open Graph title (60-90 characters) for social sharing of the category '{{name}}'."],
            'og_description'   => ['label' => 'OG Description',  'field' => 'og_description',   'prompt' => "Write an Open Graph description (100-200 characters) for social media sharing of the category '{{name}}'."],
        ];
        $perFieldConfigJson = json_encode($perFieldConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

        return <<<HTML
<div style="margin:8px 0 12px;padding:12px 15px;background:#f0f7ff;border:1px solid #c8ddf4;border-radius:4px;">
    <strong style="font-size:14px;color:#1565C0;">AI Meta Generation</strong>
    <div style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#555;">Select Prompt:</label>
        <select id="panth-ai-prompt-select-cat" onchange="panthSelectPromptCategory(this)" style="width:100%;max-width:400px;padding:4px 8px;margin-top:4px;">
            <option value="0">-- Write Custom Prompt --</option>
        </select>
    </div>
    <div style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#555;">Prompt (editable before generating):</label>
        <textarea id="panth-ai-prompt-text-cat" rows="5" style="width:100%;margin-top:4px;padding:8px;font-size:12px;border:1px solid #ccc;border-radius:3px;font-family:monospace;" placeholder="Type your custom prompt here or select a saved one above..."></textarea>
        <div style="font-size:11px;color:#888;margin-top:2px;">Placeholders: {{name}}, {{category}}, {{parent_category}}, {{store_name}}, {{url}}, {{description}}</div>
    </div>
    <div style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#555;">Upload Images (optional):</label>
        <input type="file" id="panth-ai-images-cat" multiple accept="image/*"
               style="margin-top:4px;font-size:12px;"
               onchange="panthPreviewImagesCat(this, 'panth-ai-image-preview-cat')"/>
        <div id="panth-ai-image-preview-cat" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px;"></div>
        <div style="font-size:11px;color:#888;margin-top:2px;">Upload category images for AI to analyze and generate better descriptions. Max 5 images.</div>
    </div>
    <div style="margin-top:10px;">
        <button type="button" id="panth-seo-ai-generate-btn-cat"
            onclick="panthSeoAiGenerateCategory(this)"
            style="background:#1979c3;color:#fff;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;font-size:13px;font-weight:600;">
            &#9733; Generate All Fields with AI
        </button>
        <span id="panth-seo-ai-status-cat" style="margin-left:12px;font-size:12px;color:#666;"></span>
    </div>
    <div style="margin-top:8px;font-size:12px;color:#666;border-top:1px solid #d8e8f8;padding-top:8px;">
        Or use the <strong style="color:#1979c3;">AI</strong> buttons next to individual fields to generate one field at a time.
    </div>
</div>

<!-- Per-field AI popup (category) -->
<div id="panth-ai-field-backdrop-cat" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);z-index:9999;" onclick="panthCloseFieldAiPopupCat()"></div>
<div id="panth-ai-field-popup-cat" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:10000;background:#fff;border:2px solid #1979c3;border-radius:8px;padding:20px;width:600px;max-width:90vw;box-shadow:0 10px 40px rgba(0,0,0,0.3);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <strong id="panth-ai-field-popup-title-cat" style="font-size:14px;color:#1565C0;">Generate: Field</strong>
        <button type="button" onclick="panthCloseFieldAiPopupCat()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#999;line-height:1;" title="Close">&times;</button>
    </div>
    <div>
        <label style="font-size:12px;font-weight:600;color:#555;">Prompt:</label>
        <textarea id="panth-ai-field-prompt-cat" rows="4" style="width:100%;padding:8px;font-size:12px;border:1px solid #ccc;border-radius:3px;font-family:monospace;margin-top:4px;box-sizing:border-box;"></textarea>
    </div>
    <div style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#555;">Upload Images (optional):</label>
        <input type="file" id="panth-ai-field-images-cat" multiple accept="image/*"
               style="margin-top:4px;font-size:12px;"
               onchange="panthPreviewImagesCat(this, 'panth-ai-field-image-preview-cat')"/>
        <div id="panth-ai-field-image-preview-cat" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px;"></div>
    </div>
    <div style="margin-top:10px;display:flex;gap:10px;align-items:center;">
        <button type="button" id="panth-ai-field-generate-btn-cat" onclick="panthGenerateFieldCat()" style="background:#1979c3;color:#fff;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;font-weight:600;">Generate</button>
        <button type="button" onclick="panthCloseFieldAiPopupCat()" style="background:#eee;color:#333;border:1px solid #ccc;padding:8px 20px;border-radius:4px;cursor:pointer;">Cancel</button>
        <span id="panth-ai-field-status-cat" style="font-size:12px;color:#666;"></span>
    </div>
</div>

<script>
var panthAiPromptsCat = {$promptsJson};
var panthAiGenerateUrlCat = '{$generateUrl}';
var panthAiEntityTypeCat = '{$entityType}';
var panthAiFieldMapCat = {$fieldMapJson};
var panthAiPerFieldConfigCat = {$perFieldConfigJson};
var panthAiCurrentFieldInputNameCat = '';
var panthAiCurrentFieldKeyCat = '';

function panthPreviewImagesCat(input, previewId) {
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

function panthGetUploadedImagesCat(inputId) {
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

function panthSendAiRequestCat(url, payload, images) {
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
    var sel = document.getElementById('panth-ai-prompt-select-cat');
    var ta = document.getElementById('panth-ai-prompt-text-cat');
    if (sel && panthAiPromptsCat) {
        panthAiPromptsCat.forEach(function(p) {
            var opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.name + (p.is_default ? ' (default)' : '');
            sel.appendChild(opt);
            if (p.is_default && ta) { sel.value = p.id; ta.value = p.template; }
        });
    }
})();

function panthSelectPromptCategory(sel) {
    var ta = document.getElementById('panth-ai-prompt-text-cat');
    if (!ta) return;
    if (sel.value === '0') { ta.value = ''; ta.focus(); return; }
    var found = panthAiPromptsCat.find(function(p) { return p.id == sel.value; });
    if (found) ta.value = found.template;
}

function panthGetCategoryEntityId() {
    var entityId = 0;
    var idInput = document.querySelector('input[name="entity_id"]')
        || document.querySelector('input[name="id"]')
        || document.querySelector('input[name="general[entity_id]"]');
    if (idInput) entityId = idInput.value;
    if (!entityId || entityId === '0') {
        var m = window.location.href.match(/\/id\/(\d+)/);
        if (m) entityId = m[1];
    }
    if (!entityId || entityId === '0') {
        var heading = document.querySelector('.page-title .base');
        if (heading) { var hm = heading.textContent.match(/ID:\s*(\d+)/); if (hm) entityId = hm[1]; }
    }
    return entityId;
}

function panthGetCategoryStoreId() {
    var storeInput = document.querySelector('input[name="store_id"]')
        || document.querySelector('select[name="store_id"]');
    return storeInput ? storeInput.value : 0;
}

function panthReplacePlaceholdersCat(text) {
    var nameInput = document.querySelector('[name="name"]') || document.querySelector('[name="general[name]"]');
    var nameVal = nameInput ? nameInput.value : '';
    text = text.split('{{name}}').join(nameVal || '{{name}}');
    return text;
}

function panthSetFieldValueCat(inputName, value) {
    var input = document.querySelector('[name="' + inputName + '"]');
    if (input) {
        input.value = value;
        input.dispatchEvent(new Event('input', {bubbles: true}));
        input.dispatchEvent(new Event('change', {bubbles: true}));
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

/* --- Generate All Fields (Category) --- */
function panthSeoAiGenerateCategory(btn) {
    var entityId = panthGetCategoryEntityId();
    var storeId = panthGetCategoryStoreId();
    var statusEl = document.getElementById('panth-seo-ai-status-cat');

    if (!entityId || entityId === '0') {
        if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'Please save the category first.'; }
        return;
    }

    var promptText = document.getElementById('panth-ai-prompt-text-cat');
    var promptSelect = document.getElementById('panth-ai-prompt-select-cat');

    btn.disabled = true;
    btn.textContent = 'Generating...';
    if (statusEl) statusEl.textContent = 'Calling AI provider...';

    var payload = {
        form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
        entity_type: panthAiEntityTypeCat,
        entity_id: entityId,
        store_id: storeId,
        output_format: 'json'
    };
    if (promptText && promptText.value.trim()) {
        payload.custom_prompt = promptText.value.trim();
    }
    if (promptSelect && promptSelect.value > 0) {
        payload.prompt_id = parseInt(promptSelect.value);
    }

    panthGetUploadedImagesCat('panth-ai-images-cat').then(function(images) {
        panthSendAiRequestCat(panthAiGenerateUrlCat, payload, images)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                var filled = [];
                Object.keys(data.data).forEach(function(fieldName) {
                    var inputName = panthAiFieldMapCat[fieldName] || fieldName;
                    if (panthSetFieldValueCat(inputName, data.data[fieldName])) filled.push(fieldName);
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

/* --- Per-field AI popup (Category) --- */
function panthOpenFieldAiPopupCat(inputName, config) {
    panthAiCurrentFieldInputNameCat = inputName;
    panthAiCurrentFieldKeyCat = config.field;
    var popup = document.getElementById('panth-ai-field-popup-cat');
    var backdrop = document.getElementById('panth-ai-field-backdrop-cat');
    var title = document.getElementById('panth-ai-field-popup-title-cat');
    var prompt = document.getElementById('panth-ai-field-prompt-cat');
    var status = document.getElementById('panth-ai-field-status-cat');

    if (title) title.textContent = 'Generate: ' + config.label;
    if (prompt) prompt.value = panthReplacePlaceholdersCat(config.prompt);
    if (status) { status.textContent = ''; status.style.color = '#666'; }
    if (popup) popup.style.display = 'block';
    if (backdrop) backdrop.style.display = 'block';
    if (prompt) prompt.focus();
}

function panthCloseFieldAiPopupCat() {
    var popup = document.getElementById('panth-ai-field-popup-cat');
    var backdrop = document.getElementById('panth-ai-field-backdrop-cat');
    if (popup) popup.style.display = 'none';
    if (backdrop) backdrop.style.display = 'none';
    panthAiCurrentFieldInputNameCat = '';
    panthAiCurrentFieldKeyCat = '';
}

function panthGenerateFieldCat() {
    var entityId = panthGetCategoryEntityId();
    var storeId = panthGetCategoryStoreId();
    var statusEl = document.getElementById('panth-ai-field-status-cat');
    var generateBtn = document.getElementById('panth-ai-field-generate-btn-cat');

    if (!entityId || entityId === '0') {
        if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'Please save the category first.'; }
        return;
    }

    var promptText = document.getElementById('panth-ai-field-prompt-cat');
    if (generateBtn) { generateBtn.disabled = true; generateBtn.textContent = 'Generating...'; }
    if (statusEl) { statusEl.style.color = '#666'; statusEl.textContent = 'Calling AI provider...'; }

    var payload = {
        form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
        entity_type: panthAiEntityTypeCat,
        entity_id: entityId,
        store_id: storeId,
        target_field: panthAiCurrentFieldKeyCat,
        custom_prompt: promptText ? promptText.value.trim() : '',
        output_format: 'plain'
    };

    panthGetUploadedImagesCat('panth-ai-field-images-cat').then(function(images) {
        panthSendAiRequestCat(panthAiGenerateUrlCat, payload, images)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                var value = data.data[panthAiCurrentFieldKeyCat] || '';
                if (!value) {
                    var keys = Object.keys(data.data);
                    if (keys.length > 0) value = data.data[keys[0]];
                }
                if (value) {
                    panthSetFieldValueCat(panthAiCurrentFieldInputNameCat, value);
                    var msg = 'Done';
                    if (data.provider) msg += ' via ' + data.provider;
                    if (data.tokens_used) msg += ' (' + data.tokens_used + ' tokens)';
                    if (statusEl) { statusEl.style.color = '#006400'; statusEl.textContent = msg; }
                    setTimeout(function() { panthCloseFieldAiPopupCat(); }, 1200);
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
    if (e.key === 'Escape') panthCloseFieldAiPopupCat();
});

/* --- Inject per-field AI buttons after UI components render --- */
setTimeout(function() {
    var fieldConfigs = panthAiPerFieldConfigCat;

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
            panthOpenFieldAiPopupCat(inputName, fieldConfigs[inputName]);
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
}
