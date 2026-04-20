<?php
declare(strict_types=1);

/**
 * AI Knowledge Base: Web Accessibility (WCAG 2.2) & HTML/CSS Patterns
 *
 * Returns array of entries for panth_seo_ai_knowledge table.
 * Each entry: category, subcategory, title, content, tags, is_active, sort_order
 */

$entries = [];
$sort = 1000; // offset to avoid collision with existing entries

// =====================================================================
// CATEGORY: accessibility - WCAG 2.2 Compliance (15 entries)
// =====================================================================

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'wcag_level_a',
    'title' => 'WCAG 2.2 Level A - Must-Have Requirements',
    'content' => 'Level A is the minimum conformance level. Key requirements: 1) All non-text content must have text alternatives (alt attributes on images). 2) All video/audio must have captions or transcripts. 3) Content must be navigable by keyboard alone. 4) No content should flash more than 3 times per second. 5) Pages must have descriptive titles. 6) Link purpose must be determinable from link text. Example image with alt text: <img src="product.jpg" alt="Red cotton crew-neck t-shirt, front view" width="400" height="500" loading="lazy">. Example descriptive link: <a href="/returns">View our return policy</a> instead of <a href="/returns">Click here</a>. Every form input must have a programmatically associated label: <label for="email">Email address</label><input type="email" id="email" name="email" required>.',
    'tags' => 'wcag, level-a, accessibility, alt-text, keyboard, conformance, minimum',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'wcag_level_aa',
    'title' => 'WCAG 2.2 Level AA - Should-Have Requirements',
    'content' => 'Level AA is the target for most websites and legal compliance. Key requirements include: 1) Color contrast ratio of at least 4.5:1 for normal text and 3:1 for large text (18pt+ or 14pt+ bold). 2) Text can be resized up to 200% without loss of content. 3) Multiple ways to find pages (search, sitemap, navigation). 4) Consistent navigation across pages. 5) Error identification and suggestions in forms. 6) Visible focus indicators on interactive elements. 7) Content reflows at 320px width without horizontal scroll. 8) Non-text contrast of 3:1 for UI components and graphics. Example focus style: a:focus, button:focus { outline: 3px solid #1a73e8; outline-offset: 2px; } Example reflow meta tag: <meta name="viewport" content="width=device-width, initial-scale=1">.',
    'tags' => 'wcag, level-aa, accessibility, contrast, reflow, focus, compliance',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'color_contrast',
    'title' => 'Color Contrast Ratios - WCAG Requirements',
    'content' => 'WCAG 2.2 color contrast requirements: Normal text (<18pt or <14pt bold) needs 4.5:1 ratio. Large text (>=18pt or >=14pt bold) needs 3:1 ratio. Non-text elements (icons, borders, focus indicators) need 3:1 ratio. CSS implementation for accessible colors: :root { --color-text-primary: #1a1a1a; /* 16:1 on white */ --color-text-secondary: #545454; /* 7.4:1 on white */ --color-link: #0056b3; /* 7.2:1 on white */ --color-error: #c7254e; /* 6.1:1 on white */ --color-success: #1e7e34; /* 4.8:1 on white */ --color-border: #767676; /* 4.5:1 on white - minimum for non-text */ } Avoid using color as the only indicator: .required-field label::after { content: " *"; color: var(--color-error); } .error-message { color: var(--color-error); } .error-message::before { content: "\\26A0 "; /* warning icon as additional indicator */ }',
    'tags' => 'wcag, contrast, color, ratio, 4.5, 3.1, accessibility, css-variables',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'keyboard_navigation',
    'title' => 'Keyboard Navigation Requirements',
    'content' => 'All functionality must be operable via keyboard. Key patterns: 1) Tab moves focus forward, Shift+Tab moves backward. 2) Enter/Space activates buttons and links. 3) Arrow keys navigate within composite widgets (tabs, menus, radio groups). 4) Escape closes modals and dropdowns. 5) No keyboard traps - user must always be able to Tab away. Example accessible dropdown menu: <nav aria-label="Main navigation"><ul role="menubar"><li role="none"><a role="menuitem" href="/products" aria-haspopup="true" aria-expanded="false">Products</a><ul role="menu" aria-label="Products submenu"><li role="none"><a role="menuitem" href="/products/new">New Arrivals</a></li></ul></li></ul></nav>. Focus trap for modals (allow Tab cycling within modal only): dialog.addEventListener("keydown", (e) => { if (e.key === "Tab") { /* cycle focus within modal elements */ } if (e.key === "Escape") { closeModal(); } });',
    'tags' => 'keyboard, navigation, tab, focus, trap, modal, menu, accessibility',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'screen_reader',
    'title' => 'Screen Reader Optimization',
    'content' => 'Optimize content for screen readers (JAWS, NVDA, VoiceOver). Key practices: 1) Use visually-hidden class for screen-reader-only text: .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; } 2) Use aria-live regions for dynamic content: <div aria-live="polite" aria-atomic="true" class="sr-only" id="cart-status">3 items in cart</div>. 3) Use aria-describedby for supplementary info: <input type="password" id="pwd" aria-describedby="pwd-help"><p id="pwd-help">Must be at least 8 characters with one number.</p>. 4) Hide decorative elements: <img src="divider.svg" alt="" role="presentation">. 5) Announce page changes in SPAs: <div role="status" aria-live="polite" id="page-announcer"></div>. 6) Use aria-current="page" on active navigation links.',
    'tags' => 'screen-reader, aria-live, sr-only, voiceover, jaws, nvda, accessibility',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'focus_management',
    'title' => 'Focus Management Best Practices',
    'content' => 'Focus management ensures keyboard users know where they are on the page. Rules: 1) Never remove outline without replacement: /* BAD: */ :focus { outline: none; } /* GOOD: */ :focus-visible { outline: 3px solid #1a73e8; outline-offset: 2px; border-radius: 2px; } 2) Move focus to new content: after opening a modal, focus the first focusable element or the modal heading; after closing, return focus to the trigger element. 3) Use tabindex appropriately: tabindex="0" adds element to tab order; tabindex="-1" makes it focusable via JavaScript only (for focus management); never use tabindex > 0. 4) Example modal focus management: const openModal = (triggerEl) => { previousFocus = triggerEl; modal.style.display = "block"; modal.querySelector("[autofocus], h2, button").focus(); }; const closeModal = () => { modal.style.display = "none"; previousFocus.focus(); };',
    'tags' => 'focus, outline, tabindex, modal, focus-visible, keyboard, accessibility',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'aria_landmarks',
    'title' => 'ARIA Roles and Landmarks',
    'content' => 'ARIA landmarks provide navigation structure for assistive technologies. Use semantic HTML first, ARIA as supplement. Page structure: <header role="banner"> (one per page), <nav role="navigation" aria-label="Main">, <main role="main"> (one per page), <aside role="complementary">, <footer role="contentinfo">. Additional ARIA roles: <form role="search" aria-label="Site search">, <section role="region" aria-labelledby="section-heading">. Full page skeleton: <!DOCTYPE html><html lang="en"><head><title>Page Title | Store Name</title></head><body><a href="#main-content" class="skip-link">Skip to main content</a><header><nav aria-label="Main navigation">...</nav></header><main id="main-content"><h1>Page Title</h1>...</main><aside aria-label="Sidebar">...</aside><footer><nav aria-label="Footer navigation">...</nav></footer></body></html>. Rule: Prefer native HTML elements over ARIA. <button> is better than <div role="button">.',
    'tags' => 'aria, landmarks, roles, banner, navigation, main, complementary, contentinfo',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'form_accessibility',
    'title' => 'Form Accessibility - Labels, Errors, Required Fields',
    'content' => 'Every form control needs a programmatic label. Patterns: 1) Explicit label: <label for="fname">First name <span aria-hidden="true">*</span></label><input type="text" id="fname" name="fname" required aria-required="true">. 2) Group related fields: <fieldset><legend>Shipping Address</legend><label for="street">Street</label><input id="street" name="street"></fieldset>. 3) Error handling: <label for="email">Email</label><input type="email" id="email" name="email" required aria-invalid="true" aria-describedby="email-error"><p id="email-error" role="alert" class="error-msg">Please enter a valid email address.</p>. 4) Error summary at form top: <div role="alert" aria-labelledby="error-summary-title"><h2 id="error-summary-title">There are 2 errors in this form</h2><ul><li><a href="#email">Email address is required</a></li><li><a href="#phone">Phone number format is invalid</a></li></ul></div>. 5) Use autocomplete attributes: <input type="text" name="fname" autocomplete="given-name">.',
    'tags' => 'form, label, error, required, fieldset, legend, aria-invalid, autocomplete, accessibility',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'table_accessibility',
    'title' => 'Table Accessibility - Headers, Scope, Caption',
    'content' => 'Data tables must be properly structured for screen readers. Key requirements: 1) Always include caption or aria-labelledby: <table><caption>Product Specifications for Model X</caption>...</table>. 2) Use th with scope for headers: <thead><tr><th scope="col">Feature</th><th scope="col">Basic</th><th scope="col">Pro</th></tr></thead>. 3) Row headers: <tr><th scope="row">Storage</th><td>64GB</td><td>256GB</td></tr>. 4) Complex tables with headers attribute: <th id="size">Size</th><th id="color-red">Red</th><td headers="size color-red">In Stock</td>. 5) Never use tables for layout. 6) Responsive approach - do not hide data, restructure: @media (max-width: 768px) { table, thead, tbody, tr, td, th { display: block; } thead { position: absolute; clip: rect(0,0,0,0); } td::before { content: attr(data-label); font-weight: bold; } }',
    'tags' => 'table, th, scope, caption, headers, responsive, accessibility',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'media_accessibility',
    'title' => 'Media Accessibility - Captions, Transcripts, Audio Descriptions',
    'content' => 'All media must be accessible. Requirements: 1) Video with captions: <video controls><source src="product-demo.mp4" type="video/mp4"><track kind="captions" src="demo-en.vtt" srclang="en" label="English" default><track kind="captions" src="demo-es.vtt" srclang="es" label="Spanish">Your browser does not support video.</video>. 2) Audio with transcript: <audio controls aria-describedby="audio-transcript"><source src="podcast.mp3" type="audio/mpeg"></audio><details id="audio-transcript"><summary>View transcript</summary><div class="transcript">...</div></details>. 3) Auto-playing media must have pause control and no audio: <video autoplay muted loop playsinline aria-label="Background ambience video"><source src="bg.mp4" type="video/mp4"></video><button aria-label="Pause background video" onclick="toggleVideo()">Pause</button>. 4) Provide audio descriptions for visual-only video information.',
    'tags' => 'video, audio, captions, transcript, track, vtt, media, accessibility',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'touch_target',
    'title' => 'Touch Target Size - 44x44px Minimum (WCAG 2.5.8)',
    'content' => 'WCAG 2.2 Success Criterion 2.5.8 (Target Size Minimum) requires interactive targets to be at least 24x24 CSS pixels, with 44x44px recommended for mobile. Implementation: .btn, a.nav-link, input[type="checkbox"] + label, input[type="radio"] + label { min-width: 44px; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 12px 16px; } /* Ensure spacing between small targets */ .icon-btn { min-width: 44px; min-height: 44px; padding: 10px; } /* Custom checkbox with adequate target */ .custom-checkbox { position: relative; } .custom-checkbox input { position: absolute; opacity: 0; width: 44px; height: 44px; cursor: pointer; } .custom-checkbox label { padding-left: 32px; min-height: 44px; display: flex; align-items: center; } /* Inline links within text are exempt from this requirement */.',
    'tags' => 'touch-target, 44px, mobile, tap, target-size, wcag-2.5.8, accessibility',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'motion_preferences',
    'title' => 'Motion/Animation Preferences - prefers-reduced-motion',
    'content' => 'Respect user motion preferences to prevent vestibular disorders. WCAG 2.3.3 (Animation from Interactions). CSS implementation: /* Default: include animations */ .hero-banner { animation: fadeSlideIn 0.6s ease-out; } .card { transition: transform 0.3s ease, box-shadow 0.3s ease; } .card:hover { transform: translateY(-4px); } /* Reduced motion: disable or simplify */ @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; scroll-behavior: auto !important; } .card:hover { transform: none; box-shadow: 0 0 0 3px var(--color-focus); } } /* JavaScript check */ const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches; if (!prefersReducedMotion) { startCarouselAutoplay(); }',
    'tags' => 'motion, animation, prefers-reduced-motion, vestibular, transition, accessibility',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'text_spacing',
    'title' => 'Text Spacing Requirements - WCAG 1.4.12',
    'content' => 'WCAG 1.4.12 (Text Spacing) requires content to remain readable when users adjust spacing. Must work with: line-height 1.5x font size, paragraph spacing 2x font size, letter-spacing 0.12em, word-spacing 0.16em. CSS implementation: /* Base styles that accommodate text spacing */ body { line-height: 1.6; /* already exceeds 1.5x */ } p { margin-bottom: 1.5em; } /* Do NOT set fixed heights on text containers */ .card-body { min-height: auto; /* NOT height: 200px */ overflow: visible; /* NOT overflow: hidden on text containers */ } /* Test your styles with these overrides */ .text-spacing-test { line-height: 1.5em !important; letter-spacing: 0.12em !important; word-spacing: 0.16em !important; } .text-spacing-test p { margin-bottom: 2em !important; } /* Avoid clipping text with overflow: hidden on text containers */.',
    'tags' => 'text-spacing, line-height, letter-spacing, word-spacing, wcag-1.4.12, accessibility',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'language_attribute',
    'title' => 'Language Attribute for Accessibility',
    'content' => 'The lang attribute helps screen readers pronounce content correctly. WCAG 3.1.1 (Language of Page) and 3.1.2 (Language of Parts). Implementation: 1) Always set page language: <html lang="en">. 2) Mark language changes inline: <p>Our motto is <span lang="fr">joie de vivre</span>.</p>. 3) Common lang values: en (English), en-US, en-GB, es (Spanish), fr (French), de (German), it (Italian), pt (Portuguese), ja (Japanese), zh (Chinese), ar (Arabic), he (Hebrew). 4) For RTL languages, combine with dir attribute: <html lang="ar" dir="rtl">. 5) Magento multi-store example: set lang dynamically based on store locale in the root template: <html lang="<?= $block->escapeHtmlAttr(strstr($locale, "_", true)) ?>">.  6) For product pages with multi-language descriptions: <div lang="es" class="product-description-es">Descripcion del producto...</div>.',
    'tags' => 'lang, language, html, screen-reader, rtl, locale, i18n, accessibility',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'accessibility',
    'subcategory' => 'skip_navigation',
    'title' => 'Skip Navigation Links',
    'content' => 'Skip links allow keyboard users to bypass repetitive navigation. WCAG 2.4.1 (Bypass Blocks). Implementation: Place as first focusable element in body: <body><a href="#main-content" class="skip-link">Skip to main content</a><a href="#footer-nav" class="skip-link">Skip to footer</a><header>...</header><main id="main-content" tabindex="-1">...</main></body>. CSS for skip link: .skip-link { position: absolute; top: -100%; left: 16px; z-index: 10000; padding: 12px 24px; background: #1a1a1a; color: #ffffff; font-size: 1rem; font-weight: 600; text-decoration: none; border-radius: 0 0 4px 4px; transition: top 0.2s; } .skip-link:focus { top: 0; outline: 3px solid #1a73e8; outline-offset: 2px; } /* Additional skip links for complex pages */ <a href="#search" class="skip-link">Skip to search</a> <a href="#product-info" class="skip-link">Skip to product information</a>.',
    'tags' => 'skip-link, navigation, bypass, keyboard, focus, wcag-2.4.1, accessibility',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =====================================================================
// CATEGORY: html_patterns - HTML Content Patterns (20 entries)
// =====================================================================

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'comparison_table',
    'title' => 'Feature Comparison Table - Responsive and Accessible',
    'content' => 'Accessible, responsive feature comparison table for products or plans. HTML: <div class="comparison-wrapper" role="region" aria-labelledby="compare-heading" tabindex="0"><h2 id="compare-heading">Compare Plans</h2><table class="comparison-table"><caption class="sr-only">Feature comparison between Basic, Pro, and Enterprise plans</caption><thead><tr><th scope="col">Feature</th><th scope="col">Basic<br><span class="price">$9/mo</span></th><th scope="col">Pro<br><span class="price">$29/mo</span></th><th scope="col">Enterprise<br><span class="price">$99/mo</span></th></tr></thead><tbody><tr><th scope="row">Storage</th><td>10 GB</td><td>100 GB</td><td>Unlimited</td></tr><tr><th scope="row">Users</th><td>1</td><td>5</td><td>Unlimited</td></tr><tr><th scope="row">24/7 Support</th><td><span aria-label="Not included">&#10005;</span></td><td><span aria-label="Included">&#10003;</span></td><td><span aria-label="Included">&#10003;</span></td></tr></tbody></table></div>. CSS: .comparison-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; } .comparison-table { width: 100%; border-collapse: collapse; } .comparison-table th, .comparison-table td { padding: 12px 16px; border: 1px solid #e0e0e0; text-align: center; } .comparison-table thead th { background: #f5f5f5; position: sticky; top: 0; } @media (max-width: 640px) { .comparison-table { font-size: 0.875rem; } .comparison-table th, .comparison-table td { padding: 8px; } }',
    'tags' => 'comparison, table, plans, pricing, responsive, accessible, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'specification_table',
    'title' => 'Product Specification Table',
    'content' => 'Structured product spec table with grouped attributes. HTML: <div class="product-specs" role="region" aria-labelledby="specs-heading"><h2 id="specs-heading">Specifications</h2><table class="spec-table"><caption class="sr-only">Detailed specifications for Product Name</caption><tbody><tr class="spec-group-header"><th colspan="2" scope="colgroup">Dimensions &amp; Weight</th></tr><tr><th scope="row">Width</th><td>14.2 inches (36.1 cm)</td></tr><tr><th scope="row">Height</th><td>9.8 inches (24.9 cm)</td></tr><tr><th scope="row">Weight</th><td>3.5 lbs (1.59 kg)</td></tr><tr class="spec-group-header"><th colspan="2" scope="colgroup">Performance</th></tr><tr><th scope="row">Processor</th><td>M2 Pro chip</td></tr><tr><th scope="row">Memory</th><td>16 GB unified</td></tr></tbody></table></div>. CSS: .spec-table { width: 100%; border-collapse: collapse; } .spec-table th, .spec-table td { padding: 10px 16px; border-bottom: 1px solid #e0e0e0; text-align: left; } .spec-group-header th { background: #f8f9fa; font-size: 1.1rem; padding-top: 20px; } .spec-table tr:nth-child(even):not(.spec-group-header) { background: #fafafa; }',
    'tags' => 'product, specifications, table, attributes, grouped, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'faq_accordion',
    'title' => 'FAQ Accordion with Schema Markup',
    'content' => 'Accessible FAQ accordion with FAQPage structured data. HTML: <section aria-labelledby="faq-heading" itemscope itemtype="https://schema.org/FAQPage"><h2 id="faq-heading">Frequently Asked Questions</h2><div class="faq-list"><div class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"><h3><button aria-expanded="false" aria-controls="faq-1-answer" class="faq-trigger" id="faq-1"><span itemprop="name">What is your return policy?</span><svg aria-hidden="true" class="faq-icon" viewBox="0 0 24 24" width="24" height="24"><path d="M6 9l6 6 6-6"/></svg></button></h3><div id="faq-1-answer" role="region" aria-labelledby="faq-1" hidden itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer"><div itemprop="text"><p>You can return any item within 30 days of purchase for a full refund.</p></div></div></div></div></section>. JavaScript: document.querySelectorAll(".faq-trigger").forEach(btn => { btn.addEventListener("click", () => { const expanded = btn.getAttribute("aria-expanded") === "true"; btn.setAttribute("aria-expanded", !expanded); const panel = document.getElementById(btn.getAttribute("aria-controls")); panel.hidden = expanded; }); });',
    'tags' => 'faq, accordion, schema, structured-data, faqpage, accessible, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'testimonial_card',
    'title' => 'Testimonial/Review Card',
    'content' => 'Accessible testimonial card with star rating and schema markup. HTML: <article class="testimonial-card" itemscope itemtype="https://schema.org/Review"><div class="testimonial-rating" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating"><meta itemprop="ratingValue" content="5"><meta itemprop="bestRating" content="5"><div role="img" aria-label="5 out of 5 stars" class="stars"><span aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span></div></div><blockquote itemprop="reviewBody"><p>&ldquo;Excellent quality and fast shipping. Would definitely recommend!&rdquo;</p></blockquote><footer class="testimonial-author"><cite itemprop="author" itemscope itemtype="https://schema.org/Person"><img src="avatar.jpg" alt="" width="48" height="48" class="testimonial-avatar" loading="lazy"><span itemprop="name">Jane Smith</span></cite><time itemprop="datePublished" datetime="2025-12-15">December 15, 2025</time></footer></article>. CSS: .testimonial-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; max-width: 480px; } .stars { color: #f5a623; font-size: 1.25rem; letter-spacing: 2px; } blockquote { margin: 16px 0; font-style: italic; line-height: 1.6; } .testimonial-author { display: flex; align-items: center; gap: 12px; } .testimonial-avatar { border-radius: 50%; }',
    'tags' => 'testimonial, review, card, stars, rating, schema, blockquote, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'team_grid',
    'title' => 'Team Member Grid',
    'content' => 'Responsive team member grid with accessible cards. HTML: <section aria-labelledby="team-heading"><h2 id="team-heading">Our Team</h2><ul class="team-grid" role="list"><li class="team-member"><img src="member1.jpg" alt="Portrait of Sarah Johnson" width="300" height="300" loading="lazy"><h3>Sarah Johnson</h3><p class="team-role">CEO &amp; Founder</p><p class="team-bio">15 years of experience in e-commerce.</p><ul class="team-social" aria-label="Sarah Johnson social links"><li><a href="https://linkedin.com/in/sarah" aria-label="Sarah Johnson on LinkedIn"><svg aria-hidden="true" width="24" height="24">...</svg></a></li><li><a href="mailto:sarah@example.com" aria-label="Email Sarah Johnson"><svg aria-hidden="true" width="24" height="24">...</svg></a></li></ul></li></ul></section>. CSS: .team-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 32px; list-style: none; padding: 0; } .team-member { text-align: center; } .team-member img { border-radius: 50%; width: 150px; height: 150px; object-fit: cover; } .team-role { color: #666; font-style: italic; } .team-social { display: flex; justify-content: center; gap: 16px; list-style: none; padding: 0; }',
    'tags' => 'team, grid, members, cards, responsive, social, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'pricing_table',
    'title' => 'Pricing Table with Accessible Markup',
    'content' => 'Pricing cards with clear hierarchy and screen reader support. HTML: <section aria-labelledby="pricing-heading"><h2 id="pricing-heading">Choose Your Plan</h2><div class="pricing-grid" role="list"><div class="pricing-card" role="listitem"><div class="pricing-header"><h3>Basic</h3><p class="pricing-price"><span class="sr-only">Price: </span><span class="price-amount">$19</span><span class="price-period">/month</span></p></div><ul class="pricing-features" aria-label="Basic plan features"><li><span aria-hidden="true">&#10003;</span> 5 Projects</li><li><span aria-hidden="true">&#10003;</span> 10 GB Storage</li><li><span aria-hidden="true">&#10005;</span> <span class="feature-unavailable">Priority Support<span class="sr-only"> - not included</span></span></li></ul><a href="/signup/basic" class="btn btn-outline">Get Started</a></div><div class="pricing-card pricing-featured" role="listitem" aria-label="Most popular plan"><div class="pricing-badge">Most Popular</div><div class="pricing-header"><h3>Pro</h3><p class="pricing-price"><span class="sr-only">Price: </span><span class="price-amount">$49</span><span class="price-period">/month</span></p></div><ul class="pricing-features" aria-label="Pro plan features"><li><span aria-hidden="true">&#10003;</span> Unlimited Projects</li><li><span aria-hidden="true">&#10003;</span> 100 GB Storage</li><li><span aria-hidden="true">&#10003;</span> Priority Support</li></ul><a href="/signup/pro" class="btn btn-primary">Get Started</a></div></div></section>. CSS: .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; align-items: start; } .pricing-featured { border: 2px solid #1a73e8; position: relative; } .pricing-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #1a73e8; color: #fff; padding: 4px 16px; border-radius: 12px; font-size: 0.85rem; } .feature-unavailable { text-decoration: line-through; opacity: 0.6; }',
    'tags' => 'pricing, table, plans, cards, featured, accessible, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'howto_guide',
    'title' => 'Step-by-Step How-To Guide with Schema',
    'content' => 'Accessible how-to guide with HowTo structured data. HTML: <article itemscope itemtype="https://schema.org/HowTo"><h2 itemprop="name">How to Measure Yourself for the Perfect Fit</h2><meta itemprop="totalTime" content="PT5M"><p itemprop="description">Follow these 4 simple steps to find your ideal size.</p><div itemprop="supply" itemscope itemtype="https://schema.org/HowToSupply"><span itemprop="name">Flexible measuring tape</span></div><ol class="howto-steps"><li itemprop="step" itemscope itemtype="https://schema.org/HowToStep"><div class="step-number" aria-hidden="true">1</div><h3 itemprop="name">Measure Your Chest</h3><div itemprop="text"><p>Wrap the measuring tape around the fullest part of your chest, keeping it level.</p></div><img itemprop="image" src="step1.jpg" alt="Person measuring chest circumference with tape measure" width="400" height="300" loading="lazy"></li><li itemprop="step" itemscope itemtype="https://schema.org/HowToStep"><div class="step-number" aria-hidden="true">2</div><h3 itemprop="name">Measure Your Waist</h3><div itemprop="text"><p>Measure around your natural waistline, at the narrowest point.</p></div></li></ol></article>. CSS: .howto-steps { counter-reset: step; list-style: none; padding: 0; } .howto-steps li { display: flex; gap: 20px; margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid #eee; } .step-number { flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; background: #1a73e8; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; }',
    'tags' => 'howto, steps, guide, schema, structured-data, instructions, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'newsletter_form',
    'title' => 'Newsletter Signup Form',
    'content' => 'Accessible newsletter signup with validation and GDPR consent. HTML: <section class="newsletter-section" aria-labelledby="newsletter-heading"><h2 id="newsletter-heading">Stay Updated</h2><p>Get 10% off your first order when you subscribe to our newsletter.</p><form action="/newsletter/subscribe" method="post" class="newsletter-form" novalidate><div class="form-group"><label for="newsletter-email">Email address</label><div class="input-group"><input type="email" id="newsletter-email" name="email" required aria-required="true" aria-describedby="email-hint" placeholder="you@example.com" autocomplete="email"><button type="submit" class="btn btn-primary">Subscribe</button></div><p id="email-hint" class="form-hint">We respect your privacy. Unsubscribe anytime.</p></div><div class="form-group"><input type="checkbox" id="newsletter-consent" name="consent" required aria-required="true"><label for="newsletter-consent">I agree to receive marketing emails and accept the <a href="/privacy">privacy policy</a>.</label></div><div aria-live="polite" id="newsletter-status"></div></form></section>. CSS: .newsletter-section { background: #f8f9fa; padding: 40px 24px; text-align: center; border-radius: 8px; } .input-group { display: flex; max-width: 480px; margin: 0 auto; } .input-group input { flex: 1; border: 2px solid #ccc; border-right: none; border-radius: 4px 0 0 4px; padding: 12px 16px; font-size: 1rem; } .input-group input:focus { border-color: #1a73e8; outline: none; box-shadow: 0 0 0 3px rgba(26,115,232,0.3); } .input-group button { border-radius: 0 4px 4px 0; white-space: nowrap; }',
    'tags' => 'newsletter, signup, form, email, subscribe, gdpr, consent, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'social_proof',
    'title' => 'Social Proof Section - Logos and Badges',
    'content' => 'Trust-building social proof section with partner logos and stats. HTML: <section class="social-proof" aria-labelledby="social-proof-heading"><h2 id="social-proof-heading" class="sr-only">Trusted by leading companies</h2><div class="proof-stats" role="list"><div role="listitem" class="stat-item"><span class="stat-number" aria-hidden="true">50K+</span><span class="sr-only">Over 50,000</span><span class="stat-label">Happy Customers</span></div><div role="listitem" class="stat-item"><span class="stat-number" aria-hidden="true">4.9/5</span><span class="sr-only">4.9 out of 5</span><span class="stat-label">Average Rating</span></div><div role="listitem" class="stat-item"><span class="stat-number" aria-hidden="true">99%</span><span class="stat-label">Satisfaction Rate</span></div></div><div class="logo-strip"><p class="logo-strip-title">As featured in</p><ul class="logo-list" role="list" aria-label="Featured in publications"><li><img src="logo-forbes.svg" alt="Forbes" width="120" height="32" loading="lazy"></li><li><img src="logo-techcrunch.svg" alt="TechCrunch" width="140" height="32" loading="lazy"></li></ul></div></section>. CSS: .proof-stats { display: flex; justify-content: center; gap: 48px; flex-wrap: wrap; margin-bottom: 40px; } .stat-number { display: block; font-size: 2.5rem; font-weight: 700; color: #1a73e8; } .logo-list { display: flex; align-items: center; justify-content: center; gap: 40px; flex-wrap: wrap; list-style: none; padding: 0; } .logo-list img { filter: grayscale(100%); opacity: 0.6; transition: opacity 0.3s; } .logo-list img:hover { opacity: 1; filter: none; }',
    'tags' => 'social-proof, logos, stats, trust, badges, featured, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'trust_badges',
    'title' => 'Trust Badges and Security Icons Section',
    'content' => 'Security and trust indicators for e-commerce checkout and product pages. HTML: <section class="trust-badges" aria-labelledby="trust-heading"><h2 id="trust-heading" class="sr-only">Our Guarantees</h2><ul class="badge-list" role="list"><li class="badge-item"><svg aria-hidden="true" width="40" height="40" viewBox="0 0 40 40"><path d="M20 3l-15 8v10c0 9 6.5 17.3 15 19.5 8.5-2.2 15-10.5 15-19.5V11L20 3z" fill="#1e7e34"/></svg><span class="badge-text"><strong>Secure Checkout</strong><br>256-bit SSL Encryption</span></li><li class="badge-item"><svg aria-hidden="true" width="40" height="40" viewBox="0 0 40 40">...</svg><span class="badge-text"><strong>Free Returns</strong><br>30-Day Money Back</span></li><li class="badge-item"><svg aria-hidden="true" width="40" height="40" viewBox="0 0 40 40">...</svg><span class="badge-text"><strong>Free Shipping</strong><br>Orders Over $50</span></li><li class="badge-item"><svg aria-hidden="true" width="40" height="40" viewBox="0 0 40 40">...</svg><span class="badge-text"><strong>24/7 Support</strong><br>Chat, Email &amp; Phone</span></li></ul></section>. CSS: .badge-list { display: flex; justify-content: center; gap: 32px; flex-wrap: wrap; list-style: none; padding: 0; } .badge-item { display: flex; align-items: center; gap: 12px; } .badge-text { font-size: 0.875rem; line-height: 1.4; }',
    'tags' => 'trust, badges, security, ssl, guarantee, shipping, returns, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'shipping_info',
    'title' => 'Shipping and Delivery Information Block',
    'content' => 'Accessible shipping info block with delivery options. HTML: <section class="shipping-info" aria-labelledby="shipping-heading"><h2 id="shipping-heading">Shipping &amp; Delivery</h2><div class="shipping-options" role="list"><div class="shipping-option" role="listitem"><div class="shipping-icon"><svg aria-hidden="true" width="32" height="32">...</svg></div><h3>Standard Shipping</h3><p class="shipping-price">Free on orders over $50</p><p class="shipping-time">Delivery in 5-7 business days</p></div><div class="shipping-option" role="listitem"><div class="shipping-icon"><svg aria-hidden="true" width="32" height="32">...</svg></div><h3>Express Shipping</h3><p class="shipping-price">$12.99</p><p class="shipping-time">Delivery in 2-3 business days</p></div><div class="shipping-option" role="listitem"><div class="shipping-icon"><svg aria-hidden="true" width="32" height="32">...</svg></div><h3>Next Day Delivery</h3><p class="shipping-price">$24.99</p><p class="shipping-time">Order before 2 PM for next day</p></div></div><details class="shipping-details"><summary>View full shipping policy</summary><div><p>We ship to all 50 US states and select international destinations...</p></div></details></section>. CSS: .shipping-options { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; } .shipping-option { padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; text-align: center; } .shipping-price { font-weight: 700; color: #1a73e8; } .shipping-time { color: #666; font-size: 0.9rem; }',
    'tags' => 'shipping, delivery, options, ecommerce, info, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'size_guide',
    'title' => 'Size Guide Table',
    'content' => 'Responsive, accessible size guide with unit toggle. HTML: <section class="size-guide" aria-labelledby="size-guide-heading"><h2 id="size-guide-heading">Size Guide</h2><div class="unit-toggle" role="radiogroup" aria-label="Measurement unit"><button role="radio" aria-checked="true" class="active" data-unit="in">Inches</button><button role="radio" aria-checked="false" data-unit="cm">Centimeters</button></div><div class="table-wrapper" role="region" aria-labelledby="size-guide-heading" tabindex="0"><table class="size-table"><caption class="sr-only">Size measurements in inches</caption><thead><tr><th scope="col">Size</th><th scope="col">US</th><th scope="col">Chest</th><th scope="col">Waist</th><th scope="col">Hips</th></tr></thead><tbody><tr><th scope="row">S</th><td>4-6</td><td>34-35</td><td>26-27</td><td>36-37</td></tr><tr><th scope="row">M</th><td>8-10</td><td>36-37</td><td>28-29</td><td>38-39</td></tr><tr><th scope="row">L</th><td>12-14</td><td>38-40</td><td>30-32</td><td>40-42</td></tr><tr><th scope="row">XL</th><td>16-18</td><td>41-43</td><td>33-35</td><td>43-45</td></tr></tbody></table></div><p class="size-note">Not sure? Our <a href="/fit-finder">Fit Finder tool</a> can help you choose.</p></section>. CSS: .table-wrapper { overflow-x: auto; } .unit-toggle { display: flex; gap: 4px; margin-bottom: 16px; } .unit-toggle button { padding: 8px 16px; border: 1px solid #ccc; background: #fff; cursor: pointer; } .unit-toggle button.active, .unit-toggle button[aria-checked="true"] { background: #1a73e8; color: #fff; border-color: #1a73e8; }',
    'tags' => 'size-guide, table, measurements, responsive, toggle, clothing, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'color_swatches',
    'title' => 'Color Swatches Display',
    'content' => 'Accessible color swatch selector for product options. HTML: <fieldset class="color-swatches"><legend>Select Color: <span id="selected-color-name">Midnight Blue</span></legend><div class="swatch-list" role="radiogroup" aria-labelledby="color-legend"><label class="swatch-option"><input type="radio" name="color" value="midnight-blue" checked aria-label="Midnight Blue"><span class="swatch" style="background-color: #191970;" aria-hidden="true"></span><span class="swatch-label">Midnight Blue</span></label><label class="swatch-option"><input type="radio" name="color" value="forest-green" aria-label="Forest Green"><span class="swatch" style="background-color: #228B22;" aria-hidden="true"></span><span class="swatch-label">Forest Green</span></label><label class="swatch-option unavailable"><input type="radio" name="color" value="ruby-red" aria-label="Ruby Red - Out of stock" disabled><span class="swatch" style="background-color: #9B111E;" aria-hidden="true"></span><span class="swatch-label">Ruby Red</span><span class="sr-only">Out of stock</span></label></div></fieldset>. CSS: .swatch-list { display: flex; gap: 12px; flex-wrap: wrap; } .swatch-option { position: relative; cursor: pointer; } .swatch-option input { position: absolute; opacity: 0; width: 40px; height: 40px; } .swatch { display: block; width: 40px; height: 40px; border-radius: 50%; border: 2px solid #ddd; } .swatch-option input:checked + .swatch { border-color: #1a1a1a; box-shadow: 0 0 0 2px #fff, 0 0 0 4px #1a1a1a; } .swatch-option input:focus-visible + .swatch { outline: 3px solid #1a73e8; outline-offset: 2px; } .unavailable .swatch { opacity: 0.4; } .unavailable .swatch::after { content: ""; position: absolute; width: 2px; height: 40px; background: #999; transform: rotate(45deg); top: 0; left: 19px; }',
    'tags' => 'color, swatches, selector, product, options, accessible, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'countdown_timer',
    'title' => 'Countdown Timer for Sales',
    'content' => 'Accessible countdown timer that respects reduced motion. HTML: <div class="sale-countdown" role="timer" aria-live="polite" aria-atomic="true" aria-label="Sale ends in"><p class="countdown-label">Flash Sale Ends In:</p><div class="countdown-digits"><div class="countdown-unit"><span class="countdown-value" id="cd-hours">08</span><span class="countdown-text">Hours</span></div><span class="countdown-sep" aria-hidden="true">:</span><div class="countdown-unit"><span class="countdown-value" id="cd-minutes">42</span><span class="countdown-text">Minutes</span></div><span class="countdown-sep" aria-hidden="true">:</span><div class="countdown-unit"><span class="countdown-value" id="cd-seconds">15</span><span class="countdown-text">Seconds</span></div></div><p class="sr-only" id="countdown-sr">Sale ends in 8 hours, 42 minutes, and 15 seconds</p></div>. JavaScript: Only update aria-live region every 60 seconds to avoid screen reader spam: let srUpdateInterval = 0; function updateCountdown() { /* update visual digits every second */ srUpdateInterval++; if (srUpdateInterval >= 60) { document.getElementById("countdown-sr").textContent = buildSrText(); srUpdateInterval = 0; } } setInterval(updateCountdown, 1000);. CSS: .countdown-digits { display: flex; align-items: center; gap: 8px; } .countdown-value { font-size: 2.5rem; font-weight: 700; font-variant-numeric: tabular-nums; background: #1a1a1a; color: #fff; padding: 8px 16px; border-radius: 4px; } @media (prefers-reduced-motion: reduce) { .countdown-value { animation: none; } }',
    'tags' => 'countdown, timer, sale, flash, urgency, accessible, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'product_bundle',
    'title' => 'Product Bundle/Kit Display',
    'content' => 'Accessible product bundle display with savings calculation. HTML: <section class="product-bundle" aria-labelledby="bundle-heading"><h2 id="bundle-heading">Complete the Look</h2><div class="bundle-items"><fieldset><legend>Select items for your bundle (minimum 2)</legend><ul class="bundle-list" role="list"><li class="bundle-item"><label class="bundle-selector"><input type="checkbox" name="bundle[]" value="shirt-1" checked aria-describedby="bundle-item-1-price"><img src="shirt.jpg" alt="Classic White Shirt" width="120" height="120" loading="lazy"><span class="bundle-item-name">Classic White Shirt</span><span class="bundle-item-price" id="bundle-item-1-price"><s aria-label="Original price: $59.99">$59.99</s> <strong aria-label="Bundle price: $49.99">$49.99</strong></span></label></li><li class="bundle-item"><label class="bundle-selector"><input type="checkbox" name="bundle[]" value="pants-1" checked aria-describedby="bundle-item-2-price"><img src="pants.jpg" alt="Slim Fit Chinos" width="120" height="120" loading="lazy"><span class="bundle-item-name">Slim Fit Chinos</span><span class="bundle-item-price" id="bundle-item-2-price"><s aria-label="Original price: $79.99">$79.99</s> <strong aria-label="Bundle price: $64.99">$64.99</strong></span></label></li></ul></fieldset></div><div class="bundle-summary" aria-live="polite"><p class="bundle-total">Bundle Total: <strong>$114.98</strong></p><p class="bundle-savings">You Save: <strong class="savings-amount">$25.00 (18%)</strong></p><button type="button" class="btn btn-primary btn-lg">Add Bundle to Cart</button></div></section>. CSS: .bundle-list { display: flex; gap: 16px; flex-wrap: wrap; list-style: none; padding: 0; } .bundle-item { border: 2px solid #e0e0e0; border-radius: 8px; padding: 16px; flex: 1; min-width: 200px; } .bundle-item:has(input:checked) { border-color: #1a73e8; background: #f0f6ff; } .savings-amount { color: #1e7e34; }',
    'tags' => 'bundle, kit, products, savings, checkbox, ecommerce, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'gift_card',
    'title' => 'Gift Card Options Display',
    'content' => 'Accessible gift card selection with custom amount. HTML: <section class="gift-card-options" aria-labelledby="giftcard-heading"><h2 id="giftcard-heading">Gift Card</h2><form class="giftcard-form"><fieldset><legend>Select Amount</legend><div class="amount-options" role="radiogroup"><label class="amount-option"><input type="radio" name="amount" value="25"><span class="amount-badge">$25</span></label><label class="amount-option"><input type="radio" name="amount" value="50" checked><span class="amount-badge">$50</span></label><label class="amount-option"><input type="radio" name="amount" value="100"><span class="amount-badge">$100</span></label><label class="amount-option"><input type="radio" name="amount" value="custom"><span class="amount-badge">Custom</span></label></div><div class="custom-amount" id="custom-amount-group" hidden><label for="custom-amount">Enter amount ($10 - $500)</label><input type="number" id="custom-amount" name="custom_amount" min="10" max="500" step="1" placeholder="Enter amount"></div></fieldset><fieldset><legend>Recipient Details</legend><div class="form-group"><label for="gc-recipient-name">Recipient Name</label><input type="text" id="gc-recipient-name" name="recipient_name" required autocomplete="off"></div><div class="form-group"><label for="gc-recipient-email">Recipient Email</label><input type="email" id="gc-recipient-email" name="recipient_email" required autocomplete="email"></div><div class="form-group"><label for="gc-message">Personal Message <span class="optional">(optional)</span></label><textarea id="gc-message" name="message" rows="3" maxlength="200" aria-describedby="gc-msg-count"></textarea><p id="gc-msg-count" class="char-count" aria-live="polite">200 characters remaining</p></div></fieldset><button type="submit" class="btn btn-primary">Add Gift Card to Cart</button></form></section>. CSS: .amount-options { display: flex; gap: 12px; flex-wrap: wrap; } .amount-option input { position: absolute; opacity: 0; } .amount-badge { display: block; padding: 12px 24px; border: 2px solid #ddd; border-radius: 8px; font-weight: 600; cursor: pointer; text-align: center; min-width: 80px; } .amount-option input:checked + .amount-badge { border-color: #1a73e8; background: #e8f0fe; color: #1a73e8; } .amount-option input:focus-visible + .amount-badge { outline: 3px solid #1a73e8; outline-offset: 2px; }',
    'tags' => 'gift-card, options, amount, recipient, ecommerce, form, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'subscription_options',
    'title' => 'Subscription Options Layout',
    'content' => 'Accessible subscription frequency selector for recurring products. HTML: <section class="subscription-options" aria-labelledby="sub-heading"><h2 id="sub-heading">Purchase Options</h2><fieldset class="purchase-type"><legend class="sr-only">Choose purchase type</legend><label class="purchase-option"><input type="radio" name="purchase_type" value="one-time" checked><span class="option-card"><strong>One-Time Purchase</strong><span class="option-price">$29.99</span></span></label><label class="purchase-option"><input type="radio" name="purchase_type" value="subscribe"><span class="option-card subscribe-card"><span class="subscribe-badge">Save 15%</span><strong>Subscribe &amp; Save</strong><span class="option-price"><s aria-label="Regular price $29.99">$29.99</s> <strong aria-label="Subscribe price $25.49">$25.49</strong></span></span></label></fieldset><div class="frequency-selector" id="frequency-group" hidden><label for="delivery-frequency">Delivery Frequency</label><select id="delivery-frequency" name="frequency"><option value="2w">Every 2 Weeks</option><option value="1m" selected>Every Month</option><option value="2m">Every 2 Months</option><option value="3m">Every 3 Months</option></select><p class="frequency-note">Free shipping on all subscriptions. Cancel anytime.</p></div></section>. CSS: .purchase-option input { position: absolute; opacity: 0; } .option-card { display: block; padding: 16px 20px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; } .purchase-option input:checked + .option-card { border-color: #1a73e8; background: #f0f6ff; } .subscribe-badge { display: inline-block; background: #1e7e34; color: #fff; font-size: 0.75rem; padding: 2px 8px; border-radius: 4px; }',
    'tags' => 'subscription, subscribe-save, recurring, frequency, ecommerce, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'related_products',
    'title' => 'Related/Cross-Sell Products Section',
    'content' => 'Accessible product carousel/grid for related items. HTML: <section class="related-products" aria-labelledby="related-heading"><h2 id="related-heading">You May Also Like</h2><div class="product-carousel" role="region" aria-roledescription="carousel" aria-label="Related products"><button class="carousel-prev" aria-label="Previous products" disabled><svg aria-hidden="true" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg></button><ul class="product-grid" role="list" aria-live="polite"><li class="product-card"><a href="/product/classic-tee" class="product-link"><img src="tee.jpg" alt="" width="300" height="375" loading="lazy"><h3 class="product-name">Classic Cotton Tee</h3></a><p class="product-price"><span class="sr-only">Price:</span> $24.99</p><div class="product-rating" role="img" aria-label="Rated 4.5 out of 5 stars, 128 reviews"><span aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9734;</span> <span class="review-count">(128)</span></div><button type="button" class="btn btn-add-to-cart" aria-label="Add Classic Cotton Tee to cart">Add to Cart</button></li></ul><button class="carousel-next" aria-label="Next products"><svg aria-hidden="true" width="24" height="24"><path d="M9 6l6 6-6 6"/></svg></button></div></section>. CSS: .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 24px; list-style: none; padding: 0; } .product-card { position: relative; } .product-link { text-decoration: none; color: inherit; } .product-card img { width: 100%; height: auto; aspect-ratio: 4/5; object-fit: cover; border-radius: 8px; } .btn-add-to-cart { width: 100%; padding: 12px; min-height: 44px; }',
    'tags' => 'related, cross-sell, carousel, products, grid, ecommerce, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'recently_viewed',
    'title' => 'Recently Viewed Products Section',
    'content' => 'Client-side recently viewed products with accessible markup. HTML: <section class="recently-viewed" aria-labelledby="recent-heading"><h2 id="recent-heading">Recently Viewed</h2><ul class="recent-grid" role="list" id="recently-viewed-list"><!-- Populated via JavaScript from localStorage --></ul></section>. JavaScript template: function renderRecentlyViewed() { const items = JSON.parse(localStorage.getItem("recently_viewed") || "[]"); const list = document.getElementById("recently-viewed-list"); if (items.length === 0) { list.closest("section").hidden = true; return; } list.innerHTML = items.slice(0, 6).map(item => `<li class="recent-item"><a href="${item.url}"><img src="${item.image}" alt="${item.name}" width="200" height="250" loading="lazy"><span class="recent-name">${item.name}</span><span class="recent-price">${item.price}</span></a></li>`).join(""); }. Track views: function trackProductView(product) { let items = JSON.parse(localStorage.getItem("recently_viewed") || "[]"); items = items.filter(i => i.id !== product.id); items.unshift(product); items = items.slice(0, 20); localStorage.setItem("recently_viewed", JSON.stringify(items)); }. CSS: .recent-grid { display: flex; gap: 16px; overflow-x: auto; scroll-snap-type: x mandatory; padding-bottom: 8px; list-style: none; padding-left: 0; } .recent-item { flex: 0 0 180px; scroll-snap-align: start; } .recent-item img { width: 100%; border-radius: 8px; }',
    'tags' => 'recently-viewed, history, localStorage, products, tracking, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'breadcrumb',
    'title' => 'Breadcrumb Navigation with Schema',
    'content' => 'Accessible breadcrumb with BreadcrumbList structured data. HTML: <nav aria-label="Breadcrumb"><ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList"><li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/" itemprop="item"><span itemprop="name">Home</span></a><meta itemprop="position" content="1"></li><li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/men" itemprop="item"><span itemprop="name">Men</span></a><meta itemprop="position" content="2"></li><li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="/men/shirts" itemprop="item"><span itemprop="name">Shirts</span></a><meta itemprop="position" content="3"></li><li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page"><span itemprop="name">Oxford Button-Down Shirt</span><meta itemprop="position" content="4"></li></ol></nav>. CSS: .breadcrumb { display: flex; flex-wrap: wrap; list-style: none; padding: 0; margin: 0; font-size: 0.875rem; } .breadcrumb li:not(:last-child)::after { content: "/"; margin: 0 8px; color: #999; } .breadcrumb a { color: #0056b3; text-decoration: none; } .breadcrumb a:hover { text-decoration: underline; } .breadcrumb [aria-current="page"] { color: #666; font-weight: 500; }',
    'tags' => 'breadcrumb, navigation, schema, breadcrumblist, structured-data, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =====================================================================
// CATEGORY: html_patterns - CSS Best Practices for SEO (10 entries)
// =====================================================================

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'css_mobile_first',
    'title' => 'Mobile-First Responsive Design',
    'content' => 'Mobile-first CSS ensures content is accessible on all devices, which is critical for Google mobile-first indexing. Write base styles for mobile, then add complexity with min-width breakpoints. Pattern: /* Base: mobile styles */ .product-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; padding: 0 16px; } .product-card img { width: 100%; height: auto; aspect-ratio: 1/1; object-fit: cover; } /* Tablet */ @media (min-width: 768px) { .product-grid { grid-template-columns: repeat(3, 1fr); gap: 20px; padding: 0 24px; } } /* Desktop */ @media (min-width: 1024px) { .product-grid { grid-template-columns: repeat(4, 1fr); gap: 24px; max-width: 1280px; margin: 0 auto; } } /* Large desktop */ @media (min-width: 1440px) { .product-grid { grid-template-columns: repeat(5, 1fr); } } Key: Never use max-width media queries for layout (they fight mobile-first). Use min-width exclusively. Always include: <meta name="viewport" content="width=device-width, initial-scale=1">.',
    'tags' => 'mobile-first, responsive, media-queries, min-width, grid, seo, css',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'css_print',
    'title' => 'Print Stylesheet for Product Pages',
    'content' => 'Print styles improve user experience and can include product URLs for SEO value. CSS: @media print { /* Hide non-essential elements */ header, footer, nav, .sidebar, .add-to-cart, .social-share, .newsletter-signup, .related-products, .cookie-banner, .chat-widget, video, iframe { display: none !important; } /* Ensure readable typography */ body { font-family: Georgia, "Times New Roman", serif; font-size: 12pt; line-height: 1.5; color: #000; background: #fff; } /* Show URLs after links */ a[href]::after { content: " (" attr(href) ")"; font-size: 0.8em; color: #666; } a[href^="#"]::after, a[href^="javascript"]::after { content: ""; } /* Product images print well */ .product-image { max-width: 400px; page-break-inside: avoid; } /* Ensure tables fit */ table { border-collapse: collapse; width: 100%; } th, td { border: 1px solid #000; padding: 4pt 8pt; } /* Avoid page breaks inside cards */ .product-card, .testimonial-card { page-break-inside: avoid; } /* Show product price clearly */ .product-price { font-size: 14pt; font-weight: bold; } }',
    'tags' => 'print, stylesheet, media-print, product, urls, css, seo',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'css_dark_mode',
    'title' => 'Dark Mode Support',
    'content' => 'Dark mode respects user preferences and reduces eye strain. Implementation using CSS custom properties: :root { --bg-primary: #ffffff; --bg-secondary: #f8f9fa; --text-primary: #1a1a1a; --text-secondary: #545454; --border-color: #e0e0e0; --accent: #1a73e8; --accent-hover: #1557b0; --card-bg: #ffffff; --card-shadow: 0 2px 8px rgba(0,0,0,0.1); } @media (prefers-color-scheme: dark) { :root { --bg-primary: #121212; --bg-secondary: #1e1e1e; --text-primary: #e0e0e0; --text-secondary: #a0a0a0; --border-color: #333333; --accent: #8ab4f8; --accent-hover: #aecbfa; --card-bg: #1e1e1e; --card-shadow: 0 2px 8px rgba(0,0,0,0.4); } img { opacity: 0.9; } } /* Apply variables */ body { background: var(--bg-primary); color: var(--text-primary); } .card { background: var(--card-bg); border: 1px solid var(--border-color); box-shadow: var(--card-shadow); } /* Manual toggle support */ [data-theme="dark"] { /* same overrides as prefers-color-scheme: dark */ } /* Important: Maintain contrast ratios in both modes. Dark mode #e0e0e0 on #121212 = 14:1 ratio (excellent). */.',
    'tags' => 'dark-mode, prefers-color-scheme, css-variables, theme, contrast, css',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'css_grid_flexbox',
    'title' => 'CSS Grid vs Flexbox for Layouts',
    'content' => 'Use Grid for 2D layouts (page structure, product grids) and Flexbox for 1D layouts (navigation, button groups). Grid for product listing: .product-listing { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 24px; } /* Grid for page layout */ .page-layout { display: grid; grid-template-columns: 260px 1fr; grid-template-rows: auto 1fr auto; gap: 0; } @media (max-width: 768px) { .page-layout { grid-template-columns: 1fr; } } /* Flexbox for navigation */ .nav-bar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; } /* Flexbox for button groups */ .btn-group { display: flex; gap: 8px; flex-wrap: wrap; } /* Flexbox for card content */ .card-body { display: flex; flex-direction: column; } .card-body .card-actions { margin-top: auto; /* push actions to bottom */ } /* Subgrid for aligned card grids (modern browsers) */ .product-grid { display: grid; grid-template-columns: repeat(3, 1fr); } .product-card { display: grid; grid-template-rows: subgrid; grid-row: span 4; /* image, title, price, button */ }',
    'tags' => 'grid, flexbox, layout, product-grid, responsive, subgrid, css',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'css_font_loading',
    'title' => 'Font Loading Optimization',
    'content' => 'Optimized font loading prevents layout shifts (CLS) and improves LCP. Techniques: 1) Preload critical fonts: <link rel="preload" href="/fonts/Inter-Regular.woff2" as="font" type="font/woff2" crossorigin>. 2) Use font-display: swap for visible text during load: @font-face { font-family: "Inter"; src: url("/fonts/Inter-Regular.woff2") format("woff2"); font-weight: 400; font-style: normal; font-display: swap; } @font-face { font-family: "Inter"; src: url("/fonts/Inter-Bold.woff2") format("woff2"); font-weight: 700; font-style: normal; font-display: swap; }. 3) Size-adjust fallback to reduce CLS: @font-face { font-family: "Inter-fallback"; src: local("Arial"); size-adjust: 107%; ascent-override: 90%; descent-override: 22%; line-gap-override: 0%; } body { font-family: "Inter", "Inter-fallback", sans-serif; }. 4) Subset fonts for languages you need: /* Use unicode-range for multi-script sites */ @font-face { font-family: "Inter"; src: url("inter-latin.woff2") format("woff2"); unicode-range: U+0000-00FF; }. 5) Self-host fonts instead of Google Fonts for fewer DNS lookups and privacy compliance.',
    'tags' => 'fonts, loading, preload, font-display, swap, cls, performance, css',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'css_critical',
    'title' => 'Critical CSS Extraction',
    'content' => 'Inline critical CSS in <head> for above-the-fold content; defer the rest. This improves FCP and LCP scores. Pattern: <head><style>/* Critical CSS - only what is needed for above-the-fold rendering */ :root { --max-width: 1280px; } *, *::before, *::after { box-sizing: border-box; margin: 0; } body { font-family: system-ui, -apple-system, sans-serif; line-height: 1.6; color: #1a1a1a; } .header { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-bottom: 1px solid #e0e0e0; } .hero { padding: 48px 24px; text-align: center; } .hero h1 { font-size: clamp(1.75rem, 4vw, 3rem); }</style><link rel="preload" href="/css/main.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"><noscript><link rel="stylesheet" href="/css/main.css"></noscript></head>. Best practices: 1) Keep critical CSS under 14KB (fits in first TCP round trip). 2) Use tools like Critical (npm) to auto-extract. 3) Different critical CSS per page template (home, PDP, PLP, CMS). 4) Avoid @import in critical CSS (blocks rendering).',
    'tags' => 'critical-css, inline, above-fold, fcp, lcp, performance, css',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'css_animation_performance',
    'title' => 'Animation Performance',
    'content' => 'High-performance CSS animations avoid layout thrashing and jank. Rules: 1) Only animate transform and opacity (compositor-only properties). 2) Use will-change sparingly. 3) Prefer CSS over JavaScript for simple animations. Good patterns: /* Smooth card hover - only uses transform */ .product-card { transition: transform 0.2s ease, box-shadow 0.2s ease; } .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); } /* Fade-in animation on scroll */ @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } } .animate-in { animation: fadeInUp 0.4s ease-out forwards; } /* Bad - causes layout recalculation: .bad-animation { transition: width 0.3s, height 0.3s, margin 0.3s; } */ /* Use will-change only when needed */ .carousel-slide { will-change: transform; } .carousel-slide.idle { will-change: auto; /* remove when not animating */ } /* Prefer CSS containment for complex animations */ .animated-section { contain: layout style paint; }',
    'tags' => 'animation, performance, transform, opacity, will-change, jank, css',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'css_containment',
    'title' => 'CSS Containment for Rendering Performance',
    'content' => 'CSS containment isolates elements for rendering optimization, reducing layout recalculations. Key values: 1) contain: layout - isolates layout calculations. 2) contain: paint - limits painting to element bounds. 3) contain: size - element size independent of children. 4) contain: style - counter/quote scoping. 5) content-visibility: auto - skips rendering of offscreen content. Implementation: /* Product cards in long lists - skip rendering offscreen */ .product-card { content-visibility: auto; contain-intrinsic-size: 0 400px; /* estimated height */ } /* Sidebar widget isolation */ .sidebar-widget { contain: layout paint; } /* Heavy components like reviews or recommendations */ .reviews-section { content-visibility: auto; contain-intrinsic-size: 0 600px; } /* Filter panel */ .filter-panel { contain: layout style; } /* Important: content-visibility: auto can dramatically improve initial render of long product listing pages by only rendering visible cards. Google crawlers support this CSS property. */. Performance note: On a page with 100 product cards, content-visibility: auto can reduce initial rendering time by 50-70%.',
    'tags' => 'containment, content-visibility, performance, rendering, layout, css',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'css_container_queries',
    'title' => 'Container Queries for Component-Based Design',
    'content' => 'Container queries allow components to adapt based on their container size, not viewport. Better for reusable components across different page layouts. Implementation: /* Define container */ .product-card-wrapper { container-type: inline-size; container-name: product-card; } /* Default: small container (sidebar, mobile) */ .product-card { display: flex; flex-direction: column; } .product-card img { aspect-ratio: 1/1; width: 100%; } /* Medium container: horizontal layout */ @container product-card (min-width: 400px) { .product-card { flex-direction: row; gap: 16px; } .product-card img { width: 200px; flex-shrink: 0; } } /* Large container: enhanced layout */ @container product-card (min-width: 600px) { .product-card { gap: 24px; } .product-card img { width: 280px; } .product-card .quick-view { display: block; } } /* Container query units */ @container (min-width: 500px) { .card-title { font-size: clamp(1rem, 3cqi, 1.5rem); /* cqi = container query inline */ } } /* Fallback for older browsers */ @supports not (container-type: inline-size) { .product-card { /* viewport-based responsive fallback */ } }',
    'tags' => 'container-queries, component, responsive, layout, reusable, css',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'css_logical_properties',
    'title' => 'Logical Properties for RTL Support',
    'content' => 'CSS logical properties enable seamless RTL (right-to-left) support for Arabic, Hebrew, and other RTL languages. Replace physical properties with logical equivalents. Mapping: margin-left -> margin-inline-start, margin-right -> margin-inline-end, padding-left -> padding-inline-start, text-align: left -> text-align: start, border-left -> border-inline-start, float: left -> float: inline-start, width -> inline-size, height -> block-size. Implementation: /* Before (physical - breaks in RTL) */ .product-card { margin-left: 16px; padding-right: 24px; text-align: left; border-left: 3px solid #1a73e8; } /* After (logical - works in both LTR and RTL) */ .product-card { margin-inline-start: 16px; padding-inline-end: 24px; text-align: start; border-inline-start: 3px solid #1a73e8; } /* Block direction (top/bottom) */ .section { margin-block: 24px; /* top and bottom */ padding-block-start: 40px; /* top in LTR/RTL */ } /* Shorthand logical properties */ .card { margin-inline: 16px; /* left and right */ padding-block: 20px 24px; /* top and bottom */ border-inline: 1px solid #e0e0e0; } /* Set direction in HTML */ /* <html lang="ar" dir="rtl"> */ /* Auto direction detection */ html[dir="rtl"] .icon-arrow { transform: scaleX(-1); }',
    'tags' => 'logical-properties, rtl, ltr, internationalization, arabic, hebrew, css',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =====================================================================
// CATEGORY: html_patterns - Rich Content Patterns (8 entries)
// =====================================================================

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'video_embed',
    'title' => 'Video Embed with Schema Markup',
    'content' => 'Accessible, SEO-optimized video embed with VideoObject schema. HTML: <div class="video-container" itemscope itemtype="https://schema.org/VideoObject"><meta itemprop="name" content="Product Demo: How to Use Widget Pro"><meta itemprop="description" content="Learn how to set up and use Widget Pro in 3 easy steps."><meta itemprop="uploadDate" content="2025-10-15"><meta itemprop="duration" content="PT3M45S"><meta itemprop="thumbnailUrl" content="https://example.com/thumb.jpg"><meta itemprop="contentUrl" content="https://example.com/video.mp4"><meta itemprop="embedUrl" content="https://www.youtube.com/embed/VIDEO_ID"><div class="video-wrapper" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;"><iframe src="https://www.youtube-nocookie.com/embed/VIDEO_ID" title="Product Demo: How to Use Widget Pro" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"></iframe></div><div class="video-transcript"><details><summary>View Video Transcript</summary><div itemprop="transcript"><p>Welcome to our Widget Pro demo. In this video, we will walk you through...</p></div></details></div></div>. Facade pattern for performance: Show a thumbnail image as placeholder, load the iframe only on click to improve page load speed. CSS: .video-wrapper { position: relative; padding-bottom: 56.25%; /* 16:9 */ } .video-wrapper iframe { position: absolute; inset: 0; width: 100%; height: 100%; }',
    'tags' => 'video, embed, schema, youtube, transcript, facade, performance, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'image_gallery',
    'title' => 'Image Gallery with Lightbox',
    'content' => 'Accessible product image gallery with keyboard-navigable lightbox. HTML: <section class="product-gallery" aria-labelledby="gallery-heading"><h2 id="gallery-heading" class="sr-only">Product Images</h2><div class="gallery-main"><button class="gallery-prev" aria-label="Previous image"><svg aria-hidden="true" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg></button><div class="gallery-viewport"><img id="main-image" src="product-main.jpg" alt="Blue denim jacket, front view" width="800" height="1000"><span class="gallery-counter" aria-live="polite">Image 1 of 5</span></div><button class="gallery-next" aria-label="Next image"><svg aria-hidden="true" width="24" height="24"><path d="M9 6l6 6-6 6"/></svg></button></div><ul class="gallery-thumbs" role="list" aria-label="Product image thumbnails"><li><button aria-pressed="true" aria-label="View front view"><img src="thumb-1.jpg" alt="" width="80" height="100" loading="lazy"></button></li><li><button aria-pressed="false" aria-label="View back view"><img src="thumb-2.jpg" alt="" width="80" height="100" loading="lazy"></button></li><li><button aria-pressed="false" aria-label="View detail close-up"><img src="thumb-3.jpg" alt="" width="80" height="100" loading="lazy"></button></li></ul><button class="gallery-fullscreen" aria-label="Open fullscreen gallery">&#x26F6; Fullscreen</button></section>. Lightbox dialog: <dialog class="lightbox" aria-label="Fullscreen image gallery"><button class="lightbox-close" aria-label="Close gallery">&times;</button><img src="" alt="" class="lightbox-img"><button class="lightbox-prev" aria-label="Previous image">&lsaquo;</button><button class="lightbox-next" aria-label="Next image">&rsaquo;</button></dialog>. CSS: .gallery-thumbs { display: flex; gap: 8px; list-style: none; padding: 0; overflow-x: auto; } .gallery-thumbs button[aria-pressed="true"] img { border: 2px solid #1a73e8; } .lightbox { max-width: 90vw; max-height: 90vh; border: none; padding: 0; } .lightbox::backdrop { background: rgba(0,0,0,0.9); }',
    'tags' => 'gallery, lightbox, images, thumbnails, keyboard, dialog, product, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'size_calculator',
    'title' => 'Interactive Size Calculator',
    'content' => 'Accessible size calculator with form inputs and result display. HTML: <section class="size-calculator" aria-labelledby="calc-heading"><h2 id="calc-heading">Find Your Size</h2><form id="size-calc-form" aria-describedby="calc-instructions"><p id="calc-instructions">Enter your measurements to find your recommended size.</p><div class="calc-inputs"><div class="form-group"><label for="calc-chest">Chest (inches)</label><input type="number" id="calc-chest" name="chest" min="28" max="60" step="0.5" required aria-required="true" inputmode="decimal"><p class="form-hint" id="chest-hint">Measure around the fullest part</p></div><div class="form-group"><label for="calc-waist">Waist (inches)</label><input type="number" id="calc-waist" name="waist" min="24" max="52" step="0.5" required aria-required="true" inputmode="decimal"></div><div class="form-group"><label for="calc-hips">Hips (inches)</label><input type="number" id="calc-hips" name="hips" min="30" max="60" step="0.5" required aria-required="true" inputmode="decimal"></div></div><button type="submit" class="btn btn-primary">Find My Size</button></form><div id="size-result" role="status" aria-live="polite" class="size-result" hidden><h3>Your Recommended Size</h3><p class="recommended-size"><strong id="result-size">Medium</strong></p><p>Based on your measurements, we recommend a <strong>Medium</strong> for a regular fit or <strong>Large</strong> for a relaxed fit.</p><a href="#" class="btn btn-outline">View Size M Products</a></div></section>. CSS: .calc-inputs { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 24px; } .size-result { margin-top: 24px; padding: 24px; background: #f0f6ff; border-radius: 8px; border: 2px solid #1a73e8; } .recommended-size { font-size: 2rem; }',
    'tags' => 'calculator, size, interactive, form, measurements, result, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'store_locator',
    'title' => 'Store Locator with Map',
    'content' => 'Accessible store locator with search, results list, and map integration. HTML: <section class="store-locator" aria-labelledby="locator-heading"><h2 id="locator-heading">Find a Store</h2><form class="locator-search" role="search" aria-label="Store search"><div class="search-group"><label for="store-search">Enter zip code, city, or address</label><div class="input-group"><input type="text" id="store-search" name="location" placeholder="e.g., 90210 or Los Angeles" autocomplete="postal-code"><button type="submit" class="btn btn-primary">Search</button></div></div><div class="search-filters"><label><input type="checkbox" name="filter[]" value="pickup"> In-Store Pickup</label><label><input type="checkbox" name="filter[]" value="open-now"> Open Now</label></div></form><div class="locator-results" role="region" aria-label="Search results"><div class="store-list" id="store-list" aria-live="polite" role="list"><div class="store-card" role="listitem"><h3 class="store-name">Downtown Flagship Store</h3><address>123 Main St, Los Angeles, CA 90012</address><p class="store-phone"><a href="tel:+12135551234" aria-label="Call Downtown Flagship Store">(213) 555-1234</a></p><p class="store-hours"><strong>Hours:</strong> Mon-Sat 10am-9pm, Sun 11am-6pm</p><p class="store-distance">0.8 miles away</p><div class="store-actions"><a href="/store/downtown" class="btn btn-sm">Store Details</a><a href="https://maps.google.com/?q=123+Main+St" class="btn btn-sm btn-outline" target="_blank" rel="noopener">Get Directions <span class="sr-only">(opens in new tab)</span></a></div></div></div><div class="store-map" id="store-map" role="application" aria-label="Map showing store locations"><!-- Map renders here - ensure fallback for no-JS --><noscript><p>Enable JavaScript to view the interactive map, or use the store list above.</p></noscript></div></div></section>. Schema for each store: <div itemscope itemtype="https://schema.org/Store"><meta itemprop="name" content="Downtown Flagship Store"><div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress"><meta itemprop="streetAddress" content="123 Main St"><meta itemprop="addressLocality" content="Los Angeles"><meta itemprop="addressRegion" content="CA"><meta itemprop="postalCode" content="90012"></div></div>.',
    'tags' => 'store-locator, map, search, locations, address, schema, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'live_chat',
    'title' => 'Live Chat Integration - Accessible Pattern',
    'content' => 'Accessible live chat widget that does not interfere with page usability. HTML: <div class="chat-widget" role="complementary" aria-label="Live chat support"><button class="chat-toggle" aria-expanded="false" aria-controls="chat-panel" aria-label="Open live chat"><svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg><span class="chat-badge" aria-live="polite" hidden><span class="sr-only">1 unread message</span>1</span></button><div id="chat-panel" class="chat-panel" hidden role="dialog" aria-label="Live chat"><div class="chat-header"><h2 class="chat-title">Chat with us</h2><button aria-label="Close chat" class="chat-close">&times;</button></div><div class="chat-messages" role="log" aria-live="polite" aria-relevant="additions" tabindex="0"><div class="chat-msg chat-msg-agent"><span class="chat-author sr-only">Support agent:</span><p>Hi! How can we help you today?</p></div></div><form class="chat-input" aria-label="Send a message"><label for="chat-text" class="sr-only">Type your message</label><input type="text" id="chat-text" placeholder="Type a message..." autocomplete="off"><button type="submit" aria-label="Send message"><svg aria-hidden="true" width="20" height="20"><path d="M2 21l21-9L2 3v7l15 2-15 2z"/></svg></button></form></div></div>. CSS: .chat-widget { position: fixed; bottom: 20px; right: 20px; z-index: 9999; } .chat-toggle { width: 56px; height: 56px; border-radius: 50%; background: #1a73e8; color: #fff; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2); } .chat-panel { position: absolute; bottom: 70px; right: 0; width: 360px; max-height: 500px; background: #fff; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.15); display: flex; flex-direction: column; } .chat-messages { flex: 1; overflow-y: auto; padding: 16px; } /* Ensure chat button has minimum 44px touch target and does not overlap page content */ @media (max-width: 480px) { .chat-panel { width: 100vw; height: 100vh; bottom: 0; right: 0; border-radius: 0; max-height: none; } }',
    'tags' => 'chat, live-chat, widget, support, dialog, accessible, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'product_tabs',
    'title' => 'Product Information Tabs - Accessible Tab Panel',
    'content' => 'ARIA-compliant tabbed interface for product details. HTML: <div class="product-tabs"><div role="tablist" aria-label="Product information"><button role="tab" id="tab-desc" aria-selected="true" aria-controls="panel-desc" tabindex="0">Description</button><button role="tab" id="tab-specs" aria-selected="false" aria-controls="panel-specs" tabindex="-1">Specifications</button><button role="tab" id="tab-reviews" aria-selected="false" aria-controls="panel-reviews" tabindex="-1">Reviews <span class="tab-count">(47)</span></button><button role="tab" id="tab-shipping" aria-selected="false" aria-controls="panel-shipping" tabindex="-1">Shipping &amp; Returns</button></div><div id="panel-desc" role="tabpanel" aria-labelledby="tab-desc" tabindex="0"><h2>Product Description</h2><p>...</p></div><div id="panel-specs" role="tabpanel" aria-labelledby="tab-specs" tabindex="0" hidden>...</div><div id="panel-reviews" role="tabpanel" aria-labelledby="tab-reviews" tabindex="0" hidden>...</div><div id="panel-shipping" role="tabpanel" aria-labelledby="tab-shipping" tabindex="0" hidden>...</div></div>. Keyboard behavior: Arrow Left/Right move between tabs. Home/End go to first/last tab. Tab key moves into active panel content. JavaScript: const tablist = document.querySelector("[role=tablist]"); const tabs = tablist.querySelectorAll("[role=tab]"); tablist.addEventListener("keydown", (e) => { const currentIndex = [...tabs].indexOf(e.target); let newIndex; if (e.key === "ArrowRight") newIndex = (currentIndex + 1) % tabs.length; else if (e.key === "ArrowLeft") newIndex = (currentIndex - 1 + tabs.length) % tabs.length; else if (e.key === "Home") newIndex = 0; else if (e.key === "End") newIndex = tabs.length - 1; if (newIndex !== undefined) { activateTab(tabs[newIndex]); } }); CSS: [role="tablist"] { display: flex; border-bottom: 2px solid #e0e0e0; gap: 0; } [role="tab"] { padding: 12px 24px; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; font-weight: 500; min-height: 44px; } [role="tab"][aria-selected="true"] { border-bottom-color: #1a73e8; color: #1a73e8; } [role="tab"]:focus-visible { outline: 3px solid #1a73e8; outline-offset: -3px; }',
    'tags' => 'tabs, tabpanel, product, aria, keyboard, accessible, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'html_patterns',
    'subcategory' => 'cookie_consent',
    'title' => 'Cookie Consent Banner - Accessible and GDPR Compliant',
    'content' => 'Accessible cookie consent banner with granular controls. HTML: <div class="cookie-banner" role="dialog" aria-labelledby="cookie-title" aria-describedby="cookie-desc" aria-modal="false"><div class="cookie-content"><h2 id="cookie-title">We value your privacy</h2><p id="cookie-desc">We use cookies to enhance your browsing experience, serve personalized ads, and analyze our traffic. Choose your preferences below.</p><div class="cookie-options"><fieldset><legend class="sr-only">Cookie preferences</legend><label class="cookie-option"><input type="checkbox" checked disabled aria-describedby="essential-desc"> <strong>Essential</strong><span id="essential-desc" class="cookie-detail">Required for the website to function. Cannot be disabled.</span></label><label class="cookie-option"><input type="checkbox" name="cookies[]" value="analytics"> <strong>Analytics</strong><span class="cookie-detail">Help us understand how visitors interact with our website.</span></label><label class="cookie-option"><input type="checkbox" name="cookies[]" value="marketing"> <strong>Marketing</strong><span class="cookie-detail">Used to deliver personalized advertisements.</span></label></fieldset></div><div class="cookie-actions"><button class="btn btn-primary" id="accept-all">Accept All</button><button class="btn btn-outline" id="save-preferences">Save Preferences</button><button class="btn btn-link" id="reject-optional">Reject Optional</button></div></div></div>. CSS: .cookie-banner { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; box-shadow: 0 -4px 20px rgba(0,0,0,0.15); padding: 24px; z-index: 10000; } .cookie-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 16px; } .cookie-option { display: block; padding: 8px 0; } .cookie-detail { display: block; font-size: 0.85rem; color: #666; } @media (max-width: 640px) { .cookie-actions { flex-direction: column; } .cookie-actions button { width: 100%; } }',
    'tags' => 'cookie, consent, gdpr, privacy, banner, dialog, accessible, html-pattern',
    'is_active' => 1,
    'sort_order' => $sort++,
];

return $entries;
