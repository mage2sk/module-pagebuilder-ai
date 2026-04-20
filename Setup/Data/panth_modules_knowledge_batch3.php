<?php
/**
 * Panth AdvancedSEO AI Knowledge Base - Batch 3
 * Panth Module Integration with CMS Content & SEO
 *
 * Covers: AdvancedCart, CheckoutExtended, CheckoutSuccess, CustomOptions,
 *         LowStockNotification, PriceDropAlert, OrderAttachments, ZipcodeValidation
 *
 * Copyright (c) Panth Infotech. All rights reserved.
 */

return [
    // =========================================================================
    // MODULE: AdvancedCart (Panth_AdvancedCart)
    // =========================================================================

    [
        'category' => 'panth_modules',
        'subcategory' => 'advanced_cart',
        'title' => 'AdvancedCart Module Overview and SEO Impact',
        'content' => 'Panth_AdvancedCart enhances the Magento cart page with conversion-boosting features. Config path: panth_advancedcart/*. Features include: free shipping progress bar (threshold-based with {{remaining}} placeholder), quantity +/- buttons, trust badges (secure_checkout, money_back, free_returns, fast_shipping, support_24_7, quality_guarantee), continue shopping button, cart savings display, estimated delivery dates (configurable min/max days), order notes (saved to quote and sales_order tables via panth_order_note column), and enhanced empty cart page. SEO impact: the enhanced empty cart page with customizable heading, message, and CTA button helps retain users instead of bouncing them, reducing cart abandonment bounce rate which indirectly improves SEO signals.',
        'tags' => 'advanced_cart, cart_page, free_shipping, trust_badges, conversion, seo, bounce_rate',
        'is_active' => 1,
        'sort_order' => 0,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'advanced_cart',
        'title' => 'AdvancedCart Free Shipping Bar CMS Integration',
        'content' => 'The AdvancedCart free shipping progress bar can be referenced in CMS content to create cohesive messaging. Config: panth_advancedcart/free_shipping_bar/enabled, threshold, message_progress, message_achieved. Default threshold: $50. The progress message supports {{remaining}} placeholder. When creating CMS pages or blocks about shipping policies, reference the same free shipping threshold to maintain consistency. Example CMS content: "Enjoy FREE shipping on all orders over $50!" should match the configured threshold. The bar appears on checkout_cart_index layout. For SEO, create a dedicated shipping policy CMS page that references these thresholds and link to it from product pages via structured data.',
        'tags' => 'advanced_cart, free_shipping, cms_content, shipping_policy, threshold, seo',
        'is_active' => 1,
        'sort_order' => 1,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'advanced_cart',
        'title' => 'AdvancedCart Trust Badges and Schema Markup',
        'content' => 'AdvancedCart trust badges (config: panth_advancedcart/trust_badges/badges) display security and guarantee icons on the cart page. Available badges: secure_checkout, money_back, free_returns, fast_shipping, support_24_7, quality_guarantee. For SEO integration, complement these visual trust signals with structured data on CMS pages. Create a CMS block with return policy content and add hasMerchantReturnPolicy schema markup. Create a shipping policy page with shippingDetails schema. Trust badges reduce cart abandonment, improving conversion signals that search engines factor into rankings. When building CMS landing pages, reference trust badges text (e.g., "30-Day Money Back Guarantee", "Free Returns") consistently across site content for E-E-A-T signals.',
        'tags' => 'advanced_cart, trust_badges, schema_markup, eeat, structured_data, cms_block',
        'is_active' => 1,
        'sort_order' => 2,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'advanced_cart',
        'title' => 'AdvancedCart Empty Cart Page SEO Optimization',
        'content' => 'The enhanced empty cart feature (config: panth_advancedcart/empty_cart/*) replaces the default blank empty cart with a designed page. Configurable fields: heading (default: "Your cart is empty"), message (encouraging text), button_label (default: "Start Shopping"), with the continue shopping URL (panth_advancedcart/continue_shopping/url). For SEO, the empty cart page should not be indexed (it is behind checkout_cart_index route). However, the customizable content helps reduce bounce rate when users reach an empty cart. Point the continue shopping button to a category or CMS landing page that is SEO-optimized. The message text should include internal navigation cues to guide users toward crawlable, indexed pages.',
        'tags' => 'advanced_cart, empty_cart, bounce_rate, internal_linking, cms, seo',
        'is_active' => 1,
        'sort_order' => 3,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'advanced_cart',
        'title' => 'AdvancedCart Order Notes and Estimated Delivery for Content Strategy',
        'content' => 'AdvancedCart order notes (config: panth_advancedcart/order_notes/*) add a panth_order_note column to quote, sales_order, and sales_order_grid tables. Max length configurable (default 500 chars). Estimated delivery (config: panth_advancedcart/estimated_delivery/*) shows a date range (default 3-7 days). For CMS and SEO integration: create a FAQ CMS page or block answering "How long does delivery take?" and "Can I add special instructions?" referencing these features. Use FAQPage schema markup on the FAQ page for rich results. The estimated delivery information should align with shipping policy CMS content. These features provide content for OfferShippingDetails structured data on product pages.',
        'tags' => 'advanced_cart, order_notes, estimated_delivery, faq, schema_markup, cms_page',
        'is_active' => 1,
        'sort_order' => 4,
    ],

    // =========================================================================
    // MODULE: CheckoutExtended (Panth_CheckoutExtended)
    // =========================================================================

    [
        'category' => 'panth_modules',
        'subcategory' => 'checkout_extended',
        'title' => 'CheckoutExtended Module Overview and SEO Considerations',
        'content' => 'Panth_CheckoutExtended provides a modern multi-column checkout layout. Config path: panth_checkout_extended/*. Features: configurable column layout (2 or 3 columns), sidebar position (left/right), sticky sidebar, card styling (elevated/flat), step indicators, accent color customization, compact/standard form fields with placeholders and tooltips, newsletter subscription during checkout, default shipping/payment method selection, billing title visibility, and custom CSS/JS injection. The module uses extension_attributes on PaymentInterface for newsletter subscription (panth_subscribe_newsletter). Layout handles: checkout_index_index and panth_checkout_extended_active. SEO considerations: checkout pages are typically noindex, but the newsletter integration helps build email lists for content distribution, and the streamlined UX reduces abandonment rates.',
        'tags' => 'checkout_extended, checkout, multi_column, layout, newsletter, seo, conversion',
        'is_active' => 1,
        'sort_order' => 5,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'checkout_extended',
        'title' => 'CheckoutExtended Newsletter Integration for SEO Content Distribution',
        'content' => 'CheckoutExtended includes a newsletter subscription checkbox at checkout. Config: panth_checkout_extended/newsletter/enabled (default: yes), field_label (default: "Subscribe to our newsletter"), default_checked (default: yes). This uses a PaymentInterface extension attribute (panth_subscribe_newsletter). For SEO strategy, the checkout newsletter opt-in feeds your email marketing list, which drives traffic to new CMS content, blog posts, and landing pages. When creating CMS content strategy, plan newsletter-exclusive content that links back to SEO-optimized pages. The default-checked behavior maximizes subscriber count. Customize the label in CMS-like fashion to highlight content value: e.g., "Get exclusive deals & style guides" instead of generic "Subscribe to newsletter".',
        'tags' => 'checkout_extended, newsletter, email_marketing, content_distribution, seo_strategy',
        'is_active' => 1,
        'sort_order' => 6,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'checkout_extended',
        'title' => 'CheckoutExtended Layout and UX for Conversion-Driven SEO',
        'content' => 'CheckoutExtended applies body classes for CSS-driven layout: panth-checkout-extended, panth-checkout-{2|3}col, panth-sidebar-{left|right}, panth-card-{elevated|flat}, panth-sidebar-sticky, panth-step-indicators, panth-form-{compact|standard}, panth-form-placeholders, panth-form-tooltips. These classes come from the Helper\\Data::getCheckoutBodyClass() method. For SEO indirect benefits: faster, cleaner checkout reduces cart abandonment. Google considers Core Web Vitals on all pages including checkout. The elevated card style and compact form mode reduce visual clutter and improve CLS scores. Custom CSS (panth_checkout_extended/custom_code/custom_css) and JS injection allow conversion tracking scripts that measure checkout flow for data-driven SEO decisions.',
        'tags' => 'checkout_extended, layout, body_classes, core_web_vitals, conversion, ux',
        'is_active' => 1,
        'sort_order' => 7,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'checkout_extended',
        'title' => 'CheckoutExtended Custom Code Injection for Analytics and SEO Tracking',
        'content' => 'CheckoutExtended supports custom CSS and JS injection via admin config: panth_checkout_extended/custom_code/custom_css and custom_js. This allows adding conversion tracking, Google Analytics enhanced ecommerce events, and A/B testing scripts directly to checkout without template modifications. For SEO integration: inject GA4 begin_checkout and add_shipping_info events to measure checkout funnel. Add Meta Pixel checkout events for remarketing. Track which shipping methods are selected (relates to shipping CMS content optimization). The custom JS field can contain structured data push events for DataLayer. Combine with CheckoutSuccess tracking scripts for complete purchase funnel measurement that informs content and SEO strategy.',
        'tags' => 'checkout_extended, custom_code, analytics, tracking, ga4, conversion_tracking',
        'is_active' => 1,
        'sort_order' => 8,
    ],

    // =========================================================================
    // MODULE: CheckoutSuccess (Panth_CheckoutSuccess)
    // =========================================================================

    [
        'category' => 'panth_modules',
        'subcategory' => 'checkout_success',
        'title' => 'CheckoutSuccess Module Overview and CMS Block Integration',
        'content' => 'Panth_CheckoutSuccess replaces the default Magento success page with a customizable layout. Config path: panth_checkout_success/*. Layout options: two-column or single-column (via Panth\\CheckoutSuccess\\Model\\Config\\Source\\SuccessLayout). Togglable sections: order number, order date, ordered items, order totals, shipping address, payment method, create account (for guests), continue shopping button. Key feature: CMS block integration via panth_checkout_success/content/cms_block which accepts any CMS block ID. Custom thank-you title and message are configurable. Layout handle: checkout_onepage_success. The CMS block on the success page is an excellent place to add cross-sell content, referral programs, social sharing links, or survey forms that feed back into content strategy.',
        'tags' => 'checkout_success, success_page, cms_block, thank_you, order_confirmation, layout',
        'is_active' => 1,
        'sort_order' => 9,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'checkout_success',
        'title' => 'CheckoutSuccess CMS Block for Post-Purchase SEO Content',
        'content' => 'The CheckoutSuccess CMS block field (panth_checkout_success/content/cms_block) uses Magento\\Cms\\Model\\Config\\Source\\Block as its source model, allowing selection of any CMS static block. This block renders below order details on the success page. SEO-relevant CMS block content ideas: (1) Social sharing buttons encouraging customers to share their purchase, generating social signals. (2) Links to product care/usage CMS pages that build internal link equity. (3) Review request with link to the product page, boosting UGC content for SEO. (4) Referral program details linking to a dedicated CMS landing page. (5) Blog post recommendations based on purchased category. Create a CMS block with identifier like "checkout-success-content" and select it in config.',
        'tags' => 'checkout_success, cms_block, social_sharing, reviews, ugc, internal_linking, seo',
        'is_active' => 1,
        'sort_order' => 10,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'checkout_success',
        'title' => 'CheckoutSuccess Conversion Tracking Scripts for SEO Analytics',
        'content' => 'CheckoutSuccess supports custom tracking scripts via panth_checkout_success/tracking/custom_scripts. Supports HTML/JS with template variables: {{orderId}}, {{orderTotal}}, {{orderSubtotal}}, {{orderCurrency}}, {{customerEmail}}, {{paymentTitle}}, {{shippingTitle}}, {{couponCode}}, {{orderItemCount}}, {{shippingAmount}}, {{taxAmount}}, {{discountAmount}}. Variables are processed through Magento Escaper::escapeJs() for XSS safety. Use cases for SEO: inject GA4 purchase event with revenue data, Meta Conversion API events, Google Ads conversion tracking, and custom DataLayer pushes. This data connects marketing attribution to SEO-driven traffic, proving organic search ROI. Example: track which landing pages (CMS pages) lead to the highest conversion value.',
        'tags' => 'checkout_success, tracking_scripts, ga4, conversion, analytics, template_variables',
        'is_active' => 1,
        'sort_order' => 11,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'checkout_success',
        'title' => 'CheckoutSuccess Guest Account Creation for Customer Engagement',
        'content' => 'CheckoutSuccess includes a guest-to-account conversion feature (panth_checkout_success/content/show_create_account). When enabled, guest customers see a registration form on the success page. This is valuable for SEO strategy because registered customers: (1) return more frequently, improving direct traffic signals, (2) can leave product reviews which generate SEO-valuable UGC, (3) can be targeted with personalized email content linking to CMS pages, (4) create wishlists that indicate product relevance signals. Combine with the CMS block to add incentive messaging like "Create an account to track your order and get 10% off your next purchase" linking to a dedicated benefits CMS page.',
        'tags' => 'checkout_success, guest_account, customer_retention, ugc, reviews, seo_strategy',
        'is_active' => 1,
        'sort_order' => 12,
    ],

    // =========================================================================
    // MODULE: CustomOptions (Panth_CustomOptions)
    // =========================================================================

    [
        'category' => 'panth_modules',
        'subcategory' => 'custom_options',
        'title' => 'CustomOptions Module Overview and Product Page SEO',
        'content' => 'Panth_CustomOptions enhances Magento product custom options with improved styling on the frontend. Config path: panth_customoptions/general/enabled. The module works via frontend DI (etc/frontend/di.xml) to override default custom options rendering. It provides modern, accessible styling for dropdowns, swatches, text inputs, file uploads, and other custom option types. SEO relevance: well-styled custom options improve user engagement on product pages, reducing bounce rate and increasing time-on-page. Clean custom options rendering also ensures option text is properly readable by search engine crawlers, contributing to product page content richness. The module does not add new database tables - it enhances existing Magento custom options display.',
        'tags' => 'custom_options, product_page, styling, ux, seo, accessibility',
        'is_active' => 1,
        'sort_order' => 13,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'custom_options',
        'title' => 'CustomOptions and Product Structured Data Integration',
        'content' => 'Custom options affect product structured data and SEO. When products have custom options with price modifiers, the price range displayed by Panth_CustomOptions should align with Product schema markup (offers.priceSpecification). For CMS integration: create product description CMS blocks that explain available customization options, using keywords customers search for (e.g., "personalized engraving", "custom color selection"). These option descriptions become crawlable content. When custom options include "required" fields, ensure the product page meta description mentions customization availability. Example meta: "Custom [Product Name] with personalized options - Choose from [X] colors, sizes, and engraving styles." Use AdvancedSEO meta templates with custom option attributes.',
        'tags' => 'custom_options, structured_data, product_schema, meta_description, cms_blocks, seo',
        'is_active' => 1,
        'sort_order' => 14,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'custom_options',
        'title' => 'CustomOptions Accessibility and Core Web Vitals',
        'content' => 'Panth_CustomOptions enhances the default Magento custom options with Hyva-compatible Tailwind CSS styling. This impacts Core Web Vitals metrics that affect SEO: (1) Reduced CLS - properly sized option selectors and inputs prevent layout shifts during page load. (2) Improved INP - enhanced click/tap targets on options improve Interaction to Next Paint. (3) Better LCP - streamlined CSS reduces render-blocking resources. For CMS pages that embed products with custom options (via widgets or PageBuilder product blocks), ensure the CustomOptions module is enabled to maintain consistent, performant rendering. The module respects store-level scope, allowing different styling behaviors per store view for multi-language SEO strategies.',
        'tags' => 'custom_options, accessibility, core_web_vitals, cls, inp, lcp, hyva, performance',
        'is_active' => 1,
        'sort_order' => 15,
    ],

    // =========================================================================
    // MODULE: LowStockNotification (Panth_LowStockNotification)
    // =========================================================================

    [
        'category' => 'panth_modules',
        'subcategory' => 'low_stock_notification',
        'title' => 'LowStockNotification Module Overview and SEO Integration',
        'content' => 'Panth_LowStockNotification displays a "Notify Me" subscription form on out-of-stock product pages. Config path: lowstocknotification/*. Features: guest subscriptions allowed (lowstocknotification/general/allow_guests), configurable email sender and template, cron-based notifications (every 6 hours via lowstocknotification_stock_alert cron job), design colors managed via theme-config.json (modules.low-stock-notification section), and placement control (enable_on_product_page, display_position: after_price). Database table: panth_stock_alert with columns: alert_id, customer_id, product_id, email, customer_name, store_id, status, created_at, sent_at. Foreign keys link to customer_entity and catalog_product_entity. SEO benefit: keeps out-of-stock product pages active and indexed rather than returning 404s or being noindexed.',
        'tags' => 'low_stock, notify_me, stock_alert, out_of_stock, cron, email, seo, product_page',
        'is_active' => 1,
        'sort_order' => 16,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'low_stock_notification',
        'title' => 'LowStockNotification Out-of-Stock Pages SEO Strategy',
        'content' => 'Out-of-stock product pages are an SEO concern because they may lose rankings if removed. Panth_LowStockNotification solves this by keeping pages active with a "Notify Me" form that provides user value. SEO best practices with this module: (1) Keep out-of-stock pages indexed with canonical pointing to self. (2) Use AdvancedSEO meta robots to set these pages to "index, nofollow" to preserve link equity. (3) Add CMS content below the notify form explaining expected restock dates. (4) Use Product schema with Offer availability set to "BackOrder" or "PreOrder" instead of removing the page. (5) The notify form adds interactive content that signals page relevance to crawlers. Configure display_position as "after_price" to keep the form prominent without pushing SEO content below the fold.',
        'tags' => 'low_stock, out_of_stock, seo_strategy, indexing, canonical, schema, meta_robots',
        'is_active' => 1,
        'sort_order' => 17,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'low_stock_notification',
        'title' => 'LowStockNotification Email Template SEO for Traffic Recovery',
        'content' => 'The back-in-stock email template (Panth_LowStockNotification::stock_alert.html, template ID: lowstocknotification_email_email_template) sends notifications when products return to stock. Email sender configurable via lowstocknotification/email/sender (default: general contact). For SEO-driven content strategy: (1) Include links to the product page in the email, driving direct traffic that improves page authority signals. (2) Add links to related CMS content pages (buying guides, category pages) in the email template. (3) Include social sharing buttons to amplify reach. (4) Customize the template under Marketing > Email Templates to include rich product information. (5) Track email-driven traffic in GA4 with UTM parameters to measure how stock alerts contribute to organic page performance. The cron runs every 6 hours (0 */6 * * *), ensuring timely notifications.',
        'tags' => 'low_stock, email_template, traffic_recovery, back_in_stock, utm, analytics, cron',
        'is_active' => 1,
        'sort_order' => 18,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'low_stock_notification',
        'title' => 'LowStockNotification Admin Grid and CMS Landing Pages',
        'content' => 'LowStockNotification includes an admin grid (accessible via adminhtml routes) to manage stock alert subscriptions. The panth_stock_alert table tracks all subscriptions with status tracking and sent_at timestamps. For CMS and SEO integration: (1) Create a "Coming Back Soon" CMS landing page listing products with active stock alerts - this creates a crawlable, updateable page with fresh content. (2) Use subscription count data to prioritize which out-of-stock product pages need the most SEO attention. (3) Build a "Most Wanted" CMS section showing products with highest alert counts, creating engaging content that earns links. (4) When building category pages, products with stock alerts should retain their position and show the notify form rather than being hidden, preserving internal link structure.',
        'tags' => 'low_stock, admin_grid, cms_landing_page, coming_soon, internal_linking, seo',
        'is_active' => 1,
        'sort_order' => 19,
    ],

    // =========================================================================
    // MODULE: PriceDropAlert (Panth_PriceDropAlert)
    // =========================================================================

    [
        'category' => 'panth_modules',
        'subcategory' => 'price_drop_alert',
        'title' => 'PriceDropAlert Module Overview and SEO Value',
        'content' => 'Panth_PriceDropAlert allows customers to subscribe to price drop notifications on product pages. Config path: pricedropalert/*. Features: guest subscriptions (pricedropalert/general/allow_guests), configurable email sender and template, cron-based price checking (daily at midnight via pricedropalert_price_alert job, configurable frequency in hours via pricedropalert/cron/frequency, default 24h). Database table: panth_price_alert with columns: alert_id, customer_id, product_id, email, customer_name, subscribed_price, target_price, store_id, status, created_at, sent_at. The subscribed_price and target_price columns enable sophisticated price tracking. Design colors managed via theme-config.json (modules.price-drop-alert). SEO benefit: adds interactive engagement to product pages and drives return visits when prices drop.',
        'tags' => 'price_drop, price_alert, subscription, cron, email, product_page, seo, engagement',
        'is_active' => 1,
        'sort_order' => 20,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'price_drop_alert',
        'title' => 'PriceDropAlert and Product Page Content Enrichment',
        'content' => 'The PriceDropAlert subscription form adds meaningful interactive content to product pages. For SEO integration: (1) The "Get Price Drop Alerts" form adds crawlable text content and form elements that signal page interactivity. (2) Combine with AdvancedSEO product meta templates to include "Price alerts available" in meta descriptions for long-tail queries like "notify me when [product] goes on sale". (3) Create a CMS page explaining the price alert feature as a buying guide, targeting keywords like "price tracking", "deal alerts", "sale notifications". (4) The subscribed_price field enables creating dynamic CMS content showing "price history" or "savings" on product pages. (5) Use the alert subscription count as a popularity signal when building "trending products" CMS sections.',
        'tags' => 'price_drop, product_page, meta_description, cms_page, content_enrichment, seo',
        'is_active' => 1,
        'sort_order' => 21,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'price_drop_alert',
        'title' => 'PriceDropAlert Email Notifications for SEO Traffic Generation',
        'content' => 'PriceDropAlert sends notifications via the price_alert.html email template (Panth_PriceDropAlert::price_alert.html, template ID: pricedropalert_email_email_template). Cron job pricedropalert_price_alert runs daily at midnight (0 0 * * *) comparing current prices against subscribed_price values. For SEO-driven content strategy in email templates: (1) Link directly to the product page with UTM parameters to track email-to-organic conversion paths. (2) Include links to category pages and related CMS content (buying guides, comparison pages). (3) Add "share this deal" social links to generate social signals. (4) Include links to review pages encouraging post-purchase reviews. (5) Cross-promote other products from the same category with links to category landing pages. Email-driven return traffic improves page engagement metrics that correlate with search rankings.',
        'tags' => 'price_drop, email_template, traffic_generation, utm, cron, social_signals, seo',
        'is_active' => 1,
        'sort_order' => 22,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'price_drop_alert',
        'title' => 'PriceDropAlert Sale Landing Pages and Structured Data',
        'content' => 'PriceDropAlert data can power SEO-optimized sale and deals CMS pages. Using the panth_price_alert table data: (1) Build a "Current Deals" CMS landing page featuring products where prices have recently dropped, targeting "sale" and "deals" keywords. (2) Add Sale event structured data (schema.org/Sale) to these pages referencing actual price drops. (3) Create category-specific deal pages (e.g., "/electronics-deals") with Offer schema showing priceValidUntil. (4) The subscribed_price vs. current price delta can be displayed as "Save X%" on CMS promotional banners. (5) Submit deal pages to Google Merchant Center for free listings. Combine with AdvancedSEO sitemap profiles to ensure deal pages are included in the sitemap with high change frequency.',
        'tags' => 'price_drop, sale_page, structured_data, deals, cms_landing_page, merchant_center, sitemap',
        'is_active' => 1,
        'sort_order' => 23,
    ],

    // =========================================================================
    // MODULE: OrderAttachments (Panth_OrderAttachments)
    // =========================================================================

    [
        'category' => 'panth_modules',
        'subcategory' => 'order_attachments',
        'title' => 'OrderAttachments Module Overview and SEO Considerations',
        'content' => 'Panth_OrderAttachments allows customers to upload files with their orders. Config path: panth_orderattachments/*. Upload settings: allowed extensions (default: pdf,jpg,jpeg,png,gif,doc,docx,zip), max file size (default: 10MB), max files per item (default: 3). Display options: upload button label (default: "Attach Files"), show in cart (panth_orderattachments/display/show_in_cart), show in checkout (panth_orderattachments/display/show_in_checkout). Database table: panth_order_attachment storing attachment_id, quote_item_id, order_item_id, order_id, product_id, customer_id, original_filename, stored_filename, file_path, file_size, mime_type, file_extension, customer_note, status. Layout handles: catalog_product_view, hyva_catalog_product_view, sales_order_view. Admin routes and menu available for managing attachments.',
        'tags' => 'order_attachments, file_upload, cart, checkout, product_page, configuration',
        'is_active' => 1,
        'sort_order' => 24,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'order_attachments',
        'title' => 'OrderAttachments Product Page SEO and Custom Product Content',
        'content' => 'OrderAttachments adds file upload functionality to product pages (via catalog_product_view and hyva_catalog_product_view layouts). For SEO integration: (1) Products requiring attachments (custom/personalized products) should have meta descriptions mentioning upload capability, e.g., "Upload your design for custom printing". (2) Create CMS content blocks explaining the upload process, file requirements, and supported formats - this adds keyword-rich content to product pages. (3) Use AdvancedSEO meta templates to include "custom upload available" for products in specific attribute sets that use attachments. (4) Build a FAQ CMS section answering "What file formats are accepted?", "What is the maximum file size?" using FAQPage schema. (5) The customer_note field in attachments table allows text content that could inform product review content strategy.',
        'tags' => 'order_attachments, product_page, meta_description, cms_content, faq_schema, seo',
        'is_active' => 1,
        'sort_order' => 25,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'order_attachments',
        'title' => 'OrderAttachments CMS Guide Pages for Personalized Products',
        'content' => 'OrderAttachments enables personalized/custom product workflows that benefit from supporting CMS content. SEO content strategy: (1) Create a "How to Upload Your Design" CMS page with step-by-step instructions, targeting long-tail keywords like "custom print upload guide", "how to submit artwork for printing". (2) Build a "File Preparation Guide" CMS page explaining resolution requirements, color modes, and file formats (pdf, jpg, png, etc.) - these are searchable queries. (3) Create category-specific CMS blocks for customizable product categories explaining what attachments are needed. (4) The allowed_extensions config (pdf,jpg,jpeg,png,gif,doc,docx,zip) should be documented on an SEO-optimized help/support CMS page. (5) Internal link from product pages with upload capability to these guide pages to distribute link equity and improve user experience.',
        'tags' => 'order_attachments, cms_guide, personalized_products, how_to, internal_linking, seo',
        'is_active' => 1,
        'sort_order' => 26,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'order_attachments',
        'title' => 'OrderAttachments Security and Indexation Considerations',
        'content' => 'OrderAttachments stores files on the server with stored_filename (hashed) separate from original_filename for security. Important SEO and security considerations: (1) Uploaded files in the attachment storage directory MUST be blocked from search engine indexing via robots.txt - add "Disallow: /media/panth/orderattachments/" to prevent customer files from appearing in search results. (2) Use AdvancedSEO robots.txt manager to add this rule. (3) The admin attachment viewer (via adminhtml routes and menu: Panth_OrderAttachments::config ACL) should remain behind authentication. (4) File downloads should use controller-based delivery (not direct file URLs) to prevent direct linking. (5) For the frontend upload routes (frontend/routes.xml), ensure AJAX upload endpoints return proper no-cache headers to prevent CDN caching of upload responses.',
        'tags' => 'order_attachments, security, robots_txt, indexation, file_storage, seo',
        'is_active' => 1,
        'sort_order' => 27,
    ],

    // =========================================================================
    // MODULE: ZipcodeValidation (Panth_ZipcodeValidation)
    // =========================================================================

    [
        'category' => 'panth_modules',
        'subcategory' => 'zipcode_validation',
        'title' => 'ZipcodeValidation Module Overview and Regional SEO',
        'content' => 'Panth_ZipcodeValidation provides Indian PIN code validation on frontend forms. Config path: zipcode_validation/*. Validation points: checkout (validate_on_checkout), customer account (validate_on_account), registration (validate_on_registration). Displays configurable error message (default: "Please enter a valid Indian PIN code (6 digits)") and success message with state name ({state} placeholder, default format: "Valid PIN code for {state}"). Display colors: success (#007a33), error (#e02b27). Database table: panth_zipcode_range with columns: range_id, country_id, state_code, state_name, zip_start, zip_end, is_active. Ranges managed via admin grid (Panth Infotech > Zipcode Validation > Manage Ranges) with import/export capability. SEO relevance: regional validation data maps to geographic SEO targeting for Indian market stores.',
        'tags' => 'zipcode_validation, pincode, india, regional, validation, checkout, seo',
        'is_active' => 1,
        'sort_order' => 28,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'zipcode_validation',
        'title' => 'ZipcodeValidation Serviceability CMS Pages for Local SEO',
        'content' => 'ZipcodeValidation PIN code ranges (panth_zipcode_range table with country_id, state_code, state_name, zip_start, zip_end) provide data for creating local SEO content. Strategy: (1) Create state-specific CMS landing pages (e.g., "/delivery-in-maharashtra", "/shipping-to-karnataka") listing serviceable PIN code ranges for each state. (2) Add LocalBusiness or ServiceArea schema markup to these pages with areaServed properties matching the validated states. (3) Build a "Check Delivery Availability" CMS page with the PIN code checker widget, targeting "delivery to [pincode]" and "shipping available in [city]" queries. (4) Create city-specific landing pages for high-traffic areas derived from the most-used PIN code ranges. (5) Use hreflang tags (via AdvancedSEO) for multi-language Indian content (Hindi, Tamil, etc.) targeting regional searches.',
        'tags' => 'zipcode_validation, local_seo, regional_pages, schema_markup, service_area, hreflang',
        'is_active' => 1,
        'sort_order' => 29,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'zipcode_validation',
        'title' => 'ZipcodeValidation Delivery Information for Product Page SEO',
        'content' => 'ZipcodeValidation state-based validation enhances product pages with delivery information. For SEO integration: (1) Add a "Check Delivery" widget on product pages that validates PIN codes and shows estimated delivery - this interactive element increases time-on-page. (2) Use the success_message_format config ("Valid PIN code for {state}") to display state-specific delivery estimates. (3) Enrich product structured data with shippingDetails and deliveryTime schema for Indian regions. (4) Create CMS blocks per product category showing "We deliver to X+ PIN codes across India" as social proof content. (5) The validation data (28+ Indian states) can generate areaServed schema on the homepage or about page, signaling geographic relevance to Google India. (6) Add "Free delivery to [state]" messaging in CMS promotional blocks when applicable, targeting local delivery search queries.',
        'tags' => 'zipcode_validation, product_page, delivery_check, structured_data, shipping_schema, local_seo',
        'is_active' => 1,
        'sort_order' => 30,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'zipcode_validation',
        'title' => 'ZipcodeValidation Data Import and SEO Content Automation',
        'content' => 'ZipcodeValidation supports import/export of PIN code ranges via the admin grid (Panth Infotech > Zipcode Validation > Manage Ranges). This data management capability enables SEO content automation: (1) Export the active ranges to generate a CSV of all serviceable areas, then auto-generate CMS content for each state page. (2) When new PIN code ranges are added (activating new delivery areas), trigger CMS page creation for newly serviceable regions. (3) Use the state_name field from panth_zipcode_range to maintain consistent geographic naming across all CMS content and structured data. (4) Build an internal API that cross-references PIN code ranges with product catalog to create "Products available in [State]" dynamic CMS pages. (5) The is_active flag allows seasonal service area changes that should be reflected in CMS content and sitemap inclusion/exclusion via AdvancedSEO.',
        'tags' => 'zipcode_validation, data_import, content_automation, regional_cms, sitemap, dynamic_content',
        'is_active' => 1,
        'sort_order' => 31,
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'zipcode_validation',
        'title' => 'ZipcodeValidation UX Messaging and Checkout Conversion for SEO',
        'content' => 'ZipcodeValidation provides real-time validation feedback with configurable colors (success: #007a33, error: #e02b27) and messages. UX and SEO connection: (1) The success message "Valid PIN code for {state}" confirms deliverability, reducing checkout abandonment which improves conversion rate - a signal that correlates with search ranking quality. (2) Validate on all touchpoints: checkout, account pages, and registration to ensure data quality. (3) For CMS FAQ pages, create entries like "Which PIN codes do you deliver to?" and "How do I check if delivery is available in my area?" using FAQPage schema. (4) The error message "Please enter a valid Indian PIN code (6 digits)" can be referenced in a help/troubleshooting CMS page. (5) Custom error/success colors should align with the site theme to maintain visual consistency tracked by Core Web Vitals CLS metric.',
        'tags' => 'zipcode_validation, ux, conversion, faq_schema, checkout, core_web_vitals, seo',
        'is_active' => 1,
        'sort_order' => 32,
    ],
];
