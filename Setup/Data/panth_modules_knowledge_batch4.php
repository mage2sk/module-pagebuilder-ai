<?php
/**
 * Panth AdvancedSEO - AI Knowledge Base Batch 4
 *
 * Infrastructure and Performance modules: Core, CoreWebVitals, CacheManager,
 * ImageOptimizer, PerformanceOptimizer, MalwareScanner, NotFoundPage.
 *
 * Returns an array of knowledge entries for panth_seo_ai_knowledge table.
 */
declare(strict_types=1);

$sort = 0;
$entries = [];

// =========================================================================
// MODULE: Panth_Core - Core Settings & Theme Validation
// =========================================================================

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'core_module',
    'title' => 'Panth Core Module Overview and Theme Detection',
    'content' => 'Panth_Core is the foundation module required by all other Panth extensions. It provides a shared theme detection helper (Panth\Core\Helper\Theme) that determines whether the active storefront is Hyva or Luma. The detection checks if Hyva_Theme module is enabled and inspects the theme path. This is critical for SEO content generation because Hyva uses Alpine.js (no KnockoutJS) and TailwindCSS, which means generated HTML must use Alpine directives (x-data, x-show, x-cloak) rather than Knockout templates. Content generated for Hyva themes must never include RequireJS or KnockoutJS syntax.',
    'tags' => 'core, theme-detection, hyva, luma, alpine-js, infrastructure',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'core_module',
    'title' => 'Core Caching Layer for Performance',
    'content' => 'Panth_Core provides a global caching toggle (panth_core/general/cache_enabled) that all Panth modules respect. When enabled, expensive operations like SEO audits, metadata lookups, and AI prompt resolution are cached to reduce database queries and API calls. For AI-generated content, the caching layer means that regenerating metadata for the same entity will serve the cached version unless the cache is explicitly flushed. Always mention in SEO recommendations that enabling Panth Core caching improves TTFB (Time to First Byte) by reducing backend processing on cached pages.',
    'tags' => 'core, caching, performance, ttfb, backend-optimization',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'core_module',
    'title' => 'Child Theme Validation and SEO Integrity',
    'content' => 'Panth_Core includes a child theme validation panel (Panth\Core\Block\Adminhtml\System\Config\ChildThemeValidation) that checks whether the active theme properly inherits from the Panth/Infotech base theme. An improperly configured child theme can break structured data output, meta tag rendering, hreflang injection, and canonical URL generation. When generating SEO content or auditing a site, the AI should note that theme validation must pass before relying on any frontend SEO features. The child theme setup guide is available in admin under Panth Extensions > Core Settings > Child Theme Setup Guide.',
    'tags' => 'core, child-theme, validation, structured-data, theme-inheritance',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'core_module',
    'title' => 'CSP Configuration and Third-Party Script Impact',
    'content' => 'Panth_Core sets Content Security Policy for the checkout to report-only mode (storefront_checkout_index_index report_only=1) to prevent third-party payment modules (Mollie, Stripe, etc.) from being blocked. This is an SEO consideration because CSP violations can break checkout flows, increasing cart abandonment rates. For content generation, if referencing payment methods or checkout features in product descriptions, note that the store supports secure third-party payment integrations without CSP conflicts.',
    'tags' => 'core, csp, content-security-policy, checkout, third-party-scripts',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =========================================================================
// MODULE: Panth_CoreWebVitals - Performance Monitoring
// =========================================================================

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'core_web_vitals',
    'title' => 'Core Web Vitals Monitoring Overview',
    'content' => 'Panth_CoreWebVitals injects a lightweight JavaScript snippet on every frontend page that monitors LCP (Largest Contentful Paint), INP (Interaction to Next Paint, replaced FID in March 2024), and CLS (Cumulative Layout Shift) using the browser native PerformanceObserver API. These three metrics are Google ranking signals. The module can send Real User Monitoring (RUM) data to Google Analytics 4 via gtag or to a custom endpoint via navigator.sendBeacon. Default targets: LCP < 2500ms, FID < 100ms, INP < 200ms, CLS < 0.1. When generating SEO content, the AI should be aware that page performance directly affects search rankings.',
    'tags' => 'core-web-vitals, lcp, inp, cls, fid, performance, google-ranking',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'core_web_vitals',
    'title' => 'LCP Optimization and Content Generation Impact',
    'content' => 'LCP (Largest Contentful Paint) measures the time for the largest visible element (hero image, heading text block, video poster) to render. Google target is under 2500ms. The module tracks LCP via the largest-contentful-paint PerformanceObserver entry type. For AI content generation: hero sections and above-the-fold content directly affect LCP. When generating category or CMS page content, place the most important heading and a single optimized hero image at the top. Avoid large carousels or multiple heavy images above the fold. The LCP element is often the main product image on PDPs or the category banner on PLPs.',
    'tags' => 'core-web-vitals, lcp, hero-image, above-fold, content-optimization',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'core_web_vitals',
    'title' => 'CLS Prevention and Layout Stability for Generated Content',
    'content' => 'CLS (Cumulative Layout Shift) measures unexpected visual shifts during page load. Google target is below 0.1. The module monitors layout-shift PerformanceObserver entries, excluding shifts caused by user input. When generating HTML content via PageBuilder or CMS blocks, always specify width and height attributes on images, use fixed-dimension containers for dynamic content (ads, banners, product sliders), and avoid inserting content above existing elements after page load. Alpine.js x-cloak elements can cause CLS if not styled with [x-cloak]{display:none!important} -- the PerformanceOptimizer module handles this automatically.',
    'tags' => 'core-web-vitals, cls, layout-shift, visual-stability, content-guidelines',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'core_web_vitals',
    'title' => 'Resource Hints for Faster External Resource Loading',
    'content' => 'The CoreWebVitals module provides configurable resource hints: DNS Prefetch (resolves domain names early, saving 20-120ms per domain), Preconnect (performs DNS + TCP + TLS handshake early, limit to 2-4 critical origins), and Prefetch (downloads resources at low priority for next navigation). Configure under Panth Extensions > Core Web Vitals > Resource Hints. Common preconnect targets: fonts.googleapis.com, fonts.gstatic.com, CDN domains. When generating content that references external resources (Google Fonts, CDN-hosted images, embedded videos), recommend adding those domains to the preconnect list to improve loading performance.',
    'tags' => 'core-web-vitals, resource-hints, dns-prefetch, preconnect, prefetch, performance',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'core_web_vitals',
    'title' => 'INP Metric and Interaction Responsiveness',
    'content' => 'INP (Interaction to Next Paint) replaced FID as a Core Web Vital in March 2024. While FID only measured the delay of the first interaction, INP measures the worst interaction latency throughout the entire page lifecycle. Google target is under 200ms. The module tracks both FID (first-input entry type) and INP (event entry type with durationThreshold). For SEO content: pages with heavy JavaScript interactions (product configurators, layered navigation filters, quick-view modals) are most at risk for poor INP. When generating content recommendations, suggest minimizing JavaScript-heavy interactive elements on critical landing pages.',
    'tags' => 'core-web-vitals, inp, fid, interaction, responsiveness, javascript-optimization',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =========================================================================
// MODULE: Panth_CacheManager - Cache Warming & Smart Invalidation
// =========================================================================

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'cache_manager',
    'title' => 'Cache Warmup for SEO Crawl Performance',
    'content' => 'Panth_CacheManager provides automated cache warmup via cron (default: every 6 hours, configurable via cron expression). It collects URLs for homepage, category pages, product pages, and CMS pages, then fetches them using curl_multi with configurable concurrent requests (default: 5). This ensures Googlebot and other crawlers always hit cached pages, resulting in faster TTFB and better crawl efficiency. Google has confirmed that page speed is a ranking factor, and cache misses causing slow response times can negatively impact crawl budget allocation. After bulk AI content generation (meta titles, descriptions), trigger a cache warmup to ensure updated pages are pre-cached.',
    'tags' => 'cache-manager, warmup, crawl-budget, ttfb, googlebot, cron',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'cache_manager',
    'title' => 'Smart Cache Invalidation After Content Updates',
    'content' => 'The CacheManager module supports smart (selective) cache invalidation: when a product is saved, only product-related cache entries are cleared; when a category is saved, only category-related entries; when a CMS page/block is saved, only CMS entries. This is configured under panth_cachemanager/invalidation/. This is important for AI-generated content workflows: when the AI updates meta titles and descriptions for products, only product page caches are invalidated rather than the entire full-page cache. This prevents a complete cache flush that would cause temporary performance degradation across the entire site. Default FPC TTL is 86400 seconds (24 hours).',
    'tags' => 'cache-manager, invalidation, smart-cache, product-save, category-save, fpc',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'cache_manager',
    'title' => 'Cache Warmup Page Types and URL Collection',
    'content' => 'The cache warmup cron job (Panth\CacheManager\Cron\WarmupCache) collects URLs by page type: homepage (base URL), category pages (active categories with level > 1 using url_path attribute), product pages (enabled products visible in catalog using url_key + .html suffix), and CMS pages (active pages excluding no-route). Each warmup run logs results to the panth_cache_warmup_log table with URL, page type, HTTP status, response time in ms, and success/failure status. For SEO: ensure all important landing pages are included in warmup page types. Pages not in warmup may serve uncached on first Googlebot visit, causing slow TTFB.',
    'tags' => 'cache-manager, warmup, url-collection, categories, products, cms-pages',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'cache_manager',
    'title' => 'Concurrent Warmup Requests and Server Load',
    'content' => 'Cache warmup uses curl_multi for concurrent HTTP requests. The default concurrency is 5 simultaneous requests, configurable under panth_cachemanager/warmup/concurrent_requests. Each request has a 30-second timeout and 10-second connection timeout. The user agent is "PanthCacheManager/1.0 (Warmup)". For large catalogs with thousands of products and categories, increase concurrency cautiously to avoid overloading the server. When AI generates content for many pages simultaneously, schedule the warmup cron after the bulk update completes (e.g., set cron to run at 2 AM after a midnight batch: "0 2 * * *").',
    'tags' => 'cache-manager, concurrent-requests, curl-multi, server-load, bulk-update',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =========================================================================
// MODULE: Panth_ImageOptimizer - Image Optimization
// =========================================================================

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'image_optimizer',
    'title' => 'Image Optimizer Module Scope and Capabilities',
    'content' => 'Panth_ImageOptimizer is a frontend-only image performance module. It provides: lazy loading (native loading="lazy" via PHP plugin and/or IntersectionObserver-based with fade-in), WebP detection (browser-side only, removes unsupported <source type="image/webp"> elements), and performance hints (link rel="preload" for critical images, decoding="async", fetchpriority="high"). IMPORTANT: This module does NOT perform server-side WebP conversion, responsive image generation (srcset/sizes), or CDN URL rewriting. For AI content generation, when recommending image optimization, note that server-side WebP conversion requires a separate module or CDN service like Cloudflare or Fastly.',
    'tags' => 'image-optimizer, lazy-loading, webp, performance-hints, frontend-optimization',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'image_optimizer',
    'title' => 'Lazy Loading Strategy and LCP Impact',
    'content' => 'The ImageOptimizer supports three lazy loading strategies: Native (adds loading="lazy" attribute via PHP plugin, best browser compatibility), Intersection Observer (JavaScript-based with configurable viewport threshold of 300px, supports placeholder blur and fade-in effects), and Hybrid (both). Critically, the module excludes above-the-fold images from lazy loading (default: first 3 images) to protect LCP scores. Lazy-loading the LCP image is a common SEO mistake that delays the largest contentful paint. When generating content with images, ensure the primary hero/product image is among the first 3 to benefit from eager loading.',
    'tags' => 'image-optimizer, lazy-loading, native, intersection-observer, lcp, above-fold',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'image_optimizer',
    'title' => 'Critical Image Preloading and fetchpriority',
    'content' => 'The ImageOptimizer injects link rel="preload" tags for the first N images (default: 2) to improve LCP by telling the browser to download them before the HTML parser discovers them. It also sets fetchpriority="high" (Chrome 101+) on critical images and decoding="async" on all images to decode them off the main thread. For AI-generated content: when creating landing pages, ensure the most important visual (hero banner, featured product image) appears first in the HTML source. The preload count of 1-2 is recommended -- preloading too many images wastes bandwidth and can actually hurt performance by competing with other critical resources.',
    'tags' => 'image-optimizer, preload, fetchpriority, decoding-async, lcp, critical-images',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'image_optimizer',
    'title' => 'WebP Detection and Fallback Behavior',
    'content' => 'The WebP detection feature uses JavaScript to check browser WebP support. When the browser does not support WebP, the module removes <source type="image/webp"> elements from <picture> tags so the browser falls back to PNG/JPG. This is frontend-only detection -- it does not convert images server-side. For AI-generated content that includes image recommendations: always ensure both WebP and fallback formats (JPG/PNG) are available in the media library. Product descriptions should not reference specific image formats since the module handles format selection automatically. For best SEO image performance, recommend uploading images in both WebP and original formats.',
    'tags' => 'image-optimizer, webp, fallback, picture-element, browser-support, image-format',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'image_optimizer',
    'title' => 'Image Placeholders and Perceived Performance',
    'content' => 'When using the Intersection Observer lazy loading strategy, the module supports placeholder types (configured via panth_imageoptimizer/lazy_loading/placeholder) including blur placeholders. Images fade in smoothly when loaded (configurable). The viewport threshold is 300px by default, meaning images start loading 300px before they scroll into view. For SEO: Google renders pages for indexing and evaluates visual completeness. Blur placeholders prevent empty spaces during rendering. When generating CMS content with image galleries or product grids, the lazy loading configuration ensures below-fold images do not block initial page rendering while still being available for Googlebot rendering.',
    'tags' => 'image-optimizer, placeholder, blur, fade-in, intersection-observer, perceived-performance',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =========================================================================
// MODULE: Panth_PerformanceOptimizer - JS/CSS Optimization
// =========================================================================

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'performance_optimizer',
    'title' => 'Performance Optimizer Module Overview',
    'content' => 'Panth_PerformanceOptimizer provides frontend performance improvements: third-party script deferral (async/defer for analytics, chat widgets, social embeds), font-display:swap injection to prevent Flash of Invisible Text (FOIT), x-cloak style injection for Alpine.js CLS prevention, automatic image dimension setting for CLS reduction, and iframe lazy loading via IntersectionObserver. The module is enabled by default with all features active. These optimizations directly impact Core Web Vitals scores (LCP, INP, CLS) which are Google ranking signals. When generating SEO recommendations, reference these features as active performance safeguards.',
    'tags' => 'performance-optimizer, script-deferral, font-display, cls-prevention, iframe-lazyload',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'performance_optimizer',
    'title' => 'Third-Party Script Deferral and Main Thread Impact',
    'content' => 'The PerformanceOptimizer automatically adds async/defer attributes to third-party scripts (analytics trackers, chat widgets, social media embeds, remarketing pixels) to reduce main thread blocking. Specific domains can be excluded from deferral via panth_performance/script_optimization/excluded_domains (one per line). Reducing main thread blocking improves INP (Interaction to Next Paint) and TBT (Total Blocking Time). For AI content generation: when recommending third-party integrations in content (e.g., embedded social feeds, review widgets, live chat), note that the PerformanceOptimizer will automatically defer these scripts. However, critical payment or tracking scripts may need to be excluded from deferral.',
    'tags' => 'performance-optimizer, script-deferral, async, defer, main-thread, inp, tbt',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'performance_optimizer',
    'title' => 'Font Display Swap and Text Rendering for SEO',
    'content' => 'The module injects font-display:swap into @font-face rules to prevent FOIT (Flash of Invisible Text). With font-display:swap, the browser immediately shows text using a fallback system font while custom fonts load, then swaps to the custom font once available. This is critical for both LCP and user experience -- Google measures LCP based on when the largest text block or image renders, and invisible text (FOIT) delays this measurement. For AI-generated content: text-heavy landing pages benefit significantly from font-display:swap because their LCP element is typically a heading or paragraph text block. Always ensure content-first design where text is the hero element.',
    'tags' => 'performance-optimizer, font-display-swap, foit, lcp, text-rendering, web-fonts',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'performance_optimizer',
    'title' => 'CLS Prevention: x-cloak and Image Dimensions',
    'content' => 'The module provides two CLS prevention features: (1) Injects [x-cloak]{display:none!important} CSS to hide Alpine.js elements until initialization, preventing unstyled content flash that causes layout shifts. This is especially important for Hyva themes which rely heavily on Alpine.js. (2) Automatically adds width/height attributes to images missing them (client-side, after load) to reserve space and prevent layout shifts when images load. For AI content generation: when creating PageBuilder content or CMS blocks that use Alpine.js directives (x-data, x-show, x-if), always add the x-cloak attribute to dynamic containers. This ensures the CLS protection is active.',
    'tags' => 'performance-optimizer, cls, x-cloak, alpine-js, image-dimensions, layout-shift',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'performance_optimizer',
    'title' => 'Iframe Lazy Loading for Embedded Content',
    'content' => 'The PerformanceOptimizer defers loading of iframes (YouTube, Vimeo, Google Maps, embedded forms) until they scroll into the viewport using IntersectionObserver. Iframes are among the heaviest page elements -- a single YouTube embed loads 500KB+ of JavaScript and creates multiple network connections. For AI-generated content: when product descriptions or CMS pages include embedded videos or maps, the iframe lazy loading ensures they do not block initial page rendering or hurt LCP scores. Include embedded content below the fold when possible. For video-heavy product pages, recommend using a thumbnail/play-button pattern instead of direct iframe embeds for above-fold content.',
    'tags' => 'performance-optimizer, iframe, lazy-loading, youtube, google-maps, embedded-content',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =========================================================================
// MODULE: Panth_MalwareScanner - Security Scanning
// =========================================================================

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'malware_scanner',
    'title' => 'Malware Scanner Overview and SEO Security Impact',
    'content' => 'Panth_MalwareScanner scans the Magento file system for malware, backdoors, and suspicious code using signature-based detection (literal matches, regex patterns, filename checks, path globs). It scans pub/media, app/code, vendor, var, generated, lib, bin, setup, and pub/static directories. Files over 2048KB are skipped. Scans run daily at 3 AM by default. SEO impact: Google flags compromised sites with "This site may be hacked" warnings in search results, devastating organic traffic. A hacked site can have malicious redirects injected, spam pages indexed, or phishing content served -- all causing immediate SEO damage. Regular malware scanning prevents these scenarios.',
    'tags' => 'malware-scanner, security, hacked-site, google-warning, seo-damage, file-scanning',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'malware_scanner',
    'title' => 'Malware Detection Signatures and File Types',
    'content' => 'The scanner uses four signature types: filename (known malicious file names), pathglob (suspicious file paths like PHP in image directories), literal (string patterns like eval/base64_decode combinations), and regex (complex pattern matching). It scans file types: php, phtml, phar, php3-7, phps, inc, htaccess, jpg, jpeg, png, gif, svg, html, htm, js. Image files are checked for embedded PHP code (polyglot attacks where malware is hidden inside image file headers). The scanner validates magic bytes for JPEG, PNG, GIF, WEBP, and BMP to distinguish clean images from polyglot shells. SEO relevance: malicious JavaScript injected into .js or .html files can cause unwanted redirects that Googlebot follows, leading to deindexing.',
    'tags' => 'malware-scanner, signatures, detection, polyglot, php-injection, file-types',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'malware_scanner',
    'title' => 'Scan Notifications and Severity Levels',
    'content' => 'The MalwareScanner sends email notifications when threats are detected, configurable by minimum severity level (low, medium, high, critical). Notifications go to configured recipient emails using a selected email sender identity. Scan results are persisted in panth_malware_scan_result table with file path, SHA-256 hash, file size, severity, matched signatures, code snippet, first_seen/last_seen timestamps, and notification status. For SEO monitoring: integrate malware scan results with your SEO workflow. A new high-severity finding should trigger immediate investigation as it may indicate an active compromise that could lead to Google Safe Browsing warnings and search result suppression.',
    'tags' => 'malware-scanner, notifications, severity, email-alerts, monitoring, safe-browsing',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'malware_scanner',
    'title' => 'Malware Prevention Best Practices for SEO',
    'content' => 'SEO-focused malware prevention: (1) Run daily scans (default cron: 0 3 * * *) to catch injections early before Googlebot discovers them. (2) Monitor pub/media directory closely -- it is writable and a common malware drop zone. (3) Check .htaccess files for malicious redirects that send Googlebot to spam sites. (4) Watch for new PHP files in image directories (pub/media/catalog/) which indicate webshell uploads. (5) Review scan results regularly in the admin grid. (6) If malware is found, clean it immediately and request a Google Search Console security review. Recovery from a "hacked site" label can take weeks. The scanner excludes var/cache, var/log, var/session, generated/code and other non-risky paths to reduce false positives.',
    'tags' => 'malware-scanner, prevention, best-practices, htaccess, webshell, search-console',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =========================================================================
// MODULE: Panth_NotFoundPage - Custom 404 Page
// =========================================================================

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'not_found_page',
    'title' => 'Custom 404 Page SEO Benefits',
    'content' => 'Panth_NotFoundPage replaces the default Magento 404 page with a configurable, user-friendly design. The custom page includes: a customizable heading (default: "Page Not Found"), subheading message, integrated search bar, popular category links (dynamically pulled from top-level active menu categories, limited to 6), and optional contact information with email. A well-designed 404 page is critical for SEO because it reduces bounce rate from broken links, guides users to valid content, and preserves session engagement. Google recommends custom 404 pages that help users find what they need. The module correctly returns HTTP 404 status while providing a helpful page experience.',
    'tags' => 'not-found-page, 404, custom-error, bounce-rate, user-experience, seo',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'not_found_page',
    'title' => '404 Page Search Integration and Link Recovery',
    'content' => 'The NotFoundPage module includes a search bar (linked to catalogsearch/result) that helps users find products when they land on a 404 page. It also displays up to 6 top-level store categories dynamically (active categories with include_in_menu=1, sorted by position). This is an SEO best practice: instead of losing the visitor, redirect their intent to valid product searches or category browsing. For AI content recommendations: when identifying broken URLs in crawl reports, suggest creating 301 redirects for high-traffic 404 URLs. The custom 404 page is the safety net for URLs that cannot be redirected. Configure under Panth Extensions > 404 Not Found Page.',
    'tags' => 'not-found-page, 404, search, categories, link-recovery, redirects',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'not_found_page',
    'title' => '404 Monitoring and Crawl Error Management',
    'content' => 'A high volume of 404 errors wastes crawl budget -- Googlebot spends time requesting non-existent URLs instead of crawling valid content. The NotFoundPage module provides a user-friendly experience for these errors, but the root cause should still be addressed. For AI-generated SEO audits: (1) Check Google Search Console for crawl errors and 404 spikes. (2) Identify referring pages with broken links and fix or redirect them. (3) Monitor for soft 404s where pages return 200 status but show error content -- this is worse than a proper 404 because Google wastes crawl budget indexing empty pages. (4) Use the AdvancedSEO URL rewrite features to create bulk 301 redirects for moved or renamed products and categories.',
    'tags' => 'not-found-page, 404, crawl-errors, crawl-budget, soft-404, url-rewrites',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =========================================================================
// CROSS-MODULE: Performance + SEO Integration Guidelines
// =========================================================================

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'cross_module_integration',
    'title' => 'Combined Performance Stack for Optimal SEO',
    'content' => 'For maximum SEO performance impact, enable the full Panth performance stack: (1) Core: enable caching for reduced TTFB. (2) CoreWebVitals: enable monitoring with RUM to track real user metrics sent to GA4. (3) CacheManager: enable warmup for all page types with smart invalidation to ensure Googlebot always hits cached pages. (4) ImageOptimizer: enable with native lazy loading, above-fold exclusion for first 3 images, preload for first 2 images, fetchpriority and decode_async enabled. (5) PerformanceOptimizer: enable all features (script deferral, font-display:swap, x-cloak, image dimensions, iframe lazy loading). This combination addresses all three Core Web Vitals (LCP, INP, CLS) which are confirmed Google ranking signals since 2021.',
    'tags' => 'integration, performance-stack, core-web-vitals, ranking-signals, configuration',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'cross_module_integration',
    'title' => 'AI Content Generation and Performance Awareness',
    'content' => 'When generating AI meta content, product descriptions, or CMS page content, the AI should consider performance implications: (1) Keep meta descriptions under 160 characters to prevent truncation in SERPs. (2) Generated HTML content should place critical text and the primary image above the fold. (3) Avoid recommending excessive inline images in descriptions -- use lazy-loaded images below the fold. (4) For PageBuilder content, prefer contained rows for text-heavy sections to maintain consistent max-width. (5) When including embedded videos, place them below the fold so iframe lazy loading activates. (6) After bulk AI content updates, remind users to run cache warmup via CacheManager. (7) Monitor Core Web Vitals after content changes to catch any performance regressions caused by new content.',
    'tags' => 'ai-content, performance-awareness, meta-generation, content-guidelines, bulk-update',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_infrastructure',
    'subcategory' => 'cross_module_integration',
    'title' => 'Security and SEO: MalwareScanner Integration',
    'content' => 'Security and SEO are deeply connected in the Panth ecosystem. The MalwareScanner protects against threats that directly harm search rankings: (1) Malicious redirects in .htaccess cause Googlebot to see different content than users (cloaking), leading to manual penalties. (2) Injected spam links in templates pass PageRank to malicious sites, degrading your site authority. (3) Cryptominer scripts increase page load time dramatically, hurting Core Web Vitals. (4) SEO spam injection (Japanese keyword hack, pharma hack) creates thousands of spam pages that dilute your site quality signals. For AI-generated SEO audits, include security status from MalwareScanner as a key health indicator alongside technical SEO metrics.',
    'tags' => 'malware-scanner, security-seo, cloaking, spam-injection, manual-penalty, site-health',
    'is_active' => 1,
    'sort_order' => $sort++,
];

return $entries;
