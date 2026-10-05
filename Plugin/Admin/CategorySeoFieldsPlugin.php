<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Plugin\Admin;

use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Catalog\Model\Category\DataProvider as CategoryDataProvider;
use Magento\Framework\App\ResourceConnection;
use Panth\PageBuilderAi\Helper\Config as SeoConfig;
use Panth\PageBuilderAi\Model\Config\Source\MetaRobots;

class CategorySeoFieldsPlugin
{
    public function __construct(
        private readonly MetaRobots $metaRobotsSource,
        private readonly ResourceConnection $resource,
        private readonly SeoConfig $seoConfig,
        private readonly BackendUrl $backendUrl
    ) {
    }

    public function afterGetMeta(CategoryDataProvider $subject, array $result): array
    {
        if (!$this->seoConfig->isEnabled()) {
            return $result;
        }

        $seoGroupKey = 'search_engine_optimization';
        if (!isset($result[$seoGroupKey])) {
            $seoGroupKey = 'search-engine-optimization';
        }

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
<div class="panth-ai-panel">
    <strong class="panth-ai-panel-title">AI Meta Generation</strong>
    <div class="panth-ai-block">
        <label class="panth-ai-label">Select Prompt:</label>
        <select id="panth-ai-prompt-select-cat" onchange="panthSelectPromptCategory(this)" class="admin__control-select panth-ai-select">
            <option value="0">-- Write Custom Prompt --</option>
        </select>
    </div>
    <div class="panth-ai-block">
        <label class="panth-ai-label">Prompt (editable before generating):</label>
        <textarea id="panth-ai-prompt-text-cat" rows="5" class="admin__control-textarea panth-ai-textarea" placeholder="Type your custom prompt here or select a saved one above..."></textarea>
        <div class="panth-ai-note">Placeholders: {{name}}, {{category}}, {{parent_category}}, {{store_name}}, {{url}}, {{description}}</div>
    </div>
    <div class="panth-ai-block">
        <label class="panth-ai-label">Upload Images (optional):</label>
        <input type="file" id="panth-ai-images-cat" multiple accept="image/*"
               class="panth-ai-file"
               onchange="panthPreviewImagesCat(this, 'panth-ai-image-preview-cat')"/>
        <div id="panth-ai-image-preview-cat" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px;"></div>
        <div class="panth-ai-note">Upload category images for AI to analyze and generate better descriptions. Max 5 images.</div>
    </div>
    <div class="panth-ai-block panth-ai-actions">
        <button type="button" id="panth-seo-ai-generate-btn-cat"
            onclick="panthSeoAiGenerateCategory(this)"
            class="action-secondary panth-ai-generate">
            &#9733; Generate All Fields with AI
        </button>
        <span id="panth-seo-ai-status-cat" class="panth-ai-status"></span>
    </div>
    <div class="panth-ai-foot">
        Or use the <strong class="panth-ai-em">AI</strong> buttons next to individual fields to generate one field at a time.
    </div>
</div>

<!-- Per-field AI popup (category) -->
<div id="panth-ai-field-backdrop-cat" class="panth-ai-backdrop" style="display:none;" onclick="panthCloseFieldAiPopupCat()"></div>
<div id="panth-ai-field-popup-cat" class="panth-ai-popup" style="display:none;">
    <div class="panth-ai-popup-head">
        <strong id="panth-ai-field-popup-title-cat" class="panth-ai-panel-title">Generate: Field</strong>
        <button type="button" onclick="panthCloseFieldAiPopupCat()" class="panth-ai-close" title="Close">&times;</button>
    </div>
    <div>
        <label class="panth-ai-label">Prompt:</label>
        <textarea id="panth-ai-field-prompt-cat" rows="4" class="admin__control-textarea panth-ai-textarea"></textarea>
    </div>
    <div class="panth-ai-block">
        <label class="panth-ai-label">Upload Images (optional):</label>
        <input type="file" id="panth-ai-field-images-cat" multiple accept="image/*"
               class="panth-ai-file"
               onchange="panthPreviewImagesCat(this, 'panth-ai-field-image-preview-cat')"/>
        <div id="panth-ai-field-image-preview-cat" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px;"></div>
    </div>
    <div class="panth-ai-popup-actions">
        <button type="button" id="panth-ai-field-generate-btn-cat" onclick="panthGenerateFieldCat()" class="action-primary">Generate</button>
        <button type="button" onclick="panthCloseFieldAiPopupCat()" class="panth-ai-cancel">Cancel</button>
        <span id="panth-ai-field-status-cat" class="panth-ai-status"></span>
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
        note.style.cssText = 'font-size:12px;color:#c00;align-self:center;';
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
    url = url + (url.indexOf('?') > -1 ? '&' : '?') + 'form_key=' + encodeURIComponent(payload.form_key || '');
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
    if (status) { status.textContent = ''; status.style.color = '#595959'; }
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
    if (statusEl) { statusEl.style.color = '#595959'; statusEl.textContent = 'Calling AI provider...'; }

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
        btn.className = 'panth-ai-field-btn';
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
