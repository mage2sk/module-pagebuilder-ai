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

    function injectAiButtonsInEditForm(formNode) {
        // Find all textarea and input fields in the edit form
        var fields = formNode.querySelectorAll('textarea, input[type="text"]');
        fields.forEach(function(field) {
            if (field.dataset.panthAiInjected) return;
            field.dataset.panthAiInjected = '1';

            var fieldName = field.name || field.getAttribute('name') || '';
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
                var payload = {
                    form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
                    entity_type: entityType,
                    entity_id: entityId,
                    store_id: 0,
                    custom_prompt: prompt,
                    target_field: 'description'
                };

                // Always append form_key to URL (Magento admin requirement)
                var urlWithKey = generateUrl + (generateUrl.indexOf('?') > -1 ? '&' : '?') + 'form_key=' + encodeURIComponent(payload.form_key);
                var fetchOpts = { method: 'POST', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } };
                if (images.length > 0) {
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
        var defaultPrompt = 'Generate content for the "' + label + '" field. Write professional, SEO-optimized content. No emojis. Return only the content text, no JSON wrapping.';

        var dialog = createDialog('Generate: ' + label, function(result) {
            var value = '';
            if (result.data) {
                // Try to find the right field value
                value = result.data[Object.keys(result.data)[0]] || '';
            }
            if (!value && typeof result === 'string') {
                value = result;
            }
            if (value) {
                field.value = value;
                field.dispatchEvent(new Event('input', {bubbles: true}));
                field.dispatchEvent(new Event('change', {bubbles: true}));

                // For TinyMCE editors
                if (field.id && typeof tinyMCE !== 'undefined') {
                    var editor = tinyMCE.get(field.id);
                    if (editor) editor.setContent(value);
                }
            }
        }, defaultPrompt);
        document.body.appendChild(dialog.backdrop);
        document.body.appendChild(dialog.popup);
    }

    function createDialog(title, onSuccess, defaultPrompt) {
        defaultPrompt = defaultPrompt || 'Write professional, SEO-optimized content for this section. Use proper HTML formatting with <p>, <h2>, <ul>, <li> tags. Follow Google 2026 SEO best practices. No emojis.';

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

                generateContentWithImages(promptText, images, function(result) {
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

    function generateContentWithImages(prompt, images, callback) {
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

        var generateUrl = window.panthPageBuilderAiUrl || (window.BASE_URL + 'panth_seo/aigenerate/generate');

        var payload = {
            form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : '',
            entity_type: entityType,
            entity_id: entityId,
            store_id: 0,
            custom_prompt: prompt,
            target_field: 'description'
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
        var out = raw.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
        out = out.replace(/<(iframe|object|embed|form|meta|link)\b[^>]*>(?:[\s\S]*?<\/\1>)?/gi, '');
        out = out.replace(/\son[a-z]+\s*=\s*"[^"]*"/gi, '');
        out = out.replace(/\son[a-z]+\s*=\s*'[^']*'/gi, '');
        out = out.replace(/(href|src)\s*=\s*(["'])\s*(javascript|vbscript|file):[^"']*\2/gi, '$1=$2#$2');
        out = out.replace(/(href|src)\s*=\s*(["'])\s*data:(?!image\/)[^"']*\2/gi, '$1=$2#$2');
        return out;
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

    function insertIntoPageBuilder(htmlContent) {
        var clean = sanitizeLlmHtml(htmlContent);
        if (!/^\s*<div[^>]*data-content-type=["']row["']/i.test(clean)) {
            clean = wrapInPageBuilderBlock(clean);
        }

        var textarea = document.querySelector('textarea[data-role="stage"]')
            || document.querySelector('#cms_page_form_content textarea')
            || document.querySelector('textarea[name="content"]')
            || document.querySelector('textarea[name="product[description]"]')
            || document.querySelector('textarea[name="block[content]"]');

        if (textarea) {
            // Setting .value is safe — it's a string assignment, no HTML parsing / script exec.
            textarea.value = clean;
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            textarea.dispatchEvent(new Event('change', { bubbles: true }));

            // TinyMCE fallback for older admin forms.
            if (textarea.id && typeof tinyMCE !== 'undefined') {
                var editor = tinyMCE.get(textarea.id);
                if (editor) editor.setContent(clean);
            }

            // Custom event for any listeners that want to refresh the stage.
            document.dispatchEvent(new CustomEvent('panth:pagebuilder-ai:content-injected', {
                detail: { textareaId: textarea.id || null }
            }));

            if (window.console && typeof console.info === 'function') {
                console.info('[Panth PageBuilder AI] Content injected into stage textarea. ' +
                             'If the stage does not refresh automatically, click Save or close ' +
                             'and reopen the PageBuilder editor — the new content is persisted.');
            }
        } else if (window.console && console.warn) {
            console.warn('[Panth PageBuilder AI] No stage textarea found — content not injected.');
        }
    }
})();
