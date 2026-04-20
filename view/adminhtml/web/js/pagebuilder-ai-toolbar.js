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
                var label = '';
                var labelEl = field.closest('.admin__field') ? field.closest('.admin__field').querySelector('.admin__field-label span') : null;
                if (labelEl) label = labelEl.textContent.trim();
                if (!label) label = field.name || field.getAttribute('name') || 'Content';

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

    // Only decorate inputs where the admin can plausibly type content we can
    // generate (titles, descriptions, rich text). Checkboxes, radios, selects,
    // numbers, colour pickers etc. are configuration — never show the AI
    // button on those.
    function isAiEligibleField(field) {
        if (!field) return false;
        var tag = (field.tagName || '').toLowerCase();
        if (tag === 'textarea') return true;
        if (tag !== 'input') return false;
        var type = (field.getAttribute('type') || 'text').toLowerCase();
        return type === 'text' || type === 'search' || type === 'url';
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

            var fieldName = rawName;
            var label = '';
            var labelEl = field.closest('.admin__field')?.querySelector('.admin__field-label span');
            if (labelEl) label = labelEl.textContent.trim();

            if (!label && !fieldName) return;

            var btn = createSmallAiButton(label || fieldName, function() {
                openFieldAiDialog(field, label || fieldName);
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

            var label = '';
            var labelEl = ta.closest('.admin__field')?.querySelector('.admin__field-label span');
            if (labelEl) label = labelEl.textContent.trim();
            if (!label) label = ta.name || 'Content';

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
        btn.innerHTML = '&#9733; AI';
        btn.title = 'Generate ' + label + ' with AI';
        btn.className = 'panth-pb-ai-btn-small';
        btn.onclick = function(e) { e.preventDefault(); e.stopPropagation(); onclick(); };
        return btn;
    }

    function openFullPageAiDialog() {
        // Detect current page title for context
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

        // Create enhanced dialog with page type selector
        var backdrop = document.createElement('div');
        backdrop.className = 'panth-pb-ai-backdrop';
        backdrop.onclick = function() { backdrop.remove(); popup.remove(); };

        var popup = document.createElement('div');
        popup.className = 'panth-pb-ai-popup';
        popup.style.width = '700px';
        popup.innerHTML = '<div class="panth-pb-ai-popup-header">'
            + '<strong>Generate Full Page Content with AI</strong>'
            + '<button type="button" class="panth-pb-ai-close" onclick="this.closest(\'.panth-pb-ai-popup\').remove();document.querySelector(\'.panth-pb-ai-backdrop\')?.remove();">&times;</button>'
            + '</div>'
            + '<div class="panth-pb-ai-popup-body">'
            + '<label>Page Type:</label>'
            + '<select id="panth-pb-page-type" onchange="document.getElementById(\'panth-pb-ai-prompt\').value=this.options[this.selectedIndex].dataset.prompt" style="width:100%;padding:6px;margin:4px 0 10px;border:1px solid #ccc;border-radius:4px;">'
            + '<option value="custom" data-prompt="' + defaultPrompt.replace(/"/g, '&quot;') + '">Custom Page</option>'
            + '<option value="homepage" data-prompt="Generate a complete HOMEPAGE layout with HTML. Include: hero banner section with main heading and CTA button, featured categories grid (3 columns), new arrivals section, USP/trust badges bar (4 items: free shipping, secure payment, easy returns, 24/7 support), customer testimonials section, newsletter signup section. Use h2 for sections. No emojis. No JSON. Return only HTML.">Homepage</option>'
            + '<option value="about" data-prompt="Generate a complete ABOUT US page with HTML. Include: company story section, mission and values (3 columns), team section, milestones/achievements, why choose us section with bullet points, CTA section. Professional tone. No emojis. Return only HTML.">About Us</option>'
            + '<option value="contact" data-prompt="Generate a complete CONTACT page with HTML. Include: heading with intro text, contact information section (address, phone, email, hours), FAQ section about contact/support, map placeholder, social media links. No emojis. Return only HTML.">Contact Page</option>'
            + '<option value="faq" data-prompt="Generate a complete FAQ page with HTML and FAQPage schema. Include: page intro, 8-10 common questions organized in sections, use details/summary tags for accordion. Categories: Orders, Shipping, Returns, Products, Account. No emojis. Return only HTML.">FAQ Page</option>'
            + '<option value="landing" data-prompt="Generate a PRODUCT LANDING PAGE with HTML. Include: hero section with product name and key benefit, problem/solution section, 3-column feature grid with icons, comparison table, testimonials, pricing/CTA section, FAQ section. Conversion-focused copy. No emojis. Return only HTML.">Product Landing Page</option>'
            + '<option value="category" data-prompt="Generate CATEGORY LANDING PAGE content with HTML. Include: category description (2 paragraphs with keywords), buying guide section, featured products intro, comparison guide, care/maintenance tips, FAQ section. SEO-optimized. No emojis. Return only HTML.">Category Landing</option>'
            + '<option value="policy" data-prompt="Generate a SHIPPING & RETURNS POLICY page with HTML. Include: shipping methods table (standard, express, overnight with prices and times), free shipping threshold, international shipping info, return policy (30-day window), return process steps, refund timeline, exchange policy, FAQ section. Clear, professional. No emojis. Return only HTML.">Shipping & Returns</option>'
            + '<option value="404" data-prompt="Generate a custom 404 NOT FOUND page with HTML. Include: friendly heading, apologetic message, search suggestion, helpful links (homepage, categories, contact), popular products section placeholder. Engaging but professional. No emojis. Return only HTML.">404 Page</option>'
            + '</select>'
            + '<label>Prompt (editable):</label>'
            + '<textarea id="panth-pb-ai-prompt" class="panth-pb-ai-prompt" rows="8">' + defaultPrompt + '</textarea>'
            + '<div class="panth-pb-ai-upload">'
            + '<label>Reference Images (optional):</label>'
            + '<input type="file" multiple accept="image/*" class="panth-pb-ai-images" onchange="var p=this.nextElementSibling;p.innerHTML=\'\';Array.from(this.files).slice(0,5).forEach(function(f){var r=new FileReader();r.onload=function(e){var i=document.createElement(\'img\');i.src=e.target.result;i.style.cssText=\'width:50px;height:50px;object-fit:cover;border-radius:4px;border:1px solid #ccc;\';p.appendChild(i);};r.readAsDataURL(f);})"/>'
            + '<div class="panth-pb-ai-preview"></div>'
            + '</div>'
            + '<div style="margin:8px 0;display:flex;align-items:center;gap:6px;font-size:12px;color:#666;">'
            + '<input type="checkbox" id="panth-pb-ai-raw-main"/>'
            + '<label for="panth-pb-ai-raw-main">Use my prompt as-is (skip built-in PageBuilder instructions)</label>'
            + '</div>'
            + '</div>'
            + '<div class="panth-pb-ai-popup-footer">'
            + '<button type="button" class="panth-pb-ai-generate-btn" id="panth-pb-gen-btn">Generate Page</button>'
            + '<button type="button" class="panth-pb-ai-cancel-btn" onclick="this.closest(\'.panth-pb-ai-popup\').remove();document.querySelector(\'.panth-pb-ai-backdrop\')?.remove();">Cancel</button>'
            + '<span class="panth-pb-ai-status" id="panth-pb-gen-status"></span>'
            + '</div>';

        var genBtn = popup.querySelector('#panth-pb-gen-btn');
        genBtn.onclick = function() {
            var prompt = document.getElementById('panth-pb-ai-prompt').value;
            var status = document.getElementById('panth-pb-gen-status');
            genBtn.disabled = true;
            genBtn.textContent = 'Generating...';
            status.textContent = 'Calling AI provider...';
            status.style.color = '#666';

            // Collect images
            var imgInput = popup.querySelector('.panth-pb-ai-images');
            var imgPromises = imgInput && imgInput.files.length > 0
                ? Array.from(imgInput.files).slice(0, 5).map(function(f) {
                    return new Promise(function(res) {
                        if (f.size > 5*1024*1024) { res(null); return; }
                        var reader = new FileReader();
                        reader.onload = function(e) { res(e.target.result); };
                        reader.readAsDataURL(f);
                    });
                }) : [Promise.resolve(null)];

            Promise.all(imgPromises).then(function(images) {
                images = images.filter(Boolean);
                var entityType = 'cms_page';
                var entityId = 0;
                var url = window.location.href;
                var m = url.match(/\/page_id\/(\d+)/);
                if (m) entityId = parseInt(m[1]);
                if (!entityId) { m = url.match(/\/id\/(\d+)/); if (m) entityId = parseInt(m[1]); }
                var idInput = document.querySelector('input[name="page_id"]') || document.querySelector('input[name="block_id"]');
                if (idInput && !entityId) entityId = parseInt(idInput.value) || 0;

                var generateUrl = window.panthPageBuilderAiUrl;
                var rawEl = document.getElementById('panth-pb-ai-raw-main');
                var payload = {
                    form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
                    entity_type: entityType,
                    entity_id: entityId,
                    store_id: 0,
                    custom_prompt: prompt,
                    target_field: 'description',
                    // Stage-level "AI Content" button: full PageBuilder HTML page.
                    output_format: 'pagebuilder_html',
                    raw_prompt: !!(rawEl && rawEl.checked)
                };

                // Attach images as base64 data URIs. Controller validates each entry against
                // a strict data-URI / https-URL allowlist and caps at 5 images / 4MB each.
                if (images.length > 0) {
                    payload.images = images;
                }

                // Always append form_key to URL (Magento admin requirement)
                var urlWithKey = generateUrl + (generateUrl.indexOf('?') > -1 ? '&' : '?') + 'form_key=' + encodeURIComponent(payload.form_key);
                var fetchOpts = { method: 'POST', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } };
                if (images.length > 0) {
                    // JSON body required so the base64-encoded images survive transit without URL-encoding blowup.
                    fetchOpts.headers['Content-Type'] = 'application/json';
                    fetchOpts.body = JSON.stringify(payload);
                } else {
                    fetchOpts.body = new URLSearchParams(payload);
                }

                fetch(urlWithKey, fetchOpts)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        genBtn.disabled = false;
                        genBtn.textContent = 'Generate Page';
                        if (data.success && data.data) {
                            var content = data.data.description || data.data.content || data.data[Object.keys(data.data)[0]] || '';
                            if (content) {
                                insertIntoPageBuilder(content);
                                status.textContent = 'Content generated! Check the editor.';
                                status.style.color = '#006400';
                                setTimeout(function() { backdrop.remove(); popup.remove(); }, 1500);
                            } else {
                                status.textContent = 'AI returned empty content.';
                                status.style.color = '#c00';
                            }
                        } else {
                            status.textContent = 'Error: ' + (data.message || 'Unknown error');
                            status.style.color = '#c00';
                        }
                    })
                    .catch(function(e) {
                        genBtn.disabled = false;
                        genBtn.textContent = 'Generate Page';
                        status.textContent = 'Error: ' + e.message;
                        status.style.color = '#c00';
                    });
            });
        };

        // ESC to close
        var escHandler = function(e) { if (e.key === 'Escape') { backdrop.remove(); popup.remove(); document.removeEventListener('keydown', escHandler); } };
        document.addEventListener('keydown', escHandler);

        document.body.appendChild(backdrop);
        document.body.appendChild(popup);
    }

    function openFieldAiDialog(field, label) {
        var lowered = (label || '').toLowerCase();
        var hint = '';
        if (/meta ?title/.test(lowered))       hint = 'Aim for 50–60 characters, keyword-focused, no clickbait.';
        else if (/meta ?description/.test(lowered)) hint = 'Aim for 140–156 characters summarising page purpose and visitor benefit.';
        else if (/meta ?keyword/.test(lowered))     hint = 'Return 5–10 comma-separated keywords on a single line.';
        else if (/url ?key|identifier|slug/.test(lowered)) hint = 'Return a lowercase-hyphenated slug only.';
        else if (/short ?description/.test(lowered)) hint = 'Keep under 160 characters. Plain text only.';

        var defaultPrompt =
            'Fill the "' + label + '" admin form input with a professional SEO-friendly value.\n' +
            (hint ? hint + '\n' : '') +
            'No emojis. No quotes. No JSON. No HTML. No markdown. Output only the bare value.';

        var dialog = createDialog('Generate: ' + label, function(result) {
            var value = '';
            if (result && result.data) {
                // Prefer explicit field names if the server returned a JSON pack.
                var keyMap = {
                    'meta title': 'meta_title',
                    'meta description': 'meta_description',
                    'meta keywords': 'meta_keywords'
                };
                var preferredKey = keyMap[lowered];
                if (preferredKey && result.data[preferredKey]) {
                    value = result.data[preferredKey];
                } else {
                    // Skip wrapper keys that always exist (content / description may contain raw LLM output).
                    var keys = Object.keys(result.data).filter(function (k) {
                        return k !== 'content' && k !== 'description';
                    });
                    value = (keys.length ? result.data[keys[0]] : (result.data.content || result.data.description)) || '';
                }
            }
            if (!value && typeof result === 'string') {
                value = result;
            }
            if (value) {
                // Strip surrounding quotes and whitespace that LLMs sometimes add.
                value = String(value).trim().replace(/^["'`](.*)["'`]$/s, '$1').trim();
                field.value = value;
                field.dispatchEvent(new Event('input', {bubbles: true}));
                field.dispatchEvent(new Event('change', {bubbles: true}));

                if (field.id && typeof tinyMCE !== 'undefined') {
                    var editor = tinyMCE.get(field.id);
                    if (editor) editor.setContent(value);
                }
            }
        }, defaultPrompt, 'plain');
        document.body.appendChild(dialog.backdrop);
        document.body.appendChild(dialog.popup);
    }

    function createDialog(title, onSuccess, defaultPrompt, outputFormat) {
        defaultPrompt = defaultPrompt || 'Write professional, SEO-optimized content for this section. Use proper HTML formatting with <p>, <h2>, <ul>, <li> tags. Follow Google 2026 SEO best practices. No emojis.';
        outputFormat = outputFormat || 'plain';

        var backdrop = document.createElement('div');
        backdrop.className = 'panth-pb-ai-backdrop';
        backdrop.onclick = function() { closeDialog(); };

        var popup = document.createElement('div');
        popup.className = 'panth-pb-ai-popup';
        popup.innerHTML = '<div class="panth-pb-ai-popup-header">'
            + '<strong>' + title + '</strong>'
            + '<button type="button" class="panth-pb-ai-close">&times;</button>'
            + '</div>'
            + '<div class="panth-pb-ai-popup-body">'
            + '<label>Prompt (editable):</label>'
            + '<textarea class="panth-pb-ai-prompt" rows="4">' + defaultPrompt + '</textarea>'
            + '<div class="panth-pb-ai-upload">'
            + '<label>Upload Images (optional):</label>'
            + '<input type="file" multiple accept="image/*" class="panth-pb-ai-images"/>'
            + '<div class="panth-pb-ai-preview"></div>'
            + '</div>'
            + '<div style="margin:8px 0;display:flex;align-items:center;gap:6px;font-size:12px;color:#666;">'
            + '<input type="checkbox" class="panth-pb-ai-raw-field"/>'
            + '<label>Use my prompt as-is (skip built-in PageBuilder instructions)</label>'
            + '</div>'
            + '</div>'
            + '<div class="panth-pb-ai-popup-footer">'
            + '<button type="button" class="panth-pb-ai-generate-btn">Generate</button>'
            + '<button type="button" class="panth-pb-ai-cancel-btn">Cancel</button>'
            + '<span class="panth-pb-ai-status"></span>'
            + '</div>';

        // Close button handler
        popup.querySelector('.panth-pb-ai-close').onclick = function() { closeDialog(); };

        // Cancel button handler
        popup.querySelector('.panth-pb-ai-cancel-btn').onclick = function() { closeDialog(); };

        // Image preview handler
        var imgInput = popup.querySelector('.panth-pb-ai-images');
        imgInput.onchange = function() {
            var preview = popup.querySelector('.panth-pb-ai-preview');
            preview.innerHTML = '';
            Array.from(this.files).slice(0, 5).forEach(function(f) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width:50px;height:50px;object-fit:cover;border-radius:4px;border:1px solid #ccc;';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(f);
            });
        };

        // Generate button handler
        var genBtn = popup.querySelector('.panth-pb-ai-generate-btn');
        genBtn.onclick = function() {
            var promptText = popup.querySelector('.panth-pb-ai-prompt').value;
            var rawCheckbox = popup.querySelector('.panth-pb-ai-raw-field')
                || document.querySelector('.panth-pb-ai-popup .panth-pb-ai-raw-field');
            var rawPrompt = !!(rawCheckbox && rawCheckbox.checked);
            var status = popup.querySelector('.panth-pb-ai-status');
            genBtn.disabled = true;
            genBtn.textContent = 'Generating...';
            status.textContent = 'Calling AI...';
            status.style.color = '#666';

            // Collect images
            var imgFiles = popup.querySelector('.panth-pb-ai-images').files;
            var imagePromises = Array.from(imgFiles).slice(0, 5).map(function(f) {
                return new Promise(function(resolve) {
                    if (f.size > 5 * 1024 * 1024) { resolve(null); return; }
                    var reader = new FileReader();
                    reader.onload = function(e) { resolve(e.target.result); };
                    reader.readAsDataURL(f);
                });
            });

            Promise.all(imagePromises).then(function(images) {
                images = images.filter(Boolean);

                generateContentWithImages(promptText, images, outputFormat, rawPrompt, function(result) {
                    genBtn.disabled = false;
                    genBtn.textContent = 'Generate';

                    if (result && result.success) {
                        status.textContent = 'Done!';
                        status.style.color = '#006400';
                        onSuccess(result);
                        setTimeout(function() { closeDialog(); }, 1000);
                    } else {
                        status.textContent = 'Error: ' + (result?.message || 'Unknown');
                        status.style.color = '#c00';
                    }
                });
            });
        };

        function closeDialog() {
            backdrop.remove();
            popup.remove();
        }

        // ESC key
        var escHandler = function(e) {
            if (e.key === 'Escape') {
                closeDialog();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);

        return { backdrop: backdrop, popup: popup };
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

    /**
     * Strip <script>, inline event handlers, <iframe>/<object>/<embed>/<form>/<meta>/<link>
     * and dangerous URL schemes from LLM output before it touches the DOM or the stage
     * textarea. The server-side controller also has an allowlisted system prompt; this
     * is defence in depth.
     */
    function sanitizeLlmHtml(raw) {
        if (typeof raw !== 'string' || raw === '') return '';

        // Strip markdown code fences. Some LLMs wrap HTML in ```html … ``` despite
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

        // Strip well-formed <style>…</style> blocks entirely — the prompt forbids
        // custom CSS, and if the LLM emits one anyway it usually burns the entire
        // token budget before producing content.
        out = out.replace(/<style\b[^>]*>[\s\S]*?<\/style>/gi, '');

        // Handle a TRUNCATED <style> block (opening tag present, closing tag missing —
        // the LLM ran out of tokens mid-CSS). Drop everything from the <style> onward
        // so we don't inject raw CSS text into the DOM. stripos-style check: if an
        // opening <style> exists after all closed blocks were removed, everything
        // from it to the end is orphan CSS.
        var openStyleIdx = out.search(/<style\b/i);
        if (openStyleIdx !== -1) {
            out = out.slice(0, openStyleIdx);
        }

        // Handle CSS that leaked out of a <style> block entirely (the response STARTS
        // with CSS — no <style> opener survived the fence strip, but the body begins
        // with `/* … */` or `.some-class {`). If there's no `<` before the first
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
        // media URL — it typically hallucinates {{media url="something.jpg"}} that
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
                // Already has a span[data-element=link_text] — leave it alone.
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
        return out;
    }

    /**
     * Cheap tag balancer for LLM output that got cut off mid-element. We do NOT try
     * to be a full HTML parser — just walk the string, push opening tags for a
     * known set of block/inline elements, pop on close tags, and at the end append
     * closing tags for anything still on the stack in LIFO order. Handles the most
     * common truncation cases without rearranging content.
     *
     * Self-closing / void tags (img, br, hr, input, …) are ignored on both sides.
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
            // Self-closing syntax like <div ... /> — ignore.
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
     * Magento 2 PageBuilder does NOT persist live stage content in the DOM textarea —
     * it holds content in a Knockout observable on a uiRegistry component (e.g.
     * `cms_page_form.cms_page_form.content.content`). Setting textarea.value is
     * therefore insufficient: we must push the new HTML into that observable so the
     * stage re-renders immediately.
     *
     * Strategy (first match wins):
     *   1. uiRegistry — find a component whose config references PageBuilder and
     *      update its `value` observable. This triggers native re-render.
     *   2. Any registered component with a `value` observable AND a field named
     *      `content` / `description` / `short_description`.
     *   3. Plain textarea selectors (legacy wysiwyg / non-PageBuilder admin forms).
     */
    function insertIntoPageBuilder(htmlContent) {
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
                // Registry didn't find a component — fall back to textarea.
                if (!tryInjectViaTextarea(clean)) {
                    showInjectionBanner(false);
                }
            }
        });

        if (!handledByRegistry) {
            // require/uiRegistry not available at all — go straight to textarea path.
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
     * `pageBuilder.stage.masterFormat()` — NOT the `value` observable. So to both
     * (a) re-render the stage visually, and (b) have the new content actually
     * persist when the admin clicks Save, we have to replace the stage's content
     * type tree via `Magento_PageBuilder/js/stage-builder`.
     *
     * `stage-builder` takes a stage instance and an HTML string, parses it into
     * a tree of content-type components, and attaches them under the stage's
     * root-container. Everything (drag handles, edit buttons, master-format
     * serialization) then works natively.
     *
     * The callback receives a boolean — true if at least one stage was rebuilt.
     */
    function tryInjectViaUiRegistry(clean, callback) {
        if (typeof require !== 'function') return false;

        require(['uiRegistry', 'Magento_PageBuilder/js/stage-builder'], function (registry, stageBuilder) {
            var wysiwygs = [];
            try {
                wysiwygs = registry.filter(function (item) {
                    return item && item.pageBuilder && item.pageBuilder.stage;
                }) || [];
            } catch (e) { /* registry.filter missing — empty array keeps us in fallback */ }

            if (wysiwygs.length === 0) {
                callback(tryFallbackValueInjection(registry, clean));
                return;
            }

            var rebuilt = 0;
            var pending = wysiwygs.length;
            var settle = function () { if (--pending === 0) callback(rebuilt > 0); };

            // Helper: clear the stage root children before each stage-builder attempt.
            var clearRoot = function (stage) {
                try {
                    var rootContainer = stage.rootContainer || stage.parent;
                    if (rootContainer && rootContainer.children && typeof rootContainer.children.removeAll === 'function') {
                        rootContainer.children.removeAll();
                    }
                } catch (e) { /* ignore — rebuild will replace children anyway */ }
            };

            // Helper: secondary attempt — wrap the whole payload in a single
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
                                   // Final fallback — write value() observable only.
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
                        // Synchronous API (older signature) — treat as success.
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
            // stage-builder module missing — fall back to pure value() observable injection.
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
     * Plain-textarea fallback — legacy wysiwyg forms and any hidden wysiwyg textarea
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

    function showInjectionBanner(ok, customMessage) {
        // Remove any previous banner so repeated generations don't stack.
        var existing = document.getElementById('panth-pb-ai-banner');
        if (existing) existing.remove();

        var bar = document.createElement('div');
        bar.id = 'panth-pb-ai-banner';
        bar.style.cssText =
            'position:fixed;top:20px;right:20px;z-index:999999;max-width:420px;' +
            'padding:14px 18px 14px 16px;border-radius:4px;font-family:inherit;font-size:13px;' +
            'box-shadow:0 4px 14px rgba(0,0,0,.18);line-height:1.45;' +
            (ok
                ? 'background:#eaf7e6;border-left:4px solid #3fae2a;color:#1a5a0a;'
                : 'background:#fbeaea;border-left:4px solid #c00;color:#5a0a0a;');

        var dismissColor = ok ? '#1a5a0a' : '#5a0a0a';
        var dismissHtml = '<div style="margin-top:8px;"><button type="button" style="background:transparent;border:0;color:' + dismissColor + ';cursor:pointer;text-decoration:underline;padding:0;font:inherit;">Dismiss</button></div>';

        if (customMessage && typeof customMessage === 'string') {
            // Escape angle brackets so a bad upstream message can't inject markup.
            var safe = customMessage.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            bar.innerHTML = '<strong>' + (ok ? 'AI content applied.' : 'Could not apply AI content.') + '</strong><br>' + safe + dismissHtml;
        } else {
            bar.innerHTML = ok
                ? '<strong>AI content applied.</strong><br>' +
                  'The HTML has been written to the PageBuilder content field. ' +
                  'If the stage does not update visually, click <em>Save</em> then reopen the page — ' +
                  'the content is persisted and will render as editable PageBuilder blocks.' +
                  dismissHtml
                : '<strong>Could not find PageBuilder stage.</strong><br>' +
                  'No content textarea was detected on this page. ' +
                  'Open a CMS Page / Block / Product / Category first, then try again.' +
                  dismissHtml;
        }

        document.body.appendChild(bar);
        var btn = bar.querySelector('button');
        if (btn) btn.onclick = function () { bar.remove(); };
        setTimeout(function () { if (bar.parentNode) bar.remove(); }, 12000);
    }
})();
