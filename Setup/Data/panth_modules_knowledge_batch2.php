<?php
/**
 * Panth Modules AI Knowledge Base - Batch 2
 *
 * Covers: QuickView, ProductTabs, DynamicForms, AdvancedContactUs,
 *         WhatsApp, LiveActivity, Footer, ThemeCustomizer
 *
 * Usage: Load this file and insert entries into panth_seo_ai_knowledge table.
 *
 * @return array<int, array{category: string, subcategory: string, title: string, content: string, tags: string, is_active: int, sort_order: int}>
 */

$entries = [];
$sort = 1000; // Offset to avoid collision with batch 1

// =====================================================================
// MODULE: Panth_QuickView
// Block: Panth\QuickView\Block\QuickView (modal), Panth\QuickView\Block\ProductViewTracker
// Helper: Panth\QuickView\Helper\Data
// Config section: panth_quickview
// =====================================================================

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'quickview',
    'title' => 'QuickView Module Overview',
    'content' => 'Panth_QuickView adds a quick view modal to product listing pages (category, search results). When enabled, a QuickView button appears on each product card. Clicking opens a modal overlay showing product image gallery, price, short description, SKU, stock status, and Add to Cart -- all without leaving the listing page. Configure at Stores > Configuration > Panth Extensions > Quick View. The module also includes a product view tracker that records recently viewed products via AJAX (Panth\QuickView\Block\ProductViewTracker). Compatible with both Luma and Hyva themes -- Hyva uses Panth_QuickView::quick-view-modal.phtml with Alpine.js, while Luma uses Panth_QuickView::luma/quick-view-modal.phtml.',
    'tags' => 'quickview, modal, product listing, quick view button, panth',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'quickview',
    'title' => 'QuickView Admin Configuration',
    'content' => 'QuickView configuration is at Stores > Configuration > Panth Extensions > Quick View (section id: panth_quickview). General Settings: Enable Quick View (panth_quickview/general/enabled). Display Settings: Show Product Image Gallery (panth_quickview/display/show_image_gallery), Show Short Description (panth_quickview/display/show_short_description), Show SKU (panth_quickview/display/show_sku), Show Stock Status (panth_quickview/display/show_stock_status), Show Add to Cart (panth_quickview/display/show_add_to_cart). All settings are store-view scoped.',
    'tags' => 'quickview, configuration, admin, display settings, panth',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'quickview',
    'title' => 'QuickView Layout XML Integration',
    'content' => 'QuickView modal is added to all pages via default.xml layout: block class="Panth\QuickView\Block\QuickView" placed in after.body.start container with template Panth_QuickView::luma/quick-view-modal.phtml. For Hyva themes, default_hyva.xml overrides the template to Panth_QuickView::quick-view-modal.phtml. The QuickView button on product cards is added via catalog_list_item.xml: block name="product.quickview.button" with template Panth_QuickView::product/list/buttons.phtml placed after the wishlist button. Product view tracking is added in catalog_product_view.xml via Panth\QuickView\Block\ProductViewTracker in before.body.end.',
    'tags' => 'quickview, layout xml, hyva, luma, template, integration',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'quickview',
    'title' => 'QuickView CMS Block Embedding',
    'content' => 'To add a QuickView button manually in a CMS block or page, use a layout XML reference. The QuickView modal is automatically loaded on all pages when enabled -- you do not need to embed it separately. The button is automatically injected into product listing grids. For custom product lists, ensure your template calls $block->getChildHtml("quickview") after including the product.quickview.button block. The QuickView AJAX endpoint is quickview/product/view with product ID parameter for loading product data into the modal.',
    'tags' => 'quickview, cms, embedding, product list, ajax endpoint',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'quickview',
    'title' => 'QuickView SEO Considerations',
    'content' => 'QuickView modals load product data via AJAX so modal content is not crawled by search engines -- this is beneficial as it avoids duplicate content issues. The modal does not create additional URLs or change the page URL (no hash fragments or query params). Product view tracking uses the endpoint quickview/track/view which records views in the panth_quickview_recently_viewed table. For SEO, ensure your canonical tags point to the actual product pages. The QuickView approach improves user engagement metrics (lower bounce rate, higher pages per session) which indirectly benefits SEO.',
    'tags' => 'quickview, seo, duplicate content, canonical, engagement',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'quickview',
    'title' => 'QuickView Helper Class Reference',
    'content' => 'Panth\QuickView\Helper\Data provides configuration access methods: isEnabled(?int $storeId = null): bool -- checks panth_quickview/general/enabled. showImageGallery(): bool -- checks panth_quickview/display/show_image_gallery. showShortDescription(): bool -- checks show_short_description. showSku(): bool -- checks show_sku. showStockStatus(): bool -- checks show_stock_status. showAddToCart(): bool -- checks show_add_to_cart. All methods accept an optional $storeId parameter for multi-store setups.',
    'tags' => 'quickview, helper, php, api, configuration methods',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =====================================================================
// MODULE: Panth_ProductTabs
// Widget: panth_product_tabs (Panth\ProductTabs\Block\Widget\Tabs)
// Block: Panth\ProductTabs\Block\Tabs
// Helper: Panth\ProductTabs\Helper\Data
// Config section: panth_producttabs
// =====================================================================

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'producttabs',
    'title' => 'ProductTabs Module Overview',
    'content' => 'Panth_ProductTabs customizes the product detail page tab section (description, reviews, additional info). Features include: horizontal or vertical tab styles, fade/slide/none animations, accordion mode on mobile, sticky tab navigation, lazy-loaded reviews tab, custom CMS block tabs, custom attribute tabs, configurable tab labels, visibility toggles, and sort order control. The block class Panth\ProductTabs\Block\Tabs extends Magento\Catalog\Block\Product\View\Details. It auto-detects Hyva vs Luma themes and selects the appropriate template. Configure at Stores > Configuration > Panth Extensions > Product Tabs.',
    'tags' => 'producttabs, product detail page, tabs, accordion, panth',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'producttabs',
    'title' => 'ProductTabs Widget Usage',
    'content' => 'ProductTabs has a widget with id "panth_product_tabs" and class Panth\ProductTabs\Block\Widget\Tabs. CMS widget code: {{widget type="Panth\ProductTabs\Block\Widget\Tabs" tab_style="horizontal" animation_type="fade"}}. Widget parameters: tab_style (horizontal or vertical), animation_type (fade, slide, or none). The widget extends the base Tabs block and implements Magento\Widget\Block\BlockInterface. Template: Panth_ProductTabs::tabs.phtml (Luma) or Panth_ProductTabs::hyva/tabs.phtml (Hyva, auto-detected). Use this widget to place product tabs in CMS pages or blocks outside the default product page layout.',
    'tags' => 'producttabs, widget, cms, embedding, tab_style, animation',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'producttabs',
    'title' => 'ProductTabs Admin Configuration - Design',
    'content' => 'ProductTabs design settings at panth_producttabs/design: Tab Style (panth_producttabs/design/tab_style) -- horizontal (top) or vertical (side) layout, source model Panth\ProductTabs\Model\Config\Source\TabStyle. Animation Type (panth_producttabs/design/animation_type) -- fade, slide, or none, source model Panth\ProductTabs\Model\Config\Source\AnimationType. Mobile Behavior (panth_producttabs/design/mobile_behavior) -- accordion or horizontal scroll tab strip, source model Panth\ProductTabs\Model\Config\Source\MobileBehavior. Open First Tab by Default (design/first_tab_open). Accordion on Mobile (design/accordion_on_mobile) -- recommended for better mobile UX.',
    'tags' => 'producttabs, design, tab style, animation, mobile, accordion',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'producttabs',
    'title' => 'ProductTabs Custom CMS Block Tabs',
    'content' => 'Add custom tabs that display CMS block content. Configure at Stores > Configuration > Panth Extensions > Product Tabs > Custom CMS Block Tabs (panth_producttabs/custom_cms_tabs/cms_tabs). Uses frontend_model Panth\ProductTabs\Block\Adminhtml\Form\Field\CustomCmsTabs with ArraySerialized backend. Each row defines a tab with a label, CMS block identifier, sort order, and enabled flag. Example: Add a "Shipping Info" tab that renders the "shipping-info" CMS block content. The serialized data is accessed via Panth\ProductTabs\Helper\Data::getCustomCmsTabs() which returns an array of tab configurations.',
    'tags' => 'producttabs, custom tabs, cms block, cms content, configuration',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'producttabs',
    'title' => 'ProductTabs Custom Attribute Tabs',
    'content' => 'Add custom tabs displaying product attribute values in a table format. Configure at panth_producttabs/custom_attribute_tabs/attr_tabs using frontend_model Panth\ProductTabs\Block\Adminhtml\Form\Field\CustomAttributeTabs with ArraySerialized backend. Each row defines: tab label, comma-separated attribute codes (e.g., material,color,weight), sort order, and enabled flag. Accessed via Panth\ProductTabs\Helper\Data::getCustomAttributeTabs(). This is useful for grouping product specifications into logical tabs like "Materials", "Dimensions", or "Care Instructions".',
    'tags' => 'producttabs, attribute tabs, product attributes, specifications, table',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'producttabs',
    'title' => 'ProductTabs Tab Labels and Visibility',
    'content' => 'Customize tab labels at panth_producttabs/labels: Description Tab Label (labels/description_label, default "Description"), More Information Tab Label (labels/more_info_label, default "More Information"), Reviews Tab Label (labels/reviews_label, default "Reviews"). Control visibility at panth_producttabs/visibility: Show Description Tab (visibility/show_description), Show More Information Tab (visibility/show_more_info), Show Reviews Tab (visibility/show_reviews). Set sort order at panth_producttabs/sort_order: description_order (default 10), more_info_order (default 20), reviews_order (default 30). Lower numbers appear first.',
    'tags' => 'producttabs, labels, visibility, sort order, tab names',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'producttabs',
    'title' => 'ProductTabs Performance Features',
    'content' => 'ProductTabs includes performance optimizations: Sticky Tab Navigation (panth_producttabs/general/sticky_tabs) -- makes tab nav stick to top on scroll for long content. Lazy Load Reviews (panth_producttabs/general/lazy_load_reviews) -- defers Reviews tab JavaScript initialization until the tab is actually clicked, reducing initial page load JS. Default Open Tab (panth_producttabs/general/default_tab) -- controls which tab opens on load using Panth\ProductTabs\Model\Config\Source\DefaultTab source model. These settings improve Core Web Vitals by reducing initial JavaScript execution and improving perceived load time.',
    'tags' => 'producttabs, performance, lazy load, sticky, core web vitals',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =====================================================================
// MODULE: Panth_DynamicForms
// Widget: panth_dynamic_form (Panth\DynamicForms\Block\Widget\DynamicForm)
// Config section: panth_dynamicforms
// =====================================================================

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'dynamicforms',
    'title' => 'DynamicForms Module Overview',
    'content' => 'Panth_DynamicForms is a full form builder for Magento. Create custom forms with a drag-and-drop field builder in the admin panel (Marketing > Panth > Dynamic Forms). Forms support field types: text, textarea, select, radio, checkbox, email, tel, file upload, and more. Each form has its own URL (via custom router Panth\DynamicForms\Controller\Router), submission management, email notifications (admin + auto-reply), and can be embedded anywhere via widget. Forms are stored in the panth_dynamicforms_form table with fields in panth_dynamicforms_field and submissions in panth_dynamicforms_submission. Configure at Stores > Configuration > Panth Extensions > Dynamic Forms (panth_dynamicforms).',
    'tags' => 'dynamicforms, form builder, custom forms, submissions, panth',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'dynamicforms',
    'title' => 'DynamicForms Widget Embedding',
    'content' => 'Embed any dynamic form using the widget with id "panth_dynamic_form" and class Panth\DynamicForms\Block\Widget\DynamicForm. CMS widget code: {{widget type="Panth\DynamicForms\Block\Widget\DynamicForm" form_id="1" show_title="1" show_description="1" template="Panth_DynamicForms::widget/form.phtml"}}. Parameters: form_id (required, select from dropdown via Panth\DynamicForms\Model\Config\Source\FormList), show_title (1/0), show_description (1/0), template (Panth_DynamicForms::widget/form.phtml for Luma or Panth_DynamicForms::widget/form_hyva.phtml for Hyva). Allowed containers: content, content.top, content.bottom, sidebar.main, sidebar.additional.',
    'tags' => 'dynamicforms, widget, cms, embedding, form_id, template',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'dynamicforms',
    'title' => 'DynamicForms PageBuilder Integration',
    'content' => 'To embed a Dynamic Form in PageBuilder: 1) Use the Widget content type in PageBuilder and select "Dynamic Form" from the widget type dropdown. 2) Choose the form from the form_id dropdown. 3) Configure show_title and show_description. Alternatively, insert the widget code directly in the HTML content type: {{widget type="Panth\DynamicForms\Block\Widget\DynamicForm" form_id="YOUR_FORM_ID" show_title="1" show_description="1"}}. The block auto-detects Hyva vs Luma themes via Panth\Core\Helper\Theme::isHyva() and selects the appropriate template automatically if no template parameter is specified.',
    'tags' => 'dynamicforms, pagebuilder, widget, integration, hyva detection',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'dynamicforms',
    'title' => 'DynamicForms reCAPTCHA and File Uploads',
    'content' => 'DynamicForms supports Google reCAPTCHA v3 for spam protection. Enable at panth_dynamicforms/general/recaptcha_enabled, then set recaptcha_site_key and recaptcha_secret_key. File uploads are configured globally: allowed_file_extensions (comma-separated, e.g., "jpg,png,pdf,doc"), max_file_size (in MB), upload_dir (relative to media directory). Upload endpoint: dynamicforms/form/upload. Submission endpoint: dynamicforms/form/submit. The form supports AJAX submission (panth_dynamicforms/display/ajax_submit) for no-reload form posting with configurable loading button text.',
    'tags' => 'dynamicforms, recaptcha, file upload, spam protection, ajax',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'dynamicforms',
    'title' => 'DynamicForms Styling Configuration',
    'content' => 'DynamicForms styling can use theme colors or custom values. At panth_dynamicforms/styling: use_theme_config (Yes/No) -- when enabled, colors from theme-config.json are used. Custom options (when use_theme_config is off): primary_color, error_color, success_color, border_radius (CSS value like 8px), and custom_css (textarea for additional CSS). Display settings at panth_dynamicforms/display: form_layout (source model Panth\DynamicForms\Model\Config\Source\FormLayout), show_form_title, show_form_description, ajax_submit, loading_text. Field widths support full, half, and third via CSS classes panth-df-field--full, panth-df-field--half, panth-df-field--third.',
    'tags' => 'dynamicforms, styling, colors, layout, css, field width',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'dynamicforms',
    'title' => 'DynamicForms Email Notifications',
    'content' => 'DynamicForms sends two types of emails on form submission: 1) Admin Notification -- configured at panth_dynamicforms/email/admin_email_template and admin_email_sender. 2) Auto-Reply to submitter -- configured at panth_dynamicforms/email/autoreply_email_template and autoreply_email_sender. Email templates are registered in etc/email_templates.xml. Each form can also have a custom success_message and redirect_url. The Block\Widget\DynamicForm::getFormConfig() method returns JSON config including submit_url, upload_url, ajax_enabled, success_message, redirect_url, submit_button_text, allowed_extensions, and max_file_size_mb.',
    'tags' => 'dynamicforms, email, notifications, auto-reply, admin notification',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'dynamicforms',
    'title' => 'DynamicForms Direct URL Access',
    'content' => 'Each dynamic form gets its own URL via the custom router Panth\DynamicForms\Controller\Router. Forms can be accessed at the URL path configured in the form admin (e.g., /contact-sales or /feedback). The View controller at Panth\DynamicForms\Controller\Form\View loads the form, sets it in the registry as "current_dynamic_form", and renders the page. The widget block checks the registry first before falling back to the form_id widget parameter. This means forms work both as standalone pages with SEO-friendly URLs and as embeddable widgets on any CMS page or block.',
    'tags' => 'dynamicforms, url, router, standalone page, seo url',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =====================================================================
// MODULE: Panth_AdvancedContactUs
// ViewModel: Panth\AdvancedContactUs\ViewModel\ContactForm
// Config section: panth_advancedcontact
// =====================================================================

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'advancedcontactus',
    'title' => 'AdvancedContactUs Module Overview',
    'content' => 'Panth_AdvancedContactUs replaces the default Magento contact form with an enhanced version. Features: custom page title, AJAX form submission with Alpine.js, contact info sidebar (email, phone, address, business hours), configurable fields (phone, subject, custom fields), honeypot spam protection, rate limiting per IP, minimum form fill time bot detection, admin submission management with UI grid, and customer confirmation emails. The form uses ViewModel pattern via Panth\AdvancedContactUs\ViewModel\ContactForm. Template at Panth_AdvancedContactUs::form.phtml uses CSS variables for theming (--contact-primary, --contact-bg, etc.). Configure at Stores > Configuration > Panth Extensions > Advanced Contact Us (panth_advancedcontact).',
    'tags' => 'contact form, advanced contact, ajax, spam protection, panth',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'advancedcontactus',
    'title' => 'AdvancedContactUs Custom Fields',
    'content' => 'Add unlimited custom fields to the contact form at panth_advancedcontact/fields/custom_fields. Uses frontend_model Panth\AdvancedContactUs\Block\Adminhtml\Form\Field\CustomFields with ArraySerialized backend. Each field row defines: label, type (text, textarea, select, radio, checkbox, email, tel), required (1 or 0), placeholder text, and options (comma-separated values for select/radio types). Custom field values are submitted with key format "custom_" + lowercase sanitized label. Example: a field labeled "Department" with type "select" and options "Sales,Support,Billing" creates a dropdown. Built-in fields (name, email, message) are always present; phone and subject can be toggled via show_phone and show_subject settings.',
    'tags' => 'contact form, custom fields, dynamic fields, select, radio, checkbox',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'advancedcontactus',
    'title' => 'AdvancedContactUs Bot Protection',
    'content' => 'AdvancedContactUs includes three layers of bot protection configured at panth_advancedcontact/protection: 1) Honeypot field (protection/honeypot) -- a hidden "website_url" field invisible to users but filled by bots, causing rejection. 2) Rate Limiting (protection/rate_limit) -- limits submissions per IP per hour, configurable via max_per_hour. 3) Minimum Fill Time (protection/min_time) -- rejects submissions completed faster than N seconds (default 2), since bots fill forms instantly. The form includes a hidden _timestamp field set at page load to measure fill duration. These protections work without requiring reCAPTCHA, providing a frictionless user experience.',
    'tags' => 'contact form, bot protection, honeypot, rate limiting, spam, security',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'advancedcontactus',
    'title' => 'AdvancedContactUs Contact Info Sidebar',
    'content' => 'The contact form displays an optional sticky sidebar with business contact information. Enable at panth_advancedcontact/general/show_info. Configure details at panth_advancedcontact/contact_info: email (validates as email), phone, address (textarea), hours (business hours text). The sidebar uses a sticky card layout (CSS class panth-ci-card with position:sticky; top:100px) that stays visible while scrolling the form on desktop. On mobile (below 768px), it becomes a standard stacked card below the form. Each info item displays with an SVG icon (email, phone, map pin, clock), a label, and a clickable value (mailto: for email, tel: for phone).',
    'tags' => 'contact form, sidebar, contact info, sticky, business hours',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'advancedcontactus',
    'title' => 'AdvancedContactUs AJAX Submission',
    'content' => 'The contact form uses Alpine.js for AJAX submission. The form POSTs to the action URL (from $viewModel->getFormAction()) with FormData containing all fields plus ajax=1 header and X-Requested-With: XMLHttpRequest. Response is JSON with success boolean and optional message. On success, the form card transitions to a success state showing a checkmark icon and the configured success message (panth_advancedcontact/general/success_message). Client-side validation runs on blur and input events for name, email, and message fields. Email validation uses regex pattern. The form pre-fills name and email for logged-in customers via $viewModel->getUserName() and $viewModel->getUserEmail().',
    'tags' => 'contact form, ajax, alpine.js, validation, form submission',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'advancedcontactus',
    'title' => 'AdvancedContactUs Admin Submissions',
    'content' => 'All contact form submissions are saved to the database and viewable in the admin at Marketing > Panth > Contact Submissions. The UI grid (panth_contact_submission_listing) shows submission data with actions for viewing and deleting. Admin routes: panthcontact/submission/index (list), panthcontact/submission/view (detail), panthcontact/submission/delete, panthcontact/submission/massDelete. Email settings at panth_advancedcontact/email: recipient_email (admin notification email), sender_email_identity (from email), send_confirmation (send customer confirmation email). The Model\Submission stores each entry and Model\Mail handles email dispatch.',
    'tags' => 'contact form, admin, submissions, grid, email notification',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =====================================================================
// MODULE: Panth_WhatsApp
// Helper: Panth\WhatsApp\Helper\Data
// ViewModels: Panth\WhatsApp\ViewModel\FloatButton, Product, Category
// Blocks: Panth\WhatsApp\Block\Product\Button, Category\Banner
// Config section: panth_whatsapp
// =====================================================================

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'whatsapp',
    'title' => 'WhatsApp Module Overview',
    'content' => 'Panth_WhatsApp adds WhatsApp chat integration across three contexts: 1) Floating button on all pages -- a persistent WhatsApp icon linking to wa.me with pre-filled message. 2) Product page button -- "Ask on WhatsApp" with product name and URL auto-filled in the message. 3) Category page banner -- WhatsApp assistance banner on category/listing pages. Configure at Stores > Configuration > Panth Extensions > WhatsApp Integration (panth_whatsapp). The module uses wa.me deep links (https://wa.me/PHONE?text=MESSAGE) which work on both mobile and desktop. Colors are managed via CSS variables from theme-config.json.',
    'tags' => 'whatsapp, chat, floating button, product page, category page, panth',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'whatsapp',
    'title' => 'WhatsApp Float Button Configuration',
    'content' => 'Floating WhatsApp button settings at panth_whatsapp/general: enabled (Yes/No), phone_number (with country code, e.g., +1234567890), message (pre-filled default message, default: "Hi! I have a question about your products."), button_text (hover text, default: "Chat with Us"), position (bottom-left or bottom-right via Panth\WhatsApp\Model\Config\Source\Position). The float button is rendered via Panth\WhatsApp\ViewModel\FloatButton and template Panth_WhatsApp::whatsapp-float.phtml added to all pages via default.xml layout. The helper method isWhatsAppEnabled() checks both the module setting and the Core module dependency.',
    'tags' => 'whatsapp, float button, configuration, phone number, position',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'whatsapp',
    'title' => 'WhatsApp Product Page Integration',
    'content' => 'Product page WhatsApp button settings at panth_whatsapp/product: enabled, button_text (default: "Ask on WhatsApp"), message_template (supports {product_name} and {product_url} placeholders, default: "Hi! I\'m interested in {product_name}. {product_url}"), button_style (via Panth\WhatsApp\Model\Config\Source\ButtonStyle). The button is rendered by Panth\WhatsApp\Block\Product\Button with ViewModel Panth\WhatsApp\ViewModel\Product on catalog_product_view.xml layout. Template: Panth_WhatsApp::product/button.phtml. The message template dynamically replaces {product_name} with the current product name and {product_url} with its full URL.',
    'tags' => 'whatsapp, product page, button, message template, placeholders',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'whatsapp',
    'title' => 'WhatsApp Category Page Integration',
    'content' => 'Category page WhatsApp banner settings at panth_whatsapp/category: enabled, button_text (default: "Chat with Us"), message_template (default: "Hi! I need help finding products in your store."). Rendered by Panth\WhatsApp\Block\Category\Banner with ViewModel Panth\WhatsApp\ViewModel\Category via catalog_category_view.xml layout. Templates: Panth_WhatsApp::category/banner.phtml and Panth_WhatsApp::category/contact.phtml. This creates a CTA banner encouraging customers to chat for product recommendations -- especially effective for stores with large catalogs or complex products requiring consultation.',
    'tags' => 'whatsapp, category page, banner, customer support, inquiry',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'whatsapp',
    'title' => 'WhatsApp Advanced Styling',
    'content' => 'WhatsApp button styling uses CSS variables from theme-config.json for colors (button background, text color, etc.). Additional custom CSS classes can be added at panth_whatsapp/advanced/custom_css_classes (one class per line, converted to space-separated string). The Helper\Data::getCustomCssClasses() method cleans up line breaks and whitespace. Button styles are controlled by panth_whatsapp/product/button_style source model Panth\WhatsApp\Model\Config\Source\ButtonStyle. The deprecated getWhatsAppProductButtonBgColor() and getWhatsAppProductButtonTextColor() methods return empty strings -- colors are now managed via CSS variables exclusively.',
    'tags' => 'whatsapp, styling, css variables, theme-config, custom classes',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =====================================================================
// MODULE: Panth_LiveActivity
// Block: Panth\LiveActivity\Block\Activity
// Helper: Panth\LiveActivity\Helper\Config
// Config section: live_activity
// =====================================================================

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'liveactivity',
    'title' => 'LiveActivity Module Overview',
    'content' => 'Panth_LiveActivity adds social proof notifications showing real-time (or simulated) customer activity like purchases, cart adds, wishlist adds, live viewers, trending alerts, and low stock warnings. Notifications appear as toast popups that cycle through activity data. The module tracks real customer events via observers: TrackOrderPlacement (checkout_onepage_controller_success_action), TrackCartAdd (checkout_cart_product_add_after), TrackWishlistAdd (wishlist_add_product). Data is stored in the panth_liveactivity table. For new stores, simulated/fake data mode generates realistic activity with configurable fake names and locations. Configure at Stores > Configuration > Panth Extensions > Live Activity & Social Proof (live_activity).',
    'tags' => 'live activity, social proof, notifications, fomo, urgency, panth',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'liveactivity',
    'title' => 'LiveActivity Notification Settings',
    'content' => 'General settings at live_activity/general: enabled, position (via Panth\LiveActivity\Model\Config\Source\Position), display_delay (0-60 seconds before first notification), notification_duration (3-30 seconds each notification is visible), interval (5-120 seconds between notifications), max_notifications (per page view, 0 = unlimited). Activity types at live_activity/activity_types: show_purchases ("John from New York purchased this 5 minutes ago"), show_cart_adds ("Sarah added this to cart 2 minutes ago"), show_wishlist_adds ("This product was added to 23 wishlists today"), show_live_viewers ("15 people viewing this right now"), show_trending ("Trending: 50 views in last hour"), show_low_stock ("Stock alert: Only 5 left!").',
    'tags' => 'live activity, notification settings, timing, activity types, position',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'liveactivity',
    'title' => 'LiveActivity Data Source Configuration',
    'content' => 'Data source settings at live_activity/data_source: use_real_data (track actual customer activity), use_simulated_data (generate fake activity for low-traffic stores), time_range (show activity from last X hours/days via Panth\LiveActivity\Model\Config\Source\TimeRange), anonymize_names (show "John D." instead of full names for privacy), featured_products (specific product IDs to show, uses Panth\LiveActivity\Block\Adminhtml\System\Config\ProductPicker with AJAX product search). Simulated data settings: fake_names (managed via Panth\LiveActivity\Block\Adminhtml\System\Config\FakeNames, JSON format with default names like "James D.", "Sarah J."), fake_locations (via FakeLocations block, default cities worldwide). Both can run simultaneously.',
    'tags' => 'live activity, data source, simulated, fake data, real tracking, product picker',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'liveactivity',
    'title' => 'LiveActivity Frontend Integration',
    'content' => 'LiveActivity notifications are loaded via Panth\LiveActivity\Block\Activity in default.xml layout with template Panth_LiveActivity::notifications.phtml. The block provides: isEnabled(), getConfigJson() (JSON-serialized frontend config with timing/position/appearance settings), getAjaxUrl() (liveactivity/ajax/getactivity endpoint), getCurrentProductId() (detects product page context), getCustomCss(). Frontend JavaScript polls the AJAX endpoint at the configured interval. Appearance settings at live_activity/appearance: animation_style (via Panth\LiveActivity\Model\Config\Source\Animation), show_product_image (small thumbnail), show_icon (activity type icon). Advanced: exclude_categories, mobile_enabled toggle.',
    'tags' => 'live activity, frontend, ajax, block, notifications template, animation',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'liveactivity',
    'title' => 'LiveActivity SEO and Performance Tips',
    'content' => 'LiveActivity notifications load asynchronously via AJAX after page load, so they do not affect initial page render or Core Web Vitals LCP/FID metrics. The configurable display_delay (0-60s) ensures the first notification does not appear until after the page is interactive. CSS classes for styling: .live-activity-notification (main container), .live-activity-icon, .live-activity-message. Custom CSS can be added at live_activity/appearance/custom_css. For SEO: notification content is not in the initial HTML (loaded via AJAX) so it does not create keyword stuffing concerns. Exclude categories (live_activity/advanced/exclude_categories) to avoid showing notifications on irrelevant pages. Disable on mobile (advanced/mobile_enabled) if notifications are distracting on small screens.',
    'tags' => 'live activity, seo, performance, core web vitals, css classes',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =====================================================================
// MODULE: Panth_Footer
// Helper: Panth\Footer\Helper\Data
// ViewModel: Panth\Footer\ViewModel\FooterData
// Config section: panth_footer
// =====================================================================

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'footer',
    'title' => 'Footer Module Overview',
    'content' => 'Panth_Footer provides a fully customizable footer with up to 4 columns, newsletter section, social media links, back-to-top button, payment icons, and copyright bar. Supports both Hyva and Luma themes with separate templates. Column 1: Logo + About text + social icons. Columns 2-3: Quick links in JSON format. Column 4: Contact information (phone, email, address, working hours). Configure at Stores > Configuration > Panth Extensions > Footer Configuration (panth_footer). Colors and visual styling are managed via Theme Customizer (theme-config.json). The footer uses responsive grid layout with Tailwind CSS classes: grid-cols-1 md:grid-cols-2 lg:grid-cols-4.',
    'tags' => 'footer, customization, columns, layout, newsletter, panth',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'footer',
    'title' => 'Footer Column Configuration',
    'content' => 'Each footer column is independently configurable. Column 1 (Logo/About) at panth_footer/column1: enabled, show_logo (display store logo), title, content (HTML allowed about text), show_social (show social media icons). Columns 2-3 (Quick Links) at panth_footer/column2 and column3: enabled, title, links (JSON format: [{"title":"About Us","url":"/about","target":"_self"}] -- use "_blank" and full URL for external links). Column 4 (Contact) at panth_footer/column4: enabled, title, show_contact_info, phone, email, address (textarea), working_hours. Layout at panth_footer/general/layout controls column count (2, 3, or 4) via Panth\Footer\Model\Config\Source\Layout. The Helper\Data::getColumnData(int $columnNumber) returns all data for any column.',
    'tags' => 'footer, columns, links, json format, contact info, layout',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'footer',
    'title' => 'Footer Links JSON Format',
    'content' => 'Footer links for columns 2, 3, and bottom bar use JSON format stored as textarea values with the Panth\Footer\Block\Adminhtml\Form\Field\JsonBeautifier frontend model for pretty display. Format: [{"title":"Page Title","url":"/relative-url","target":"_self"}]. For external links, use full URL with target "_blank": [{"title":"Facebook","url":"https://facebook.com/store","target":"_blank"}]. The Helper\Data::parseLinks() method safely decodes JSON with error logging. Example Quick Links config: [{"title":"About Us","url":"/about-us","target":"_self"},{"title":"Blog","url":"/blog","target":"_self"},{"title":"Careers","url":"https://careers.example.com","target":"_blank"}].',
    'tags' => 'footer, links, json, format, external links, target blank',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'footer',
    'title' => 'Footer Newsletter Section',
    'content' => 'Newsletter section at panth_footer/newsletter: enabled, title (section heading), subtitle (description text), benefits (JSON array, e.g., ["Weekly updates","Exclusive deals","No spam, ever"]), placeholder_text (email input placeholder, default: "Enter your email address"), button_text (subscribe button, default: "Subscribe"). Benefits use Panth\Footer\Block\Adminhtml\Form\Field\JsonBeautifier for admin display. The newsletter uses Magento native subscription backend. Templates: Panth_Footer::newsletter.phtml (Luma), Panth_Footer::hyva/newsletter.phtml (Hyva). Newsletter colors (bg, title, text, button, input) are managed via theme-config.json through the Theme Customizer module.',
    'tags' => 'footer, newsletter, subscription, email, benefits, styling',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'footer',
    'title' => 'Footer Social Media and Bottom Bar',
    'content' => 'Social media links at panth_footer/social: facebook, twitter (X), instagram, linkedin, youtube, pinterest -- each is a URL field. The Helper\Data::getSocialLinks() returns only non-empty platforms as key-value array. SVG icons are provided by getSocialIcon(string $platform). Show social icons in Column 1 by enabling column1/show_social. Bottom bar at panth_footer/bottom: show_payment_icons (Visa, Mastercard, etc.), copyright_text (supports {{year}} placeholder for current year and HTML), show_footer_links (Privacy Policy, Terms links), footer_links (JSON format same as column links). Back to top button at panth_footer/back_to_top: enabled, position (bottom-left or bottom-right via Panth\Footer\Model\Config\Source\Position). Colors from theme-config.json.',
    'tags' => 'footer, social media, copyright, payment icons, back to top',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'footer',
    'title' => 'Footer SEO Best Practices',
    'content' => 'Footer SEO tips with Panth_Footer: 1) Include important internal links in columns 2-3 for crawl depth distribution -- use descriptive anchor text in link titles. 2) Use the copyright field with {{year}} to auto-update: "(c) {{year}} Your Store. All rights reserved." 3) Add structured contact data in Column 4 (phone, email, address) which supports LocalBusiness schema. 4) Social media links use noopener noreferrer target="_blank" for security. 5) The newsletter section adds an email subscription CTA above the fold on every page. 6) Footer links in bottom bar should include Privacy Policy and Terms pages for E-E-A-T compliance. 7) Keep footer content consistent across all pages (site-wide via default.xml layout).',
    'tags' => 'footer, seo, internal links, structured data, e-e-a-t, copyright',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =====================================================================
// MODULE: Panth_ThemeCustomizer
// Helper: Panth\ThemeCustomizer\Helper\Data
// Blocks: Panth\ThemeCustomizer\Block\GoogleFonts, CustomCss
// Config section: theme_customizer, panth_header
// =====================================================================

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'themecustomizer',
    'title' => 'ThemeCustomizer Module Overview',
    'content' => 'Panth_ThemeCustomizer manages visual theme settings through two systems: 1) theme-config.json (at app/design/frontend/Panth/Infotech/web/tailwind/theme-config.json) for colors, typography, spacing -- processed by Node.js build script. 2) Admin config for runtime settings: Google Fonts loading, custom CSS injection, header configuration. Configure at Stores > Configuration > Panth Extensions > Theme Customizer (theme_customizer) and Header Configuration (panth_header). After editing theme-config.json: run "node generate-theme-css.js" in the tailwind directory, then "npm run build" to rebuild Tailwind CSS, then flush Magento cache.',
    'tags' => 'theme customizer, theme-config.json, tailwind, styling, panth',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'themecustomizer',
    'title' => 'ThemeCustomizer Google Fonts',
    'content' => 'Google Fonts configuration at theme_customizer/typography: font_family_base (body font for all text, paragraphs, buttons, inputs) and font_family_heading (font for h1-h6 headings). Source model: Panth\ThemeCustomizer\Model\Config\Source\GoogleFonts. Selecting "System Fonts" loads nothing from CDN (fastest option). Fonts are loaded via Panth\ThemeCustomizer\Block\GoogleFonts block with template Panth_ThemeCustomizer::google-fonts.phtml which outputs a <link> tag in the HTML head. Important: After selecting a Google Font in admin, you must also update typography.font-family-base or typography.font-family-heading in theme-config.json to match, then rebuild Tailwind CSS.',
    'tags' => 'theme customizer, google fonts, typography, font loading, performance',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'themecustomizer',
    'title' => 'ThemeCustomizer Custom CSS',
    'content' => 'Inject custom CSS on every page via theme_customizer/custom_css/custom_tailwind_css. This renders as an inline <style> tag after the main stylesheet via Panth\ThemeCustomizer\Block\CustomCss with template Panth_ThemeCustomizer::custom-css.phtml. Uses frontend_model Panth\ThemeCustomizer\Block\Adminhtml\System\Config\TailwindCss and backend_model Panth\ThemeCustomizer\Model\Config\Backend\TailwindCss. Write standard CSS (not Tailwind utility classes). Example: ".my-class { color: red; }". Access via Panth\ThemeCustomizer\Helper\Data::getCustomTailwindCss(). This is ideal for quick overrides without modifying theme source files or triggering a Tailwind rebuild.',
    'tags' => 'theme customizer, custom css, inline style, overrides, styling',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'themecustomizer',
    'title' => 'ThemeCustomizer Header Configuration',
    'content' => 'Header settings at panth_header section: General (panth_header/general) -- enabled (use Panth custom header), sticky_enabled (fixed on scroll), show_on_scroll (hide on scroll down, show on scroll up). Top Bar (panth_header/topbar) -- enabled, left_text and right_text (HTML supported, e.g., "Free Shipping on Orders Over $99"). Icons (panth_header/icons) -- search_enabled, account_enabled, minicart_enabled, counter_style (badge shape via Panth\ThemeCustomizer\Model\Config\Source\CounterStyle), icon_size (px, default 24). Mini Cart (panth_header/minicart) -- free_shipping_enabled with threshold amount, progress message ({amount} placeholder), success message, show_continue_shopping, show_subtotal. Layout -- container_width (via ContainerWidth source), height (px, default 80).',
    'tags' => 'theme customizer, header, sticky header, top bar, icons, mini cart',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'themecustomizer',
    'title' => 'ThemeCustomizer theme-config.json Build Process',
    'content' => 'The theme-config.json file at app/design/frontend/Panth/Infotech/web/tailwind/theme-config.json is the single source of truth for visual styling (colors, typography values, spacing, sizing). Build process: 1) Edit theme-config.json with desired values. 2) Run "node generate-theme-css.js" in the tailwind directory to generate CSS custom properties. 3) Run "npm run build" to rebuild Tailwind CSS with the new values. 4) Flush Magento cache: "php bin/magento cache:flush". The ThemeCustomizer module includes Panth\ThemeCustomizer\Observer\AutoBuildOnConfigSave and Panth\ThemeCustomizer\Observer\ConfigSaveAfter for handling config save events. Export CSS via Controller\Adminhtml\Build\ExportCss. Build via AJAX at Controller\Adminhtml\Build\Ajax.',
    'tags' => 'theme customizer, theme-config.json, build process, tailwind, css variables',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'themecustomizer',
    'title' => 'ThemeCustomizer Free Shipping Progress Bar',
    'content' => 'The mini cart includes an optional free shipping progress bar. Enable at panth_header/minicart/free_shipping_enabled. Set threshold at free_shipping_threshold (minimum order amount, default 99). Configure messages: free_shipping_message (use {amount} placeholder, e.g., "Add {amount} more for free shipping!") and free_shipping_success_message (shown when threshold reached, e.g., "You qualify for free shipping!"). Additional mini cart options: show_continue_shopping button and show_subtotal. This feature encourages higher order values and reduces cart abandonment by showing customers how close they are to free shipping.',
    'tags' => 'theme customizer, free shipping, progress bar, mini cart, threshold',
    'is_active' => 1,
    'sort_order' => $sort++,
];

// =====================================================================
// CROSS-MODULE: Integration Tips
// =====================================================================

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'integration',
    'title' => 'Panth Modules Hyva vs Luma Theme Detection',
    'content' => 'All Panth modules use Panth\Core\Helper\Theme::isHyva() to detect the active theme. Hyva-compatible templates are placed in subdirectories: Panth_ModuleName::hyva/template.phtml. Luma templates use the standard path or luma/ subdirectory. Template switching happens automatically -- in Block classes via getTemplate() method override (e.g., Panth\ProductTabs\Block\Tabs checks themeHelper->isHyva()), or in layout XML via default_hyva.xml overrides (e.g., QuickView default_hyva.xml changes the modal template). Hyva templates use Alpine.js for interactivity instead of RequireJS/KnockoutJS. The DynamicForms widget auto-detects in _toHtml() and selects form.phtml or form_hyva.phtml.',
    'tags' => 'panth, hyva, luma, theme detection, alpine.js, template switching',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'integration',
    'title' => 'Panth Modules Color Management via theme-config.json',
    'content' => 'Visual colors for Panth modules are centralized in theme-config.json rather than individual module admin settings. This ensures consistent branding. The ThemeCustomizer module generates CSS custom properties from theme-config.json that all modules reference. Examples: Footer colors (footer/bg_color, footer/text_color, footer/h2_color), newsletter colors (newsletter/bg_color, newsletter/button_color), back-to-top button colors, WhatsApp button colors, LiveActivity notification colors, contact form colors (--contact-primary, --contact-bg). To change a module color: edit theme-config.json, run the build process, flush cache. Do NOT look for color fields in individual module admin configs -- they have been migrated to theme-config.json.',
    'tags' => 'panth, theme-config.json, colors, css variables, centralized styling',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'integration',
    'title' => 'Embedding Multiple Panth Widgets on One Page',
    'content' => 'Multiple Panth widgets can coexist on a single CMS page or block. Example CMS content combining modules: {{widget type="Panth\DynamicForms\Block\Widget\DynamicForm" form_id="1" show_title="1"}} for a contact form, combined with {{widget type="Panth\ProductTabs\Block\Widget\Tabs" tab_style="horizontal" animation_type="fade"}} for product tabs. The WhatsApp float button and LiveActivity notifications are global (added via default.xml) and appear automatically. The Footer module replaces the entire footer section. QuickView buttons inject automatically into product listing grids. For PageBuilder pages: use the Widget content type to insert DynamicForms widgets, and standard layout handles for the global modules.',
    'tags' => 'panth, widgets, multiple, cms page, pagebuilder, embedding',
    'is_active' => 1,
    'sort_order' => $sort++,
];

$entries[] = [
    'category' => 'panth_module',
    'subcategory' => 'integration',
    'title' => 'Panth Module Admin Menu Structure',
    'content' => 'All Panth modules are accessible under the Panth Extensions tab in Stores > Configuration with the tab id "panth". Admin menu items for data management: Marketing > Panth > Dynamic Forms (form builder), Marketing > Panth > Contact Submissions (advanced contact form entries), Quick View > View Tracker (product view analytics). Config section IDs for direct access: panth_quickview (Quick View), panth_producttabs (Product Tabs), panth_dynamicforms (Dynamic Forms), panth_advancedcontact (Advanced Contact Us), panth_whatsapp (WhatsApp Integration), live_activity (Live Activity), panth_footer (Footer Configuration), theme_customizer (Theme Customizer), panth_header (Header Configuration). ACL resources follow pattern: Panth_ModuleName::config.',
    'tags' => 'panth, admin menu, configuration, section ids, acl',
    'is_active' => 1,
    'sort_order' => $sort++,
];

return $entries;
