<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\Admin;

use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Framework\App\ResourceConnection;
use Panth\PageBuilderAi\Helper\Config as AiConfig;

class AiButtonRenderer
{
    public function __construct(
        private readonly AiConfig $config,
        private readonly BackendUrl $backendUrl,
        private readonly ResourceConnection $resource
    ) {
    }

    public function isAvailable(): bool
    {
        return $this->config->isEnabled() && $this->config->hasOwnApiKey();
    }

    public function buildContainerMeta(
        string $entityType,
        string $idFieldName,
        string $storeFieldName,
        array $fieldMap,
        array $perFieldConfig,
        string $uniqueSuffix,
        string $placeholderHelp = '',
        int $sortOrder = 5
    ): array {
        $generateUrl = $this->backendUrl->getUrl('panth_pagebuilderai/generate/index');

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'container',
                        'component'     => 'Magento_Ui/js/form/components/html',
                        'content'       => $this->buildHtml(
                            $generateUrl,
                            $entityType,
                            $idFieldName,
                            $storeFieldName,
                            $fieldMap,
                            $perFieldConfig,
                            $uniqueSuffix,
                            $placeholderHelp
                        ),
                        'sortOrder'     => $sortOrder,
                        'additionalClasses' => 'panth-pagebuilderai-ai-generate-wrapper',
                    ],
                ],
            ],
        ];
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

    private function buildHtml(
        string $generateUrl,
        string $entityType,
        string $idFieldName,
        string $storeFieldName,
        array $fieldMap,
        array $perFieldConfig,
        string $sfx,
        string $placeholderHelp
    ): string {
        $fieldMapJson = (string)json_encode(
            $fieldMap,
            JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG
        );

        $prompts = $this->loadPrompts($entityType);
        $promptsJson = (string)json_encode(array_map(function ($p) {
            return [
                'id' => (int) $p['prompt_id'],
                'name' => (string) $p['name'],
                'template' => (string) $p['prompt_template'],
                'is_default' => (int) $p['is_default'],
            ];
        }, $prompts), JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG);

        $perFieldConfigJson = (string)json_encode(
            $perFieldConfig,
            JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG
        );

        if ($placeholderHelp === '') {
            $placeholderHelp = 'Type your custom prompt here or select a saved one above...';
        }
        $placeholderHelp = htmlspecialchars($placeholderHelp, ENT_QUOTES, 'UTF-8');

        $entityTypeJs = addslashes($entityType);
        $idFieldJs = addslashes($idFieldName);
        $storeFieldJs = addslashes($storeFieldName);
        $sfx = preg_replace('/[^A-Za-z0-9_]/', '', $sfx);
        $generateUrl = htmlspecialchars($generateUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<div class="panth-ai-panel">
    <strong class="panth-ai-panel-title">AI Content Generation</strong>
    <div class="panth-ai-block">
        <label class="panth-ai-label" for="panth-ai-prompt-select-{$sfx}">Select Prompt:</label>
        <select id="panth-ai-prompt-select-{$sfx}" onchange="panthSelectPrompt_{$sfx}(this)" class="admin__control-select panth-ai-select">
            <option value="0">-- Write Custom Prompt --</option>
        </select>
    </div>
    <div class="panth-ai-block">
        <label class="panth-ai-label" for="panth-ai-prompt-text-{$sfx}">Prompt (editable before generating):</label>
        <textarea id="panth-ai-prompt-text-{$sfx}" rows="4" class="admin__control-textarea panth-ai-textarea" placeholder="{$placeholderHelp}"></textarea>
    </div>
    <div class="panth-ai-block panth-ai-actions">
        <button type="button" id="panth-pagebuilderai-ai-generate-btn-{$sfx}"
            onclick="panthPageBuilderAiGenerate_{$sfx}(this)"
            class="action-secondary panth-ai-generate">
            &#9733; Generate with AI
        </button>
        <span id="panth-pagebuilderai-ai-status-{$sfx}" class="panth-ai-status"></span>
    </div>
    <div class="panth-ai-foot">
        Or use the <strong class="panth-ai-em">AI</strong> buttons next to individual fields to generate one field at a time.
    </div>
</div>

<div id="panth-ai-field-backdrop-{$sfx}" class="panth-ai-backdrop" style="display:none;" onclick="panthCloseFieldAiPopup_{$sfx}()"></div>
<div id="panth-ai-field-popup-{$sfx}" class="panth-ai-popup" style="display:none;">
    <div class="panth-ai-popup-head">
        <strong id="panth-ai-field-popup-title-{$sfx}" class="panth-ai-panel-title">Generate: Field</strong>
        <button type="button" onclick="panthCloseFieldAiPopup_{$sfx}()" class="panth-ai-close" title="Close">&times;</button>
    </div>
    <div>
        <label class="panth-ai-label" for="panth-ai-field-prompt-{$sfx}">Prompt:</label>
        <textarea id="panth-ai-field-prompt-{$sfx}" rows="4" class="admin__control-textarea panth-ai-textarea"></textarea>
    </div>
    <div class="panth-ai-popup-actions">
        <button type="button" id="panth-ai-field-generate-btn-{$sfx}" onclick="panthGenerateField_{$sfx}()" class="action-primary">Generate</button>
        <button type="button" onclick="panthCloseFieldAiPopup_{$sfx}()" class="panth-ai-cancel">Cancel</button>
        <span id="panth-ai-field-status-{$sfx}" class="panth-ai-status"></span>
    </div>
</div>

<script>
(function(){
    var S = '{$sfx}';
    var prompts = {$promptsJson};
    var generateUrl = '{$generateUrl}';
    var entityType = '{$entityTypeJs}';
    var fieldMap = {$fieldMapJson};
    var perFieldConfig = {$perFieldConfigJson};
    var idFieldName = '{$idFieldJs}';
    var storeFieldName = '{$storeFieldJs}';
    var currentFieldInputName = '';
    var currentFieldKey = '';

    var sel = document.getElementById('panth-ai-prompt-select-' + S);
    var ta = document.getElementById('panth-ai-prompt-text-' + S);
    if (sel && prompts) {
        prompts.forEach(function(p) {
            var opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.name + (p.is_default ? ' (default)' : '');
            sel.appendChild(opt);
            if (p.is_default && ta) { sel.value = p.id; ta.value = p.template; }
        });
    }

    function getEntityId() {
        var input = document.querySelector('[name="' + idFieldName + '"]');
        var val = input ? input.value : 0;
        if (!val || val === '0') {
            var m = window.location.href.match(/\/id\/(\d+)/);
            if (!m) m = window.location.href.match(/\/item_id\/(\d+)/);
            if (!m) m = window.location.href.match(/\/slide_id\/(\d+)/);
            if (!m) m = window.location.href.match(/\/testimonial_id\/(\d+)/);
            if (!m) m = window.location.href.match(/\/form_id\/(\d+)/);
            if (m) val = m[1];
        }
        return val;
    }

    function getStoreId() {
        if (!storeFieldName) return 0;
        var input = document.querySelector('[name="' + storeFieldName + '"]');
        if (!input) return 0;
        if (input.multiple) {
            return input.options && input.options[input.selectedIndex]
                ? input.options[input.selectedIndex].value : 0;
        }
        return input.value || 0;
    }

    function setFieldValue(inputName, value) {
        var input = document.querySelector('[name="' + inputName + '"]');
        if (input) {
            input.value = value;
            input.dispatchEvent(new Event('input', {bubbles: true}));
            input.dispatchEvent(new Event('change', {bubbles: true}));
            if (typeof tinyMCE !== 'undefined') {
                var editorId = input.id;
                if (editorId) {
                    var editor = tinyMCE.get(editorId);
                    if (editor) editor.setContent(value);
                }
            }
            return true;
        }
        return false;
    }

    function sendRequest(url, payload) {
        url = url + (url.indexOf('?') > -1 ? '&' : '?') + 'form_key=' + encodeURIComponent(payload.form_key || '');
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

    window['panthSelectPrompt_' + S] = function(sel) {
        var ta = document.getElementById('panth-ai-prompt-text-' + S);
        if (!ta) return;
        if (sel.value === '0') { ta.value = ''; ta.focus(); return; }
        var found = prompts.find(function(p) { return p.id == sel.value; });
        if (found) ta.value = found.template;
    };

    window['panthPageBuilderAiGenerate_' + S] = function(btn) {
        var entityId = getEntityId();
        var storeId = getStoreId();
        var statusEl = document.getElementById('panth-pagebuilderai-ai-status-' + S);

        if (!entityId || entityId === '0') {
            if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'Please save the entity first.'; }
            return;
        }

        var promptText = document.getElementById('panth-ai-prompt-text-' + S);
        var promptSelect = document.getElementById('panth-ai-prompt-select-' + S);

        btn.disabled = true;
        btn.textContent = 'Generating...';
        if (statusEl) statusEl.textContent = 'Calling AI provider...';

        var payload = {
            form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
            entity_type: entityType,
            entity_id: entityId,
            store_id: storeId
        };
        if (promptText && promptText.value.trim()) {
            payload.custom_prompt = promptText.value.trim();
        }
        if (promptSelect && promptSelect.value > 0) {
            payload.prompt_id = parseInt(promptSelect.value);
        }

        sendRequest(generateUrl, payload)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                var filled = [];
                Object.keys(data.data).forEach(function(fieldName) {
                    var inputName = fieldMap[fieldName] || fieldName;
                    if (setFieldValue(inputName, data.data[fieldName])) filled.push(fieldName);
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
            btn.innerHTML = '&#9733; Generate with AI';
        })
        .catch(function(e) {
            if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'Error: ' + e.message; }
            btn.disabled = false;
            btn.innerHTML = '&#9733; Generate with AI';
        });
    };

    window['panthOpenFieldAiPopup_' + S] = function(inputName, config) {
        currentFieldInputName = inputName;
        currentFieldKey = config.field;
        var popup = document.getElementById('panth-ai-field-popup-' + S);
        var backdrop = document.getElementById('panth-ai-field-backdrop-' + S);
        var title = document.getElementById('panth-ai-field-popup-title-' + S);
        var prompt = document.getElementById('panth-ai-field-prompt-' + S);
        var status = document.getElementById('panth-ai-field-status-' + S);

        if (title) title.textContent = 'Generate: ' + config.label;
        if (prompt) prompt.value = config.prompt || '';
        if (status) { status.textContent = ''; status.style.color = '#595959'; }
        if (popup) popup.style.display = 'block';
        if (backdrop) backdrop.style.display = 'block';
        if (prompt) prompt.focus();
    };

    window['panthCloseFieldAiPopup_' + S] = function() {
        var popup = document.getElementById('panth-ai-field-popup-' + S);
        var backdrop = document.getElementById('panth-ai-field-backdrop-' + S);
        if (popup) popup.style.display = 'none';
        if (backdrop) backdrop.style.display = 'none';
        currentFieldInputName = '';
        currentFieldKey = '';
    };

    window['panthGenerateField_' + S] = function() {
        var entityId = getEntityId();
        var storeId = getStoreId();
        var statusEl = document.getElementById('panth-ai-field-status-' + S);
        var generateBtn = document.getElementById('panth-ai-field-generate-btn-' + S);

        if (!entityId || entityId === '0') {
            if (statusEl) { statusEl.style.color = '#c00'; statusEl.textContent = 'Please save the entity first.'; }
            return;
        }

        var promptText = document.getElementById('panth-ai-field-prompt-' + S);
        if (generateBtn) { generateBtn.disabled = true; generateBtn.textContent = 'Generating...'; }
        if (statusEl) { statusEl.style.color = '#595959'; statusEl.textContent = 'Calling AI provider...'; }

        var payload = {
            form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
            entity_type: entityType,
            entity_id: entityId,
            store_id: storeId,
            target_field: currentFieldKey,
            custom_prompt: promptText ? promptText.value.trim() : ''
        };

        sendRequest(generateUrl, payload)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                var value = data.data[currentFieldKey] || '';
                if (!value) {
                    var keys = Object.keys(data.data);
                    if (keys.length > 0) value = data.data[keys[0]];
                }
                if (value) {
                    setFieldValue(currentFieldInputName, value);
                    var msg = 'Done';
                    if (data.provider) msg += ' via ' + data.provider;
                    if (data.tokens_used) msg += ' (' + data.tokens_used + ' tokens)';
                    if (statusEl) { statusEl.style.color = '#006400'; statusEl.textContent = msg; }
                    setTimeout(function() { window['panthCloseFieldAiPopup_' + S](); }, 1200);
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
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') window['panthCloseFieldAiPopup_' + S]();
    });

    setTimeout(function() {
        Object.keys(perFieldConfig).forEach(function(inputName) {
            var input = document.querySelector('[name="' + inputName + '"]');
            if (!input) return;

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:3px;"><path d="M12 2l2.09 6.26L20 10l-5.91 1.74L12 18l-2.09-6.26L4 10l5.91-1.74z"/></svg>AI';
            btn.title = 'Generate ' + perFieldConfig[inputName].label + ' with AI';
            btn.className = 'panth-ai-field-btn';
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                window['panthOpenFieldAiPopup_' + S](inputName, perFieldConfig[inputName]);
            };

            var container = input.closest('.admin__field-control') || input.closest('.admin__field') || input.parentNode;
            if (container) {
                container.appendChild(btn);
            }
        });
    }, 3000);
})();
</script>
HTML;
    }
}
