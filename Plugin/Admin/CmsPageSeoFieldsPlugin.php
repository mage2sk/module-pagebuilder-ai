<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Plugin\Admin;

use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Cms\Model\Page\DataProvider as CmsPageDataProvider;
use Magento\Framework\App\ResourceConnection;
use Panth\PageBuilderAi\Helper\Config as SeoConfig;
use Panth\PageBuilderAi\Model\Config\Source\MetaRobots;

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

        if (!$this->isTableAvailable(self::OVERRIDE_TABLE)) {
            unset(
                $result['search_engine_optimisation']['children']['meta_robots'],
                $result['search_engine_optimisation']['children']['hreflang_identifier']
            );
        }

        if ($this->seoConfig->isEnabled() && $this->seoConfig->hasOwnApiKey()) {
            $generateUrl = $this->backendUrl->getUrl('panth_pagebuilderai/generate/index');

            if (isset($result['content'])) {
                $result['content']['children']['ai_content_container'] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'container',
                                'component'     => 'Magento_Ui/js/form/components/html',
                                'content'       => '<div class="panth-ai-panel panth-ai-panel-compact">'
                                    . '<button type="button" onclick="panthOpenContentAiPopup()" class="action-secondary panth-ai-generate">'
                                    . '&#9733; Generate Page Content with AI</button>'
                                    . '<span id="panth-ai-content-status" class="panth-ai-status"></span>'
                                    . '</div>',
                                'sortOrder'     => 1,
                            ],
                        ],
                    ],
                ];
            }

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

    private function isTableAvailable(string $tableName): bool
    {
        try {
            $connection = $this->resource->getConnection();
            return $connection->isTableExists($this->resource->getTableName($tableName));
        } catch (\Throwable) {
            return false;
        }
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
<div class="panth-ai-panel">
    <strong class="panth-ai-panel-title">AI Meta Generation</strong>
    <div class="panth-ai-block">
        <label class="panth-ai-label">Select Prompt:</label>
        <select id="panth-ai-prompt-select-cms" onchange="panthSelectPromptCmsPage(this)" class="admin__control-select panth-ai-select">
            <option value="0">-- Write Custom Prompt --</option>
        </select>
    </div>
    <div class="panth-ai-block">
        <label class="panth-ai-label">Prompt (editable before generating):</label>
        <textarea id="panth-ai-prompt-text-cms" rows="5" class="admin__control-textarea panth-ai-textarea" placeholder="Type your custom prompt here or select a saved one above..."></textarea>
        <div class="panth-ai-note">Placeholders: {{title}}, {{identifier}}, {{content}}, {{store_name}}, {{url}}</div>
    </div>
    <div class="panth-ai-block">
        <label class="panth-ai-label">Upload Images (optional):</label>
        <input type="file" id="panth-ai-images-cms" multiple accept="image/*"
               class="panth-ai-file"
               onchange="panthPreviewImagesCms(this, 'panth-ai-image-preview-cms')"/>
        <div id="panth-ai-image-preview-cms" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px;"></div>
        <div class="panth-ai-note">Upload page images for AI to analyze and generate better descriptions. Max 5 images.</div>
    </div>
    <div class="panth-ai-block panth-ai-actions">
        <button type="button" id="panth-seo-ai-generate-btn-cms"
            onclick="panthSeoAiGenerateCmsPage(this)"
            class="action-secondary panth-ai-generate">
            &#9733; Generate All Fields with AI
        </button>
        <span id="panth-seo-ai-status-cms" class="panth-ai-status"></span>
    </div>
    <div class="panth-ai-foot">
        Or use the <strong class="panth-ai-em">AI</strong> buttons next to individual fields to generate one field at a time.
    </div>
</div>

<!-- Per-field AI popup (CMS page) -->
<div id="panth-ai-field-backdrop-cms" class="panth-ai-backdrop" style="display:none;" onclick="panthCloseFieldAiPopupCms()"></div>
<div id="panth-ai-field-popup-cms" class="panth-ai-popup" style="display:none;">
    <div class="panth-ai-popup-head">
        <strong id="panth-ai-field-popup-title-cms" class="panth-ai-panel-title">Generate: Field</strong>
        <button type="button" onclick="panthCloseFieldAiPopupCms()" class="panth-ai-close" title="Close">&times;</button>
    </div>
    <div>
        <label class="panth-ai-label">Prompt:</label>
        <textarea id="panth-ai-field-prompt-cms" rows="4" class="admin__control-textarea panth-ai-textarea"></textarea>
    </div>
    <div class="panth-ai-block">
        <label class="panth-ai-label">Upload Images (optional):</label>
        <input type="file" id="panth-ai-field-images-cms" multiple accept="image/*"
               class="panth-ai-file"
               onchange="panthPreviewImagesCms(this, 'panth-ai-field-image-preview-cms')"/>
        <div id="panth-ai-field-image-preview-cms" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px;"></div>
    </div>
    <div class="panth-ai-popup-actions">
        <button type="button" id="panth-ai-field-generate-btn-cms" onclick="panthGenerateFieldCms()" class="action-primary">Generate</button>
        <button type="button" onclick="panthCloseFieldAiPopupCms()" class="panth-ai-cancel">Cancel</button>
        <span id="panth-ai-field-status-cms" class="panth-ai-status"></span>
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
        note.style.cssText = 'font-size:12px;color:#c00;align-self:center;';
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
        store_id: storeId,
        // "Generate All Fields" uses the default CMS meta prompt which asks for a
        // {"meta_title":"...","meta_description":"..."} JSON pack.
        output_format: 'json'
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
    if (status) { status.textContent = ''; status.style.color = '#595959'; }
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
    if (statusEl) { statusEl.style.color = '#595959'; statusEl.textContent = 'Calling AI provider...'; }

    var payload = {
        form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
        entity_type: panthAiEntityTypeCms,
        entity_id: entityId,
        store_id: storeId,
        target_field: panthAiCurrentFieldKeyCms,
        custom_prompt: promptText ? promptText.value.trim() : '',
        // Per-field generation wants a single bare string (meta_title text, etc.).
        output_format: 'plain'
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
        btn.className = 'panth-ai-field-btn';
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

    private function loadOverride(int $entityId, int $storeId): ?array
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName(self::OVERRIDE_TABLE);

        if (!$connection->isTableExists($table)) {
            return null;
        }

        try {
            $select = $connection->select()
                ->from($table, ['robots', 'hreflang_identifier'])
                ->where('entity_type = ?', self::ENTITY_TYPE)
                ->where('entity_id = ?', $entityId)
                ->where('store_id IN (?)', [0, $storeId])
                ->order('store_id DESC')
                ->limit(1);

            $row = $connection->fetchRow($select);
        } catch (\Throwable) {
            return null;
        }

        return $row !== false ? $row : null;
    }
}
