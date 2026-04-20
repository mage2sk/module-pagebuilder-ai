<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Plugin\Admin;

use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Cms\Model\Page\DataProvider as CmsPageDataProvider;
use Magento\Framework\App\ResourceConnection;
use Panth\PageBuilderAi\Helper\Config as SeoConfig;
use Panth\PageBuilderAi\Model\Config\Source\MetaRobots;

/**
 * Adds meta_robots (select) and hreflang_identifier (text) fields to the
 * CMS page edit form.
 *
 * CMS pages do not use EAV, so both values are stored in the
 * `panth_seo_override` table keyed by entity_type = 'cms_page'.
 */
class CmsPageSeoFieldsPlugin
{
    private const OVERRIDE_TABLE = 'panth_seo_override';
    private const ENTITY_TYPE    = 'cms_page';

    public function __construct(
        private readonly MetaRobots $metaRobotsSource,
        private readonly ResourceConnection $resource,
        private readonly SeoConfig $seoConfig,
        private readonly BackendUrl $backendUrl
    ) {
    }

    /**
     * Inject SEO fields into the CMS page form meta.
     *
     * @param CmsPageDataProvider   $subject
     * @param array<string, mixed>  $result
     * @return array<string, mixed>
     */
    public function afterGetMeta(CmsPageDataProvider $subject, array $result): array
    {
        if (!$this->seoConfig->isEnabled()) {
            return $result;
        }

        $result['search_engine_optimisation'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Search Engine Optimization'),
                        'componentType' => 'fieldset',
                        'collapsible'   => true,
                        'sortOrder'     => 40,
                    ],
                ],
            ],
            'children' => [
                'meta_robots' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'field',
                                'formElement'   => 'select',
                                'dataType'      => 'text',
                                'label'         => __('Meta Robots'),
                                'options'       => $this->metaRobotsSource->toOptionArray(),
                                'sortOrder'     => 10,
                                'dataScope'     => 'meta_robots',
                            ],
                        ],
                    ],
                ],
                'hreflang_identifier' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'field',
                                'formElement'   => 'input',
                                'dataType'      => 'text',
                                'label'         => __('Hreflang Identifier'),
                                'notice'        => __(
                                    'Use the same identifier across store views to link '
                                    . 'this CMS page for hreflang tag generation '
                                    . '(e.g. "about-us").'
                                ),
                                'sortOrder'     => 20,
                                'dataScope'     => 'hreflang_identifier',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // -- AI Generate Content button (in Content section) --
        if ($this->seoConfig->isAiEnabled()) {
            $generateUrl = $this->backendUrl->getUrl('panth_seo/aigenerate/generate');

            // Add AI button to Content section for page content generation
            if (isset($result['content'])) {
                $result['content']['children']['ai_content_container'] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'container',
                                'component'     => 'Magento_Ui/js/form/components/html',
                                'content'       => '<div style="margin:8px 0;padding:8px 12px;background:#f0f7ff;border:1px solid #c8ddf4;border-radius:4px;">'
                                    . '<button type="button" onclick="panthOpenContentAiPopup()" style="background:#1979c3;color:#fff;border:none;padding:6px 16px;border-radius:4px;cursor:pointer;font-size:12px;font-weight:600;">'
                                    . '&#9733; Generate Page Content with AI</button>'
                                    . '<span id="panth-ai-content-status" style="margin-left:10px;font-size:12px;color:#666;"></span>'
                                    . '</div>',
                                'sortOrder'     => 1,
                            ],
                        ],
                    ],
                ];
            }

            // AI Generate Meta button (in SEO section)
            $result['search_engine_optimisation']['children']['ai_generate_container'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'container',
                            'component'     => 'Magento_Ui/js/form/components/html',
                            'content'       => $this->buildAiButtonHtml($generateUrl, 'cms_page'),
                            'sortOrder'     => 1,
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
     * and per-field AI generation buttons (CMS page form).
     */
    private function buildAiButtonHtml(string $generateUrl, string $entityType): string
    {
        $fieldMap = [
            'meta_title'       => 'meta_title',
            'meta_description' => 'meta_description',
            'meta_keywords'    => 'meta_keywords',
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
            'meta_title'       => ['label' => 'Meta Title',       'field' => 'meta_title',       'prompt' => "Write an SEO-optimized meta title for the CMS page '{{title}}' (URL key: {{identifier}}). Must be 50-60 characters."],
            'meta_description' => ['label' => 'Meta Description', 'field' => 'meta_description', 'prompt' => "Write a compelling meta description for the CMS page '{{title}}'. Must be 140-156 characters with a clear CTA."],
            'meta_keywords'    => ['label' => 'Meta Keywords',    'field' => 'meta_keywords',    'prompt' => "Generate 5-10 comma-separated SEO keywords for the CMS page '{{title}}' (URL key: {{identifier}})."],
            'content_heading'  => ['label' => 'Content Heading',  'field' => 'content_heading',  'prompt' => "Write an engaging, SEO-friendly content heading for the CMS page '{{title}}'. Keep it concise and compelling."],
        ];
        $perFieldConfigJson = json_encode($perFieldConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

        return <<<HTML
<div style="margin:8px 0 12px;padding:12px 15px;background:#f0f7ff;border:1px solid #c8ddf4;border-radius:4px;">
    <strong style="font-size:14px;color:#1565C0;">AI Meta Generation</strong>
    <div style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#555;">Select Prompt:</label>
        <select id="panth-ai-prompt-select-cms" onchange="panthSelectPromptCmsPage(this)" style="width:100%;max-width:400px;padding:4px 8px;margin-top:4px;">
            <option value="0">-- Write Custom Prompt --</option>
        </select>
    </div>
    <div style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#555;">Prompt (editable before generating):</label>
        <textarea id="panth-ai-prompt-text-cms" rows="5" style="width:100%;margin-top:4px;padding:8px;font-size:12px;border:1px solid #ccc;border-radius:3px;font-family:monospace;" placeholder="Type your custom prompt here or select a saved one above..."></textarea>
        <div style="font-size:11px;color:#888;margin-top:2px;">Placeholders: {{title}}, {{identifier}}, {{content}}, {{store_name}}, {{url}}</div>
    </div>
    <div style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#555;">Upload Images (optional):</label>
        <input type="file" id="panth-ai-images-cms" multiple accept="image/*"
               style="margin-top:4px;font-size:12px;"
               onchange="panthPreviewImagesCms(this, 'panth-ai-image-preview-cms')"/>
        <div id="panth-ai-image-preview-cms" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px;"></div>
        <div style="font-size:11px;color:#888;margin-top:2px;">Upload page images for AI to analyze and generate better descriptions. Max 5 images.</div>
    </div>
    <div style="margin-top:10px;">
        <button type="button" id="panth-seo-ai-generate-btn-cms"
            onclick="panthSeoAiGenerateCmsPage(this)"
            style="background:#1979c3;color:#fff;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;font-size:13px;font-weight:600;">
            &#9733; Generate All Fields with AI
        </button>
        <span id="panth-seo-ai-status-cms" style="margin-left:12px;font-size:12px;color:#666;"></span>
    </div>
    <div style="margin-top:8px;font-size:12px;color:#666;border-top:1px solid #d8e8f8;padding-top:8px;">
        Or use the <strong style="color:#1979c3;">AI</strong> buttons next to individual fields to generate one field at a time.
    </div>
</div>

<!-- Per-field AI popup (CMS page) -->
<div id="panth-ai-field-backdrop-cms" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);z-index:9999;" onclick="panthCloseFieldAiPopupCms()"></div>
<div id="panth-ai-field-popup-cms" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:10000;background:#fff;border:2px solid #1979c3;border-radius:8px;padding:20px;width:600px;max-width:90vw;box-shadow:0 10px 40px rgba(0,0,0,0.3);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <strong id="panth-ai-field-popup-title-cms" style="font-size:14px;color:#1565C0;">Generate: Field</strong>
        <button type="button" onclick="panthCloseFieldAiPopupCms()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#999;line-height:1;" title="Close">&times;</button>
    </div>
    <div>
        <label style="font-size:12px;font-weight:600;color:#555;">Prompt:</label>
        <textarea id="panth-ai-field-prompt-cms" rows="4" style="width:100%;padding:8px;font-size:12px;border:1px solid #ccc;border-radius:3px;font-family:monospace;margin-top:4px;box-sizing:border-box;"></textarea>
    </div>
    <div style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#555;">Upload Images (optional):</label>
        <input type="file" id="panth-ai-field-images-cms" multiple accept="image/*"
               style="margin-top:4px;font-size:12px;"
               onchange="panthPreviewImagesCms(this, 'panth-ai-field-image-preview-cms')"/>
        <div id="panth-ai-field-image-preview-cms" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px;"></div>
    </div>
    <div style="margin-top:10px;display:flex;gap:10px;align-items:center;">
        <button type="button" id="panth-ai-field-generate-btn-cms" onclick="panthGenerateFieldCms()" style="background:#1979c3;color:#fff;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;font-weight:600;">Generate</button>
        <button type="button" onclick="panthCloseFieldAiPopupCms()" style="background:#eee;color:#333;border:1px solid #ccc;padding:8px 20px;border-radius:4px;cursor:pointer;">Cancel</button>
        <span id="panth-ai-field-status-cms" style="font-size:12px;color:#666;"></span>
    </div>
</div>

<script>
var panthAiPromptsCms = {$promptsJson};
var panthAiGenerateUrlCms = '{$generateUrl}';
var panthAiEntityTypeCms = '{$entityType}';
var panthAiFieldMapCms = {$fieldMapJson};
var panthAiPerFieldConfigCms = {$perFieldConfigJson};
var panthAiCurrentFieldInputNameCms = '';
var panthAiCurrentFieldKeyCms = '';

function panthPreviewImagesCms(input, previewId) {
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

function panthGetUploadedImagesCms(inputId) {
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

function panthSendAiRequestCms(url, payload, images) {
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
    var sel = document.getElementById('panth-ai-prompt-select-cms');
    var ta = document.getElementById('panth-ai-prompt-text-cms');
    if (sel && panthAiPromptsCms) {
        panthAiPromptsCms.forEach(function(p) {
            var opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.name + (p.is_default ? ' (default)' : '');
            sel.appendChild(opt);
            if (p.is_default && ta) { sel.value = p.id; ta.value = p.template; }
        });
    }
})();

function panthSelectPromptCmsPage(sel) {
    var ta = document.getElementById('panth-ai-prompt-text-cms');
    if (!ta) return;
    if (sel.value === '0') { ta.value = ''; ta.focus(); return; }
    var found = panthAiPromptsCms.find(function(p) { return p.id == sel.value; });
    if (found) ta.value = found.template;
}

function panthGetCmsEntityId() {
    var entityIdInput = document.querySelector('input[name="page_id"]');
    var entityId = entityIdInput ? entityIdInput.value : 0;
    if (!entityId || entityId === '0') {
        var m = window.location.href.match(/\/page_id\/(\d+)/);
        if (m) entityId = m[1];
    }
    return entityId;
}

function panthGetCmsStoreId() {
    var storeInput = document.querySelector('select[name="store_id"]')
        || document.querySelector('input[name="store_id"]');
    if (!storeInput) return 0;
    if (storeInput.multiple) {
        return storeInput.options[storeInput.selectedIndex] ? storeInput.options[storeInput.selectedIndex].value : 0;
    }
    return storeInput.value;
}

function panthReplacePlaceholdersCms(text) {
    var titleInput = document.querySelector('[name="title"]');
    var identifierInput = document.querySelector('[name="identifier"]');
    text = text.split('{{title}}').join(titleInput ? (titleInput.value || '{{title}}') : '{{title}}');
    text = text.split('{{identifier}}').join(identifierInput ? (identifierInput.value || '{{identifier}}') : '{{identifier}}');
    return text;
}

function panthSetFieldValueCms(inputName, value) {
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

/* --- Generate All Fields (CMS Page) --- */
function panthSeoAiGenerateCmsPage(btn) {
    var entityId = panthGetCmsEntityId();
    var storeId = panthGetCmsStoreId();
    var statusEl = document.getElementById('panth-seo-ai-status-cms');

    if (!entityId || entityId === '0') {
        if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'Please save the CMS page first.'; }
        return;
    }

    var promptText = document.getElementById('panth-ai-prompt-text-cms');
    var promptSelect = document.getElementById('panth-ai-prompt-select-cms');

    btn.disabled = true;
    btn.textContent = 'Generating...';
    if (statusEl) statusEl.textContent = 'Calling AI provider...';

    var payload = {
        form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
        entity_type: panthAiEntityTypeCms,
        entity_id: entityId,
        store_id: storeId
    };
    if (promptText && promptText.value.trim()) {
        payload.custom_prompt = promptText.value.trim();
    }
    if (promptSelect && promptSelect.value > 0) {
        payload.prompt_id = parseInt(promptSelect.value);
    }

    panthGetUploadedImagesCms('panth-ai-images-cms').then(function(images) {
        panthSendAiRequestCms(panthAiGenerateUrlCms, payload, images)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                var filled = [];
                Object.keys(data.data).forEach(function(fieldName) {
                    var inputName = panthAiFieldMapCms[fieldName] || fieldName;
                    if (panthSetFieldValueCms(inputName, data.data[fieldName])) filled.push(fieldName);
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

/* --- Per-field AI popup (CMS Page) --- */
function panthOpenFieldAiPopupCms(inputName, config) {
    panthAiCurrentFieldInputNameCms = inputName;
    panthAiCurrentFieldKeyCms = config.field;
    var popup = document.getElementById('panth-ai-field-popup-cms');
    var backdrop = document.getElementById('panth-ai-field-backdrop-cms');
    var title = document.getElementById('panth-ai-field-popup-title-cms');
    var prompt = document.getElementById('panth-ai-field-prompt-cms');
    var status = document.getElementById('panth-ai-field-status-cms');

    if (title) title.textContent = 'Generate: ' + config.label;
    if (prompt) prompt.value = panthReplacePlaceholdersCms(config.prompt);
    if (status) { status.textContent = ''; status.style.color = '#666'; }
    if (popup) popup.style.display = 'block';
    if (backdrop) backdrop.style.display = 'block';
    if (prompt) prompt.focus();
}

function panthCloseFieldAiPopupCms() {
    var popup = document.getElementById('panth-ai-field-popup-cms');
    var backdrop = document.getElementById('panth-ai-field-backdrop-cms');
    if (popup) popup.style.display = 'none';
    if (backdrop) backdrop.style.display = 'none';
    panthAiCurrentFieldInputNameCms = '';
    panthAiCurrentFieldKeyCms = '';
}

function panthGenerateFieldCms() {
    var entityId = panthGetCmsEntityId();
    var storeId = panthGetCmsStoreId();
    var statusEl = document.getElementById('panth-ai-field-status-cms');
    var generateBtn = document.getElementById('panth-ai-field-generate-btn-cms');

    if (!entityId || entityId === '0') {
        if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'Please save the CMS page first.'; }
        return;
    }

    var promptText = document.getElementById('panth-ai-field-prompt-cms');
    if (generateBtn) { generateBtn.disabled = true; generateBtn.textContent = 'Generating...'; }
    if (statusEl) { statusEl.style.color = '#666'; statusEl.textContent = 'Calling AI provider...'; }

    var payload = {
        form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
        entity_type: panthAiEntityTypeCms,
        entity_id: entityId,
        store_id: storeId,
        target_field: panthAiCurrentFieldKeyCms,
        custom_prompt: promptText ? promptText.value.trim() : ''
    };

    panthGetUploadedImagesCms('panth-ai-field-images-cms').then(function(images) {
        panthSendAiRequestCms(panthAiGenerateUrlCms, payload, images)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                var value = data.data[panthAiCurrentFieldKeyCms] || '';
                if (!value) {
                    var keys = Object.keys(data.data);
                    if (keys.length > 0) value = data.data[keys[0]];
                }
                if (value) {
                    panthSetFieldValueCms(panthAiCurrentFieldInputNameCms, value);
                    var msg = 'Done';
                    if (data.provider) msg += ' via ' + data.provider;
                    if (data.tokens_used) msg += ' (' + data.tokens_used + ' tokens)';
                    if (statusEl) { statusEl.style.color = '#006400'; statusEl.textContent = msg; }
                    setTimeout(function() { panthCloseFieldAiPopupCms(); }, 1200);
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
    if (e.key === 'Escape') panthCloseFieldAiPopupCms();
});

/* --- Inject per-field AI buttons after UI components render --- */
setTimeout(function() {
    var fieldConfigs = panthAiPerFieldConfigCms;

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
            panthOpenFieldAiPopupCms(inputName, fieldConfigs[inputName]);
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
     * Pre-fill meta_robots and hreflang_identifier from the override table.
     *
     * @param CmsPageDataProvider   $subject
     * @param array<string, mixed>  $result
     * @return array<string, mixed>
     */
    public function afterGetData(CmsPageDataProvider $subject, array $result): array
    {
        if (!$this->seoConfig->isEnabled()) {
            return $result;
        }

        if (empty($result)) {
            return $result;
        }

        foreach ($result as $pageId => &$pageData) {
            if (!is_array($pageData)) {
                continue;
            }

            $entityId = (int) ($pageData['page_id'] ?? $pageId);
            $storeId  = 0;
            if (isset($pageData['store_id'])) {
                $stores  = is_array($pageData['store_id']) ? $pageData['store_id'] : [$pageData['store_id']];
                $storeId = (int) reset($stores);
            }

            $override = $this->loadOverride($entityId, $storeId);
            if ($override === null) {
                continue;
            }

            if (!empty($override['robots'])) {
                $pageData['meta_robots'] = $override['robots'];
            }
            if (!empty($override['hreflang_identifier'])) {
                $pageData['hreflang_identifier'] = $override['hreflang_identifier'];
            }
        }

        return $result;
    }

    /**
     * Load an existing override row for the CMS page.
     *
     * @return array<string, mixed>|null
     */
    private function loadOverride(int $entityId, int $storeId): ?array
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName(self::OVERRIDE_TABLE);

        $select = $connection->select()
            ->from($table, ['robots', 'hreflang_identifier'])
            ->where('entity_type = ?', self::ENTITY_TYPE)
            ->where('entity_id = ?', $entityId)
            ->where('store_id IN (?)', [0, $storeId])
            ->order('store_id DESC')
            ->limit(1);

        $row = $connection->fetchRow($select);

        return $row !== false ? $row : null;
    }
}
