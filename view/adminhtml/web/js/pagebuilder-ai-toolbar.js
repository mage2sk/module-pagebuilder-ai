(function() {
    'use strict';

    // Watch for PageBuilder toolbar to appear (Knockout renders it dynamically)
    var toolbarInjected = false;
    var observer = new MutationObserver(function() {
        // Check for the template-buttons span (rendered by Knockout after stage loads)
        if (!toolbarInjected) {
            var templateButtons = document.querySelector('.pagebuilder-header .template-buttons');
            if (templateButtons && !templateButtons.querySelector('.panth-pb-ai-btn')) {
                addToolbarButton(templateButtons);
                toolbarInjected = true;
            }
        }
    });

    // Start observing immediately
    observer.observe(document.body, { childList: true, subtree: true });

    // Also poll as fallback (some PageBuilder instances don't trigger mutations)
    var pollCount = 0;
    var pollInterval = setInterval(function() {
        pollCount++;
        if (pollCount > 30) { clearInterval(pollInterval); return; } // Stop after 60 seconds

        if (!toolbarInjected) {
            var templateButtons = document.querySelector('.pagebuilder-header .template-buttons');
            if (templateButtons && !templateButtons.querySelector('.panth-pb-ai-btn')) {
                addToolbarButton(templateButtons);
                toolbarInjected = true;
            }
        }

        // Also inject into edit panels
        injectEditPanelButtons();
    }, 2000);

    // Watch for edit panels opening
    observeEditPanels();

    function injectEditPanelButtons() {
        // Find any new textareas/inputs in slideout panels that haven't been processed
        var panels = document.querySelectorAll('.pagebuilder-slide-out-panel, .pagebuilder-edit-form, aside.modal-slide');
        panels.forEach(function(panel) {
            var fields = panel.querySelectorAll('textarea, input[type="text"]');
            fields.forEach(function(field) {
                if (field.dataset.panthAiInjected) return;
                if (!isAiEligibleField(field)) return;
                var rawName = field.name || field.getAttribute('name') || '';
                if (DEDICATED_FIELD_RE.test(rawName)) return;
                field.dataset.panthAiInjected = '1';
                var label = resolveFieldLabel(field);

                var btn = createSmallAiButton(label, function() {
                    openFieldAiDialog(field, label);
                });
                var container = field.closest('.admin__field-control') || field.parentNode;
                if (container) container.appendChild(btn);
            });
        });
    }

    function addToolbarButton(templateButtons) {
        var btn = createAiButton('AI Content', function() {
            openFullPageAiDialog();
        });
        btn.style.marginRight = '8px';
        templateButtons.insertBefore(btn, templateButtons.firstChild);
    }

    function observeEditPanels() {
        // Watch for PageBuilder edit panels (slide-out forms when you edit a content type)
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType !== 1) return;

                    // Check for PageBuilder edit form panels
                    var panel = node.querySelector ?
                        (node.querySelector('.pagebuilder-edit-form') ||
                         node.querySelector('[data-form="pagebuilder_base_form"]') ||
                         node.querySelector('.admin__field textarea')) : null;

                    if (panel || node.classList?.contains('pagebuilder-edit-form')) {
                        setTimeout(function() { injectAiButtonsInEditForm(node); }, 500);
                    }

                    // Also check for textarea/wysiwyg fields appearing
                    var textareas = node.querySelectorAll ? node.querySelectorAll('textarea') : [];
                    if (textareas.length > 0) {
                        setTimeout(function() { injectAiButtonsForTextareas(textareas); }, 500);
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    // Field names owned by a dedicated admin plugin (ProductSeoFieldsPlugin,
    // CategorySeoFieldsPlugin, CmsPageSeoFieldsPlugin, CmsBlockSeoFieldsPlugin).
    // Those plugins render their own per-field AI buttons with rich prompts and
    // placeholder resolution, so the generic toolbar must NOT double-decorate.
    var DEDICATED_FIELD_RE = /^(product|category|page|block|group)\[/;

    // Field codes that produce admin-authored marketing copy. Only these
    // get the AI assistant button - every other text input on the admin
    // (mass-edit modals, custom panels, search boxes, IDs, SKUs, dates,
    // postcodes, etc.) stays untouched.
    //
    // Catalog product / category content:
    //   name, description, short_description, meta_title, meta_description,
    //   meta_keyword, meta_keywords, url_key
    // CMS page / block content:
    //   title, content_heading, content, identifier
    // Generic editorial fields (blogs, FAQs, knowledge bases):
    //   excerpt, summary, teaser, byline, caption, tagline, heading,
    //   subheading
    var CONTENT_AUTHORING_FIELDS = [
        'name',
        'title',
        'description',
        'short_description',
        'content',
        'content_heading',
        'page_content',
        'block_content',
        'meta_title',
        'meta_description',
        'meta_keyword',
        'meta_keywords',
        'excerpt',
        'summary',
        'teaser',
        'byline',
        'caption',
        'tagline',
        'heading',
        'subheading'
    ];

    // Returns the bare field code from any of:
    //   "description"
    //   "product[description]"
    //   "page[content_heading]"
    //   "myform[group][short_description]"
    function extractFieldCode(rawName) {
        if (!rawName) return '';
        // Take the last bracketed segment, otherwise the whole name.
        var match = rawName.match(/\[([^\[\]]+)\]\s*$/);
        return (match ? match[1] : rawName).toLowerCase();
    }

    // The admin can use AI on a field only when:
    //   1. It's a text-bearing input (textarea, text/search/url).
    //   2. The field's bare code matches one of CONTENT_AUTHORING_FIELDS.
    //
    // Inputs with no name/id at all (dynamic widgets like Manage Columns
    // rows) never pass this check - so the AI button stays out of those.
    function isAiEligibleField(field) {
        if (!field) return false;
        var tag = (field.tagName || '').toLowerCase();
        if (tag !== 'textarea' && tag !== 'input') return false;
        if (tag === 'input') {
            var type = (field.getAttribute('type') || 'text').toLowerCase();
            if (type !== 'text' && type !== 'search' && type !== 'url') return false;
        }
        var candidates = [
            field.name || field.getAttribute('name') || '',
            field.id || '',
            field.getAttribute('data-index') || ''
        ];
        for (var i = 0; i < candidates.length; i++) {
            var code = extractFieldCode(candidates[i]);
            if (code && CONTENT_AUTHORING_FIELDS.indexOf(code) !== -1) {
                return true;
            }
        }
        return false;
    }

    function injectAiButtonsInEditForm(formNode) {
        // Find all textarea and input fields in the edit form
        var fields = formNode.querySelectorAll('textarea, input[type="text"]');
        fields.forEach(function(field) {
            if (field.dataset.panthAiInjected) return;
            if (!isAiEligibleField(field)) return;
            var rawName = field.name || field.getAttribute('name') || '';
            if (DEDICATED_FIELD_RE.test(rawName)) return;
            field.dataset.panthAiInjected = '1';

            var label = resolveFieldLabel(field);

            var btn = createSmallAiButton(label, function() {
                openFieldAiDialog(field, label);
            });

            var container = field.closest('.admin__field-control') || field.parentNode;
            if (container) {
                container.style.position = 'relative';
                container.appendChild(btn);
            }
        });
    }

    function injectAiButtonsForTextareas(textareas) {
        textareas.forEach(function(ta) {
            if (ta.dataset.panthAiInjected) return;
            if (!isAiEligibleField(ta)) return;
            var rawName = ta.name || ta.getAttribute('name') || '';
            if (DEDICATED_FIELD_RE.test(rawName)) return;
            ta.dataset.panthAiInjected = '1';

            var label = resolveFieldLabel(ta);

            var btn = createSmallAiButton(label, function() {
                openFieldAiDialog(ta, label);
            });

            var container = ta.closest('.admin__field-control') || ta.parentNode;
            if (container) container.appendChild(btn);
        });
    }

    function createAiButton(text, onclick) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = '&#9733; ' + text;
        btn.className = 'panth-pb-ai-btn';
        btn.onclick = onclick;
        return btn;
    }

    function createSmallAiButton(label, onclick) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:3px;"><path d="M12 2l2.09 6.26L20 10l-5.91 1.74L12 18l-2.09-6.26L4 10l5.91-1.74z"></path></svg>AI';
        btn.title = 'Generate ' + label + ' with AI';
        btn.className = 'panth-ai-field-btn';
        btn.onclick = function(e) { e.preventDefault(); e.stopPropagation(); onclick(); };
        return btn;
    }

    function openFullPageAiDialog() {
        if (document.querySelector('.panth-pb-ai-popup')) return;
        var opener = document.activeElement;
        var pageTitle = '';
        var titleInput = document.querySelector('input[name="title"]') || document.querySelector('input[name="product[name]"]');
        if (titleInput) pageTitle = titleInput.value;

        var defaultPrompt = 'You are a web content expert. Generate a COMPLETE, professional page using HTML.\n\n'
            + 'PAGE: ' + (pageTitle || 'New Page') + '\n\n'
            + 'REQUIREMENTS:\n'
            + '- Create a full page layout with multiple sections\n'
            + '- Use semantic HTML: <h2>, <h3>, <p>, <ul>, <li>, <strong>, <em>, <a>, <table>\n'
            + '- Include: hero section with heading + intro text, feature/benefit sections, FAQ section, call-to-action\n'
            + '- Use proper heading hierarchy (h2 for sections, h3 for sub-sections)\n'
            + '- Add internal links where appropriate\n'
            + '- Make content SEO-optimized with natural keyword usage\n'
            + '- Mobile-friendly structure\n'
            + '- NO emojis anywhere\n'
            + '- NO PageBuilder data attributes (just clean HTML)\n'
            + '- Include at least 4-6 distinct content sections\n\n'
            + 'Return ONLY the HTML content, no JSON wrapping, no markdown code fences.';

        var backdrop = document.createElement('div');
        backdrop.className = 'panth-pb-ai-backdrop';

        var popup = document.createElement('div');
        popup.className = 'panth-pb-ai-popup';
        popup.style.width = '700px';
        popup.setAttribute('role', 'dialog');
        popup.setAttribute('aria-modal', 'true');
        popup.setAttribute('aria-labelledby', 'panth-pb-ai-title');
        popup.innerHTML = '<div class="panth-pb-ai-popup-header">'
            + '<strong id="panth-pb-ai-title">Generate Full Page Content with AI</strong>'
            + '<button type="button" class="panth-pb-ai-close" aria-label="Close" title="Close">&times;</button>'
            + '</div>'
            + '<div class="panth-pb-ai-popup-body">'
            + '<label for="panth-pb-page-type">Page Type:</label>'
            + '<select id="panth-pb-page-type" class="panth-pb-ai-select">'
            + '<option value="custom">Custom Page</option>'
            + '<option value="homepage" data-prompt="Generate a complete HOMEPAGE layout with HTML. Include: hero banner section with main heading and CTA button, featured categories grid (3 columns), new arrivals section, USP/trust badges bar (4 items: free shipping, secure payment, easy returns, 24/7 support), customer testimonials section, newsletter signup section. Use h2 for sections. No emojis. No JSON. Return only HTML.">Homepage</option>'
            + '<option value="about" data-prompt="Generate a complete ABOUT US page with HTML. Include: company story section, mission and values (3 columns), team section, milestones/achievements, why choose us section with bullet points, CTA section. Professional tone. No emojis. Return only HTML.">About Us</option>'
            + '<option value="contact" data-prompt="Generate a complete CONTACT page with HTML. Include: heading with intro text, contact information section (address, phone, email, hours), FAQ section about contact/support, map placeholder, social media links. No emojis. Return only HTML.">Contact Page</option>'
            + '<option value="faq" data-prompt="Generate a complete FAQ page with HTML and FAQPage schema. Include: page intro, 8-10 common questions organized in sections, use details/summary tags for accordion. Categories: Orders, Shipping, Returns, Products, Account. No emojis. Return only HTML.">FAQ Page</option>'
            + '<option value="landing" data-prompt="Generate a PRODUCT LANDING PAGE with HTML. Include: hero section with product name and key benefit, problem/solution section, 3-column feature grid with icons, comparison table, testimonials, pricing/CTA section, FAQ section. Conversion-focused copy. No emojis. Return only HTML.">Product Landing Page</option>'
            + '<option value="category" data-prompt="Generate CATEGORY LANDING PAGE content with HTML. Include: category description (2 paragraphs with keywords), buying guide section, featured products intro, comparison guide, care/maintenance tips, FAQ section. SEO-optimized. No emojis. Return only HTML.">Category Landing</option>'
            + '<option value="policy" data-prompt="Generate a SHIPPING &amp; RETURNS POLICY page with HTML. Include: shipping methods table (standard, express, overnight with prices and times), free shipping threshold, international shipping info, return policy (30-day window), return process steps, refund timeline, exchange policy, FAQ section. Clear, professional. No emojis. Return only HTML.">Shipping &amp; Returns</option>'
            + '<option value="404" data-prompt="Generate a custom 404 NOT FOUND page with HTML. Include: friendly heading, apologetic message, search suggestion, helpful links (homepage, categories, contact), popular products section placeholder. Engaging but professional. No emojis. Return only HTML.">404 Page</option>'
            + '</select>'
            + '<label for="panth-pb-ai-prompt">Prompt (editable):</label>'
            + '<textarea id="panth-pb-ai-prompt" class="panth-pb-ai-prompt" rows="8"></textarea>'
            + '<div class="panth-pb-ai-upload">'
            + '<label for="panth-pb-ai-images">Reference Images (optional):</label>'
            + '<input type="file" id="panth-pb-ai-images" multiple accept="image/png,image/jpeg,image/gif,image/webp" class="panth-pb-ai-images"/>'
            + '<div class="panth-pb-ai-preview"></div>'
            + '</div>'
            + '<div class="panth-pb-ai-raw">'
            + '<input type="checkbox" id="panth-pb-ai-raw-main"/>'
            + '<label for="panth-pb-ai-raw-main">Use my prompt as-is (skip built-in PageBuilder instructions)</label>'
            + '</div>'
            + '<p class="panth-pb-ai-note">The generated content replaces the current Page Builder content. You can undo it from the confirmation message, and nothing is stored until you save the page.</p>'
            + '</div>'
            + '<div class="panth-pb-ai-popup-footer">'
            + '<button type="button" class="panth-pb-ai-generate-btn" id="panth-pb-gen-btn">Generate Page</button>'
            + '<button type="button" class="panth-pb-ai-cancel-btn">Cancel</button>'
            + '<span class="panth-pb-ai-status" id="panth-pb-gen-status" role="status" aria-live="polite"></span>'
            + '</div>';

        var promptEl = popup.querySelector('#panth-pb-ai-prompt');
        var typeEl = popup.querySelector('#panth-pb-page-type');
        var imgInput = popup.querySelector('.panth-pb-ai-images');
        var previewEl = popup.querySelector('.panth-pb-ai-preview');
        var genBtn = popup.querySelector('#panth-pb-gen-btn');
        var status = popup.querySelector('#panth-pb-gen-status');
        promptEl.value = defaultPrompt;

        var keyHandler;
        var closeDialog = function () {
            backdrop.remove();
            popup.remove();
            document.removeEventListener('keydown', keyHandler, true);
            if (opener && typeof opener.focus === 'function' && document.body.contains(opener)) {
                opener.focus();
            }
        };

        keyHandler = function (e) {
            if (e.key === 'Escape') {
                e.stopPropagation();
                closeDialog();
                return;
            }
            if (e.key !== 'Tab') return;
            var focusable = Array.prototype.filter.call(
                popup.querySelectorAll('button, select, textarea, input'),
                function (el) { return !el.disabled && el.offsetParent !== null; }
            );
            if (!focusable.length) return;
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            if (!popup.contains(document.activeElement)) {
                e.preventDefault();
                first.focus();
            } else if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        };

        backdrop.onclick = closeDialog;
        popup.querySelector('.panth-pb-ai-close').onclick = closeDialog;
        popup.querySelector('.panth-pb-ai-cancel-btn').onclick = closeDialog;

        typeEl.onchange = function () {
            var option = typeEl.options[typeEl.selectedIndex];
            promptEl.value = option.value === 'custom' ? defaultPrompt : (option.getAttribute('data-prompt') || '');
        };

        imgInput.onchange = function () {
            previewEl.innerHTML = '';
            Array.prototype.slice.call(imgInput.files, 0, 5).forEach(function (f) {
                var reader = new FileReader();
                reader.onload = function (ev) {
                    var img = document.createElement('img');
                    img.src = ev.target.result;
                    img.alt = f.name;
                    img.className = 'panth-pb-ai-thumb';
                    previewEl.appendChild(img);
                };
                reader.readAsDataURL(f);
            });
        };

        var setStatus = function (text, color) {
            status.textContent = text;
            status.style.color = color;
        };

        genBtn.onclick = function () {
            var prompt = promptEl.value;
            if (!prompt.trim()) {
                setStatus('Enter a prompt first.', '#c00');
                promptEl.focus();
                return;
            }
            genBtn.disabled = true;
            genBtn.setAttribute('aria-busy', 'true');
            genBtn.textContent = 'Generating...';
            setStatus('Calling AI provider...', '#595959');

            var imgPromises = imgInput.files.length > 0
                ? Array.prototype.slice.call(imgInput.files, 0, 5).map(function (f) {
                    return new Promise(function (res) {
                        if (f.size > 5 * 1024 * 1024) { res(null); return; }
                        var reader = new FileReader();
                        reader.onload = function (ev) { res(ev.target.result); };
                        reader.onerror = function () { res(null); };
                        reader.readAsDataURL(f);
                    });
                }) : [Promise.resolve(null)];

            var finish = function () {
                genBtn.disabled = false;
                genBtn.removeAttribute('aria-busy');
                genBtn.textContent = 'Generate Page';
            };

            Promise.all(imgPromises).then(function (images) {
                images = images.filter(Boolean);
                var entityId = 0;
                var url = window.location.href;
                var m = url.match(/\/page_id\/(\d+)/);
                if (m) entityId = parseInt(m[1], 10);
                if (!entityId) { m = url.match(/\/id\/(\d+)/); if (m) entityId = parseInt(m[1], 10); }
                var idInput = document.querySelector('input[name="page_id"]') || document.querySelector('input[name="block_id"]');
                if (idInput && !entityId) entityId = parseInt(idInput.value, 10) || 0;

                var generateUrl = window.panthPageBuilderAiUrl;
                var rawEl = document.getElementById('panth-pb-ai-raw-main');
                var payload = {
                    form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
                    entity_type: 'cms_page',
                    entity_id: entityId,
                    store_id: 0,
                    custom_prompt: prompt,
                    target_field: 'description',
                    output_format: 'pagebuilder_html',
                    raw_prompt: !!(rawEl && rawEl.checked)
                };
                if (images.length > 0) {
                    payload.images = images;
                }

                var urlWithKey = generateUrl + (generateUrl.indexOf('?') > -1 ? '&' : '?') + 'form_key=' + encodeURIComponent(payload.form_key);
                var fetchOpts = { method: 'POST', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } };
                if (images.length > 0) {
                    fetchOpts.headers['Content-Type'] = 'application/json';
                    fetchOpts.body = JSON.stringify(payload);
                } else {
                    fetchOpts.body = new URLSearchParams(payload);
                }

                fetch(urlWithKey, fetchOpts)
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        finish();
                        if (data.success && data.data) {
                            var content = data.data.description || data.data.content || data.data[Object.keys(data.data)[0]] || '';
                            if (content) {
                                insertIntoPageBuilder(content);
                                setStatus('Content generated. Check the editor.', '#006400');
                                setTimeout(closeDialog, 1500);
                            } else {
                                setStatus('AI returned empty content.', '#c00');
                            }
                        } else {
                            setStatus('Error: ' + (data.message || 'Unknown error'), '#c00');
                        }
                    })
                    .catch(function (e) {
                        finish();
                        setStatus('Error: ' + e.message, '#c00');
                    });
            });
        };

        document.addEventListener('keydown', keyHandler, true);
        document.body.appendChild(backdrop);
        document.body.appendChild(popup);
        promptEl.focus();
        promptEl.setSelectionRange(0, 0);
        promptEl.scrollTop = 0;
    }

    function humanizeFieldCode(raw) {
        var code = extractFieldCode(raw).replace(/[_\-]+/g, ' ').replace(/\s+/g, ' ').trim();
        if (!code) return 'Content';
        return code.replace(/\b[a-z]/g, function(c) { return c.toUpperCase(); });
    }

    function resolveFieldLabel(field) {
        var holder = field.closest('.admin__field');
        var labelEl = holder ? holder.querySelector('.admin__field-label span') : null;
        var label = labelEl ? labelEl.textContent.trim() : '';
        if (!label && field.id) {
            var safeId = window.CSS && CSS.escape ? CSS.escape(field.id) : field.id.replace(/"/g, '');
            var forLabel = document.querySelector('label[for="' + safeId + '"]');
            if (forLabel) label = forLabel.textContent.trim();
        }
        if (!label) {
            label = humanizeFieldCode(
                field.name || field.getAttribute('name') || field.getAttribute('data-index') || field.id || ''
            );
        }
        return label;
    }

    function isRichTextField(field) {
        if (!field || (field.tagName || '').toLowerCase() !== 'textarea') return false;
        if (field.id && typeof tinyMCE !== 'undefined' && tinyMCE.get(field.id)) return true;
        return !!field.closest('.admin__control-wysiwig, .admin__control-wysiwyg, .admin__field-wysiwyg');
    }

    function openFieldAiDialog(field, label) {
        var richText = isRichTextField(field);
        var lowered = (label || '').toLowerCase();
        var hint = '';
        if (/meta ?title/.test(lowered))       hint = 'Aim for 50-60 characters, keyword-focused, no clickbait.';
        else if (/meta ?description/.test(lowered)) hint = 'Aim for 140-156 characters summarising page purpose and visitor benefit.';
        else if (/meta ?keyword/.test(lowered))     hint = 'Return 5-10 comma-separated keywords on a single line.';
        else if (/url ?key|identifier|slug/.test(lowered)) hint = 'Return a lowercase-hyphenated slug only.';
        else if (/short ?description/.test(lowered)) hint = 'Keep under 160 characters. Plain text only.';

        var defaultPrompt = richText
            ? 'Write the "' + label + '" field as clean, well-structured HTML for a rich-text editor.\n' +
              'Use only <p>, <h2>, <h3>, <ul>, <ol>, <li>, <strong>, <em> and <a> tags.\n' +
              'No inline styles, no scripts, no emojis, no markdown, no code fences. Return only the HTML.'
            : 'Fill the "' + label + '" admin form input with a professional SEO-friendly value.\n' +
              (hint ? hint + '\n' : '') +
              'No emojis. No quotes. No JSON. No HTML. No markdown. Output only the bare value.';

        openFieldPanel('Generate: ' + label, defaultPrompt, richText, function(result) {
            var value = '';
            if (result && result.data) {
                var keyMap = {
                    'meta title': 'meta_title',
                    'meta description': 'meta_description',
                    'meta keywords': 'meta_keywords'
                };
                var preferredKey = keyMap[lowered];
                if (preferredKey && result.data[preferredKey]) {
                    value = result.data[preferredKey];
                } else {
                    var keys = Object.keys(result.data).filter(function (k) {
                        return k !== 'content' && k !== 'description';
                    });
                    value = (keys.length ? result.data[keys[0]] : (result.data.content || result.data.description)) || '';
                }
            }
            if (!value && typeof result === 'string') {
                value = result;
            }
            if (!value) {
                return false;
            }
            value = richText
                ? sanitizeLlmHtml(String(value))
                : String(value).trim().replace(/^["'`](.*)["'`]$/s, '$1').trim();
            if (!value) {
                return false;
            }
            field.value = value;
            field.dispatchEvent(new Event('input', {bubbles: true}));
            field.dispatchEvent(new Event('change', {bubbles: true}));
            if (field.id && typeof tinyMCE !== 'undefined') {
                var editor = tinyMCE.get(field.id);
                if (editor) editor.setContent(value);
            }
            return true;
        });
    }

    var activeFieldPanel = null;
    var fieldPanelSeq = 0;

    function closeFieldPanel() {
        if (!activeFieldPanel) return;
        activeFieldPanel.backdrop.remove();
        activeFieldPanel.popup.remove();
        document.removeEventListener('keydown', activeFieldPanel.onKey);
        activeFieldPanel = null;
    }

    function openFieldPanel(title, defaultPrompt, richText, onSuccess) {
        closeFieldPanel();
        var n = ++fieldPanelSeq;
        var backdrop = document.createElement('div');
        backdrop.className = 'panth-ai-backdrop';

        var popup = document.createElement('div');
        popup.className = 'panth-ai-popup';
        popup.setAttribute('role', 'dialog');
        popup.setAttribute('aria-modal', 'true');
        popup.setAttribute('aria-labelledby', 'panth-ai-tb-title-' + n);
        popup.innerHTML = '<div class="panth-ai-popup-head">'
            + '<strong id="panth-ai-tb-title-' + n + '" class="panth-ai-panel-title"></strong>'
            + '<button type="button" class="panth-ai-close" title="Close" aria-label="Close">&times;</button>'
            + '</div>'
            + '<div>'
            + '<label class="panth-ai-label" for="panth-ai-tb-prompt-' + n + '">Prompt:</label>'
            + '<textarea id="panth-ai-tb-prompt-' + n + '" rows="4" class="admin__control-textarea panth-ai-textarea"></textarea>'
            + '</div>'
            + '<div class="panth-ai-popup-actions">'
            + '<button type="button" class="action-primary panth-ai-generate-field">Generate</button>'
            + '<button type="button" class="panth-ai-cancel">Cancel</button>'
            + '<span class="panth-ai-status" role="status" aria-live="polite"></span>'
            + '</div>';

        popup.querySelector('.panth-ai-panel-title').textContent = title;
        var promptEl = popup.querySelector('.panth-ai-textarea');
        promptEl.value = defaultPrompt;
        var status = popup.querySelector('.panth-ai-status');
        var genBtn = popup.querySelector('.panth-ai-generate-field');

        var onKey = function(e) { if (e.key === 'Escape') closeFieldPanel(); };
        backdrop.onclick = closeFieldPanel;
        popup.querySelector('.panth-ai-close').onclick = closeFieldPanel;
        popup.querySelector('.panth-ai-cancel').onclick = closeFieldPanel;

        genBtn.onclick = function() {
            genBtn.disabled = true;
            genBtn.textContent = 'Generating...';
            status.style.color = '#595959';
            status.textContent = 'Calling AI provider...';
            generateContentWithImages(promptEl.value, [], 'plain', richText, function(result) {
                genBtn.disabled = false;
                genBtn.textContent = 'Generate';
                if (result && result.success) {
                    if (onSuccess(result) === false) {
                        status.style.color = '#c00';
                        status.textContent = 'AI returned empty result.';
                        return;
                    }
                    status.style.color = '#006400';
                    status.textContent = 'Done';
                    setTimeout(closeFieldPanel, 1200);
                } else {
                    status.style.color = '#c00';
                    status.textContent = 'Failed: ' + ((result && result.message) || 'Unknown error');
                }
            });
        };

        document.addEventListener('keydown', onKey);
        document.body.appendChild(backdrop);
        document.body.appendChild(popup);
        activeFieldPanel = {backdrop: backdrop, popup: popup, onKey: onKey};
        promptEl.focus();
        promptEl.setSelectionRange(0, 0);
        promptEl.scrollTop = 0;
    }

    function generateContentWithImages(prompt, images, outputFormat, rawPrompt, callback) {
        // Back-compat: old callers pass (prompt, images, callback) or
        // (prompt, images, outputFormat, callback).
        if (typeof outputFormat === 'function') {
            callback = outputFormat;
            outputFormat = 'plain';
            rawPrompt = false;
        } else if (typeof rawPrompt === 'function') {
            callback = rawPrompt;
            rawPrompt = false;
        }
        // Detect entity context from the page
        var entityType = 'cms_page';
        var entityId = 0;

        // Try to detect from URL
        var url = window.location.href;
        if (url.indexOf('/catalog/product/') > -1 || url.indexOf('catalog_product') > -1) {
            entityType = 'product';
            var m = url.match(/\/id\/(\d+)/);
            if (m) entityId = parseInt(m[1]);
        } else if (url.indexOf('/catalog/category/') > -1 || url.indexOf('catalog_category') > -1) {
            entityType = 'category';
            var m = url.match(/\/id\/(\d+)/);
            if (m) entityId = parseInt(m[1]);
        } else if (url.indexOf('/cms/page/') > -1) {
            entityType = 'cms_page';
            var m = url.match(/\/page_id\/(\d+)/);
            if (m) entityId = parseInt(m[1]);
        } else if (url.indexOf('/cms/block/') > -1) {
            entityType = 'cms_page';
            var m = url.match(/\/block_id\/(\d+)/);
            if (m) entityId = parseInt(m[1]);
        }

        // Also try hidden inputs
        if (!entityId) {
            var idInput = document.querySelector('input[name="page_id"]')
                || document.querySelector('input[name="block_id"]')
                || document.querySelector('input[name="product[entity_id]"]')
                || document.querySelector('input[name="entity_id"]');
            if (idInput) entityId = parseInt(idInput.value) || 0;
        }

        var generateUrl = window.panthPageBuilderAiUrl || (window.BASE_URL + 'panth_pagebuilderai/generate/index');

        var payload = {
            form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
            entity_type: entityType,
            entity_id: entityId,
            store_id: 0,
            custom_prompt: prompt,
            target_field: 'description',
            output_format: outputFormat || 'plain',
            raw_prompt: !!rawPrompt
        };

        // Append form_key to URL for Magento admin validation
        var urlWithKey = generateUrl + (generateUrl.indexOf('?') > -1 ? '&' : '?') + 'form_key=' + encodeURIComponent(payload.form_key);
        var fetchOptions = {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        };

        if (images && images.length > 0) {
            payload.images = images;
            fetchOptions.headers['Content-Type'] = 'application/json';
            fetchOptions.body = JSON.stringify(payload);
        } else {
            fetchOptions.body = new URLSearchParams(payload);
        }

        fetch(urlWithKey, fetchOptions)
            .then(function(r) { return r.json(); })
            .then(function(data) { callback(data); })
            .catch(function(e) { callback({ success: false, message: e.message }); });
    }

    var lastUndo = null;

    var BLOCKED_TAGS = {
        script: 1, style: 1, iframe: 1, frame: 1, frameset: 1, object: 1, embed: 1, applet: 1,
        form: 1, meta: 1, link: 1, base: 1, svg: 1, math: 1, noscript: 1, template: 1
    };

    var URL_ATTRIBUTES = {
        href: 1, src: 1, action: 1, formaction: 1, 'xlink:href': 1, poster: 1, background: 1, data: 1
    };

    function isUnsafeUrl(name, value) {
        var compact = String(value || '').replace(/[\u0000-\u0020]+/g, '').toLowerCase();
        if (/^(javascript|vbscript|file|livescript):/.test(compact)) return true;
        if (compact.indexOf('data:') === 0) {
            return !(name === 'src' && /^data:image\/(png|jpe?g|gif|webp);/.test(compact));
        }
        return false;
    }

    function domSanitize(html) {
        if (typeof html !== 'string' || html === '') return '';
        var tpl = document.createElement('template');
        if (!('content' in tpl)) {
            var div = document.createElement('div');
            div.textContent = html;
            return div.innerHTML;
        }
        tpl.innerHTML = html;
        var walk = function (parent) {
            Array.prototype.slice.call(parent.childNodes).forEach(function (node) {
                if (node.nodeType === 8) {
                    parent.removeChild(node);
                    return;
                }
                if (node.nodeType !== 1) return;
                if (BLOCKED_TAGS[node.nodeName.toLowerCase()]) {
                    parent.removeChild(node);
                    return;
                }
                Array.prototype.slice.call(node.attributes).forEach(function (attr) {
                    var name = attr.name.toLowerCase();
                    if (name.indexOf('on') === 0
                        || name === 'srcdoc'
                        || (URL_ATTRIBUTES[name] && isUnsafeUrl(name, attr.value))
                        || (name === 'style' && /expression\s*\(|javascript:|vbscript:|behavior\s*:|-moz-binding/i.test(attr.value))
                    ) {
                        node.removeAttribute(attr.name);
                    }
                });
                walk(node);
            });
        };
        walk(tpl.content);
        return tpl.innerHTML;
    }

    function clearStageRoot(stage) {
        try {
            var rootContainer = stage.rootContainer || stage.parent;
            if (rootContainer && rootContainer.children && typeof rootContainer.children.removeAll === 'function') {
                rootContainer.children.removeAll();
            }
        } catch (e) { /* ignore */ }
    }

    function undoLastInjection() {
        var undo = lastUndo;
        lastUndo = null;
        if (!undo) return;
        var restoredText = 'The previous content was restored. Nothing is stored until you save the page.';
        if (undo.type === 'textarea') {
            undo.textarea.value = undo.html;
            undo.textarea.dispatchEvent(new Event('input', { bubbles: true }));
            undo.textarea.dispatchEvent(new Event('change', { bubbles: true }));
            if (undo.textarea.id && typeof tinyMCE !== 'undefined') {
                var editor = tinyMCE.get(undo.textarea.id);
                if (editor) editor.setContent(undo.html);
            }
            showInjectionBanner(true, restoredText, 'AI content removed.');
            return;
        }
        var pending = undo.snapshots.length;
        var failed = 0;
        var done = function () {
            if (--pending !== 0) return;
            if (failed === 0) {
                showInjectionBanner(true, restoredText, 'AI content removed.');
            } else {
                showInjectionBanner(false, 'The previous content could not be fully restored. Leave the page without saving to discard the AI content.', 'Undo failed.');
            }
        };
        undo.snapshots.forEach(function (snap) {
            try { snap.wysiwyg.value(snap.html); } catch (e) { /* ignore */ }
            clearStageRoot(snap.stage);
            try {
                var p = undo.builder(snap.stage, snap.html);
                if (p && typeof p.then === 'function') {
                    p.then(done, function () { failed++; done(); });
                } else {
                    done();
                }
            } catch (e) {
                failed++;
                done();
            }
        });
    }

    /**
     * Strip <script>, inline event handlers, <iframe>/<object>/<embed>/<form>/<meta>/<link>
     * and dangerous URL schemes from LLM output before it touches the DOM or the stage
     * textarea. The server-side controller also has an allowlisted system prompt; this
     * is defence in depth.
     */
    function sanitizeLlmHtml(raw) {
        if (typeof raw !== 'string' || raw === '') return '';

        // Strip markdown code fences. Some LLMs wrap HTML in ```html ... ``` despite
        // the system prompt saying not to; leaving them in would make the whole
        // response land in a single inert PageBuilder HTML block.
        var out = raw.trim();
        var fenced = out.match(/^```(?:html|xml)?\s*([\s\S]*?)\s*```\s*$/i);
        if (fenced) {
            out = fenced[1].trim();
        } else {
            // Also handle a leading ```html / trailing ``` that don't perfectly
            // wrap (e.g. trailing prose after the closing fence).
            out = out.replace(/^```(?:html|xml)?\s*/i, '').replace(/\s*```\s*$/i, '');
        }

        // Strip well-formed <style>...</style> blocks entirely - the prompt forbids
        // custom CSS, and if the LLM emits one anyway it usually burns the entire
        // token budget before producing content.
        out = out.replace(/<style\b[^>]*>[\s\S]*?<\/style>/gi, '');

        // Handle a TRUNCATED <style> block (opening tag present, closing tag missing -
        // the LLM ran out of tokens mid-CSS). Drop everything from the <style> onward
        // so we don't inject raw CSS text into the DOM. stripos-style check: if an
        // opening <style> exists after all closed blocks were removed, everything
        // from it to the end is orphan CSS.
        var openStyleIdx = out.search(/<style\b/i);
        if (openStyleIdx !== -1) {
            out = out.slice(0, openStyleIdx);
        }

        // Handle CSS that leaked out of a <style> block entirely (the response STARTS
        // with CSS - no <style> opener survived the fence strip, but the body begins
        // with `/* ... */` or `.some-class {`). If there's no `<` before the first
        // `{` or `.` token, or if the response literally begins with CSS punctuation,
        // fast-forward to the first `<div data-content-type="row"` we can find. If
        // there's no such marker we return '' (caller shows the malformed banner).
        var trimmed = out.replace(/^\s+/, '');
        if (trimmed.length > 0) {
            var firstChar = trimmed.charAt(0);
            var startsWithCss =
                firstChar === '{' ||
                firstChar === '}' ||
                firstChar === '/' && trimmed.charAt(1) === '*' ||
                (firstChar === '.' && /^\.[A-Za-z_-][\w-]*\s*[,{]/.test(trimmed));
            if (startsWithCss) {
                var rowIdx = trimmed.search(/<div[^>]*data-content-type=["']row["']/i);
                out = rowIdx === -1 ? '' : trimmed.slice(rowIdx);
            }
        }

        out = out.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
        out = out.replace(/<(iframe|object|embed|form|meta|link)\b[^>]*>(?:[\s\S]*?<\/\1>)?/gi, '');
        out = out.replace(/\son[a-z]+\s*=\s*"[^"]*"/gi, '');
        out = out.replace(/\son[a-z]+\s*=\s*'[^']*'/gi, '');
        out = out.replace(/(href|src)\s*=\s*(["'])\s*(javascript|vbscript|file):[^"']*\2/gi, '$1=$2#$2');
        out = out.replace(/(href|src)\s*=\s*(["'])\s*data:(?!image\/)[^"']*\2/gi, '$1=$2#$2');

        // Strip AI-invented image blocks. We can't trust the LLM to produce a real
        // media URL - it typically hallucinates {{media url="something.jpg"}} that
        // 404s on the storefront. Admins can drop real images in via PageBuilder's
        // upload UI after generation.
        out = out.replace(
            /<figure\b[^>]*data-content-type=["']image["'][\s\S]*?<\/figure>/gi,
            ''
        );

        // PageBuilder button labels MUST be wrapped in <span data-element="link_text">
        // or the storefront theme hides the text and the button renders as an empty
        // pill. Wrap the anchor's inner text when the LLM skipped the span.
        out = out.replace(
            /(<a\b[^>]*class=["']pagebuilder-button-(?:primary|secondary)["'][^>]*>)([\s\S]*?)(<\/a>)/gi,
            function (_, open, inner, close) {
                // Already has a span[data-element=link_text] - leave it alone.
                if (/<span\b[^>]*data-element=["']link_text["']/i.test(inner)) {
                    return open + inner + close;
                }
                var text = inner.trim();
                if (text === '') return open + inner + close;
                return open + '<span data-element="link_text">' + text + '</span>' + close;
            }
        );

        // Final pass: close any tags the LLM left dangling due to mid-response
        // truncation. Without this, stage-builder can throw on malformed DOM.
        out = balanceHtmlTags(out);
        return domSanitize(out);
    }

    /**
     * Cheap tag balancer for LLM output that got cut off mid-element. We do NOT try
     * to be a full HTML parser - just walk the string, push opening tags for a
     * known set of block/inline elements, pop on close tags, and at the end append
     * closing tags for anything still on the stack in LIFO order. Handles the most
     * common truncation cases without rearranging content.
     *
     * Self-closing / void tags (img, br, hr, input, ...) are ignored on both sides.
     */
    function balanceHtmlTags(html) {
        if (typeof html !== 'string' || html === '') return html;

        var balanceable = {
            div: 1, span: 1, p: 1, section: 1, article: 1, aside: 1, header: 1, footer: 1, nav: 1, main: 1,
            h1: 1, h2: 1, h3: 1, h4: 1, h5: 1, h6: 1,
            ul: 1, ol: 1, li: 1, a: 1, figure: 1, figcaption: 1,
            strong: 1, em: 1, b: 1, i: 1, u: 1, small: 1,
            table: 1, thead: 1, tbody: 1, tr: 1, td: 1, th: 1,
            blockquote: 1, details: 1, summary: 1, button: 1
        };

        var stack = [];
        var tagRegex = /<\/?([A-Za-z][A-Za-z0-9]*)\b([^>]*)>/g;
        var match;
        while ((match = tagRegex.exec(html)) !== null) {
            var whole = match[0];
            var name = match[1].toLowerCase();
            if (!balanceable[name]) continue;
            var isClose = whole.charAt(1) === '/';
            // Self-closing syntax like <div ... /> - ignore.
            if (!isClose && /\/\s*>$/.test(whole)) continue;

            if (isClose) {
                // Pop until we find the matching opener; drop any orphaned closers.
                for (var i = stack.length - 1; i >= 0; i--) {
                    if (stack[i] === name) {
                        stack.splice(i, 1);
                        break;
                    }
                }
            } else {
                stack.push(name);
            }
        }

        if (stack.length === 0) return html;

        // Close in LIFO order.
        var tail = '';
        for (var j = stack.length - 1; j >= 0; j--) {
            tail += '</' + stack[j] + '>';
        }
        return html + tail;
    }

    /**
     * Wrap arbitrary HTML in Magento PageBuilder's row/column-group/column/html structure
     * so the output becomes a first-class editable PageBuilder block. Only called when
     * the LLM didn't already emit a data-content-type="row" wrapper.
     */
    function wrapInPageBuilderBlock(innerHtml) {
        return '<div data-content-type="row" data-appearance="contained" data-element="main">' +
                 '<div data-content-type="column-group" data-grid-size="12" data-element="main">' +
                   '<div data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" style="justify-content: flex-start; display: flex; flex-direction: column;">' +
                     '<div data-content-type="html" data-appearance="default" data-element="main">' +
                       innerHtml +
                     '</div>' +
                   '</div>' +
                 '</div>' +
               '</div>';
    }

    /**
     * Inject AI content into whatever PageBuilder / wysiwyg field is currently active.
     *
     * Magento 2 PageBuilder does NOT persist live stage content in the DOM textarea -
     * it holds content in a Knockout observable on a uiRegistry component (e.g.
     * `cms_page_form.cms_page_form.content.content`). Setting textarea.value is
     * therefore insufficient: we must push the new HTML into that observable so the
     * stage re-renders immediately.
     *
     * Strategy (first match wins):
     *   1. uiRegistry - find a component whose config references PageBuilder and
     *      update its `value` observable. This triggers native re-render.
     *   2. Any registered component with a `value` observable AND a field named
     *      `content` / `description` / `short_description`.
     *   3. Plain textarea selectors (legacy wysiwyg / non-PageBuilder admin forms).
     */
    function insertIntoPageBuilder(htmlContent) {
        lastUndo = null;
        var clean = sanitizeLlmHtml(htmlContent);

        // sanitizeLlmHtml can return '' when the LLM response was entirely
        // CSS / truncated style block with no content types recoverable.
        if (!clean || !clean.trim()) {
            showInjectionBanner(false, 'AI response was malformed (only CSS, no content). Try again with a more specific prompt.');
            return;
        }

        if (!/^\s*<div[^>]*data-content-type=["']row["']/i.test(clean)) {
            clean = wrapInPageBuilderBlock(clean);
        }

        // Strategy 1 + 2 via Magento uiRegistry (async; returns false until require is ready)
        var handledByRegistry = tryInjectViaUiRegistry(clean, function (ok) {
            if (ok) {
                announceInjection(clean, true);
            } else {
                // Registry didn't find a component - fall back to textarea.
                if (!tryInjectViaTextarea(clean)) {
                    showInjectionBanner(false);
                }
            }
        });

        if (!handledByRegistry) {
            // require/uiRegistry not available at all - go straight to textarea path.
            if (!tryInjectViaTextarea(clean)) {
                showInjectionBanner(false);
            }
        }
    }

    function announceInjection(clean, ok) {
        document.dispatchEvent(new CustomEvent('panth:pagebuilder-ai:content-injected', {
            detail: { length: clean.length }
        }));
        showInjectionBanner(ok);
    }

    /**
     * Drive new content into any live PageBuilder stage.
     *
     * In Magento PageBuilder the stage's "source of truth" on save is
     * `pageBuilder.stage.masterFormat()` - NOT the `value` observable. So to both
     * (a) re-render the stage visually, and (b) have the new content actually
     * persist when the admin clicks Save, we have to replace the stage's content
     * type tree via `Magento_PageBuilder/js/stage-builder`.
     *
     * `stage-builder` takes a stage instance and an HTML string, parses it into
     * a tree of content-type components, and attaches them under the stage's
     * root-container. Everything (drag handles, edit buttons, master-format
     * serialization) then works natively.
     *
     * The callback receives a boolean - true if at least one stage was rebuilt.
     */
    function tryInjectViaUiRegistry(clean, callback) {
        if (typeof require !== 'function') return false;

        require(['uiRegistry', 'Magento_PageBuilder/js/stage-builder'], function (registry, stageBuilder) {
            var wysiwygs = [];
            try {
                wysiwygs = registry.filter(function (item) {
                    return item && item.pageBuilder && item.pageBuilder.stage;
                }) || [];
            } catch (e) { /* registry.filter missing - empty array keeps us in fallback */ }

            if (wysiwygs.length === 0) {
                callback(tryFallbackValueInjection(registry, clean));
                return;
            }

            var rebuilt = 0;
            var pending = wysiwygs.length;
            var snapshots = wysiwygs.map(function (w) {
                var html = '';
                try { html = String(w.value() || ''); } catch (e) { /* ignore */ }
                return { wysiwyg: w, stage: w.pageBuilder.stage, html: html };
            });
            var settle = function () {
                if (--pending !== 0) return;
                if (rebuilt > 0) {
                    lastUndo = { type: 'stage', snapshots: snapshots, builder: stageBuilder };
                }
                callback(rebuilt > 0);
            };

            // Helper: clear the stage root children before each stage-builder attempt.
            var clearRoot = clearStageRoot;

            // Helper: secondary attempt - wrap the whole payload in a single
            // data-content-type="html" block. Much more forgiving than the
            // multi-row tree when the content is marginally malformed.
            var attemptHtmlBlockFallback = function (wysiwyg, stage) {
                var wrapped = wrapInPageBuilderBlock(clean);
                try { wysiwyg.value(wrapped); } catch (e) {}
                clearRoot(stage);
                try {
                    var p = stageBuilder(stage, wrapped);
                    if (p && typeof p.then === 'function') {
                        p.then(function () { rebuilt++; settle(); },
                               function (err) {
                                   if (window.console) console.warn('[Panth PageBuilder AI] stage-builder HTML-block fallback rejected:', err);
                                   // Final fallback - write value() observable only.
                                   if (tryFallbackValueInjection(registry, clean)) { rebuilt++; }
                                   settle();
                               });
                        return;
                    }
                    rebuilt++;
                    settle();
                } catch (e2) {
                    if (window.console) console.warn('[Panth PageBuilder AI] stage-builder HTML-block fallback threw:', e2);
                    if (tryFallbackValueInjection(registry, clean)) { rebuilt++; }
                    settle();
                }
            };

            wysiwygs.forEach(function (wysiwyg) {
                var stage = wysiwyg.pageBuilder.stage;
                // Also write the observable so the wysiwyg sees the pristine HTML
                // if it falls back to value() before stage rebuild completes.
                try { wysiwyg.value(clean); } catch (e) {}

                clearRoot(stage);

                try {
                    // stage-builder signature (Magento 2.4.x): (stage, template) => Promise<Stage>
                    var p = stageBuilder(stage, clean);
                    if (p && typeof p.then === 'function') {
                        p.then(function () {
                            rebuilt++;
                            settle();
                        }, function (err) {
                            if (window.console) console.warn('[Panth PageBuilder AI] stage-builder rejected:', err);
                            // Try the single-HTML-block wrap before giving up.
                            attemptHtmlBlockFallback(wysiwyg, stage);
                        });
                    } else {
                        // Synchronous API (older signature) - treat as success.
                        rebuilt++;
                        settle();
                    }
                } catch (e) {
                    // Synchronous throw from stage-builder (e.g. the TypeError on
                    // setAttribute of null when HTML is malformed). Retry via the
                    // forgiving single-HTML-block wrap.
                    if (window.console) console.warn('[Panth PageBuilder AI] stage-builder threw, retrying as HTML block:', e);
                    attemptHtmlBlockFallback(wysiwyg, stage);
                }
            });
        }, function (err) {
            if (window.console) console.warn('[Panth PageBuilder AI] require failed:', err);
            // stage-builder module missing - fall back to pure value() observable injection.
            require(['uiRegistry'], function (registry) {
                callback(tryFallbackValueInjection(registry, clean));
            }, function () { callback(false); });
        });

        return true;
    }

    /**
     * Last-ditch: no pageBuilder instance found, but maybe a plain wysiwyg /
     * hidden-input component has a value() observable we can update so the
     * content at least persists on save. Does NOT rebuild any stage.
     */
    function tryFallbackValueInjection(registry, clean) {
        var updated = 0;
        try {
            var matches = registry.filter(function (item) {
                if (!item || typeof item.value !== 'function') return false;
                var idx = (item.index || '') + ' ' + (item.dataScope || '') + ' ' + (item.name || '');
                return /\b(content|description|short_description)\b/i.test(idx)
                    && !/email|label|title|meta/i.test(idx);
            }) || [];
            matches.forEach(function (item) {
                try { item.value(clean); updated++; } catch (e) { /* ignore */ }
            });
        } catch (e) { /* ignore */ }
        return updated > 0;
    }

    /**
     * Plain-textarea fallback - legacy wysiwyg forms and any hidden wysiwyg textarea
     * bound to TinyMCE. Returns true if a textarea was found and updated.
     */
    function tryInjectViaTextarea(clean) {
        var selectors = [
            'textarea[data-role="stage"]',
            'textarea[data-role="pagebuilder-stage"]',
            '#cms_page_form_content textarea',
            '#cms_block_form_content textarea',
            'textarea[name="content"]',
            'textarea[name="page[content]"]',
            'textarea[name="block[content]"]',
            'textarea[name="product[description]"]',
            'textarea[name="product[short_description]"]',
            'textarea[name="category[description]"]',
            '[data-index="content"] textarea',
            '[data-index="description"] textarea'
        ];

        var textarea = null;
        for (var i = 0; i < selectors.length && !textarea; i++) {
            textarea = document.querySelector(selectors[i]);
        }
        if (!textarea) return false;

        lastUndo = { type: 'textarea', textarea: textarea, html: textarea.value };
        textarea.value = clean;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        textarea.dispatchEvent(new Event('change', { bubbles: true }));

        if (textarea.id && typeof tinyMCE !== 'undefined') {
            var editor = tinyMCE.get(textarea.id);
            if (editor) editor.setContent(clean);
        }

        tryRefreshKnockoutStage(clean);
        announceInjection(clean, true);
        return true;
    }

    /**
     * Final polish: after mutating a textarea, nudge any Knockout-bound stage to
     * re-render. Only relevant when the registry path missed (non-PageBuilder wysiwyg).
     */
    function tryRefreshKnockoutStage(clean) {
        if (typeof ko === 'undefined') return;
        var wrapper = document.querySelector('.pagebuilder-stage-wrapper, .pagebuilder-stage, [data-bind*="stage"]');
        if (!wrapper) return;
        try {
            var vm = ko.dataFor(wrapper);
            if (!vm) return;
            if (typeof vm.setStage === 'function') { vm.setStage(clean); return; }
            if (vm.stage && typeof vm.stage.setContent === 'function') { vm.stage.setContent(clean); return; }
            if (vm.masterFormat && typeof vm.masterFormat === 'function') { vm.masterFormat(clean); return; }
            if (typeof vm.onContentChange === 'function') { vm.onContentChange(clean); }
        } catch (e) { /* ignore */ }
    }

    function showInjectionBanner(ok, customMessage, heading) {
        var existing = document.getElementById('panth-pb-ai-banner');
        if (existing) existing.remove();

        var bar = document.createElement('div');
        bar.id = 'panth-pb-ai-banner';
        bar.setAttribute('role', ok ? 'status' : 'alert');
        bar.setAttribute('aria-live', ok ? 'polite' : 'assertive');
        bar.style.cssText =
            'position:fixed;top:20px;right:20px;z-index:999999;max-width:420px;' +
            'padding:14px 18px 14px 16px;border-radius:4px;font-family:inherit;font-size:13px;' +
            'box-shadow:0 4px 14px rgba(0,0,0,.18);line-height:1.45;' +
            (ok
                ? 'background:#eaf7e6;border-left:4px solid #3fae2a;color:#1a5a0a;'
                : 'background:#fbeaea;border-left:4px solid #c00;color:#5a0a0a;');

        var title = document.createElement('strong');
        var body = document.createElement('div');
        if (customMessage && typeof customMessage === 'string') {
            title.textContent = heading || (ok ? 'AI content applied.' : 'Could not apply AI content.');
            body.textContent = customMessage;
        } else if (ok) {
            title.textContent = 'AI content applied.';
            body.textContent = 'The generated content replaced the Page Builder content. Review it and save the page to keep it, or choose Undo to restore the previous content.';
        } else {
            title.textContent = 'Could not find PageBuilder stage.';
            body.textContent = 'No content textarea was detected on this page. Open a CMS Page / Block / Product / Category first, then try again.';
        }
        bar.appendChild(title);
        bar.appendChild(body);

        var actions = document.createElement('div');
        actions.style.cssText = 'margin-top:8px;display:flex;gap:16px;';
        var color = ok ? '#1a5a0a' : '#5a0a0a';
        var makeButton = function (label, handler) {
            var b = document.createElement('button');
            b.type = 'button';
            b.textContent = label;
            b.className = 'panth-pb-ai-banner-btn';
            b.style.cssText = 'background:transparent;border:0;color:' + color + ';cursor:pointer;text-decoration:underline;padding:4px 0;font:inherit;font-weight:600;';
            b.onclick = handler;
            actions.appendChild(b);
            return b;
        };
        var canUndo = ok && !customMessage && lastUndo !== null;
        if (canUndo) {
            makeButton('Undo', function () { bar.remove(); undoLastInjection(); });
        }
        makeButton('Dismiss', function () { bar.remove(); });
        bar.appendChild(actions);

        document.body.appendChild(bar);
        if (!canUndo) {
            setTimeout(function () { if (bar.parentNode) bar.remove(); }, 12000);
        }
    }
})();
