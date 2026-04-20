<?php
/**
 * Panth Module Knowledge Base - Batch 1
 *
 * AI training data for BannerSlider, MegaMenu, ProductSlider, Testimonials,
 * Faq, ProductAttachments, SmartBadge, and ProductGallery modules.
 *
 * @category  Panth
 * @package   Panth_AdvancedSEO
 */
return [
    // =========================================================================
    // BANNER SLIDER (8 entries)
    // =========================================================================
    [
        'category' => 'panth_modules',
        'subcategory' => 'banner_slider',
        'title' => 'Panth BannerSlider - Widget Embedding',
        'content' => 'To embed a banner slider in CMS content, use the widget code: {{widget type="Panth\\BannerSlider\\Block\\Widget\\BannerSlider" identifier="homepage_banner"}}. The "identifier" parameter is required and must match a slider created in the admin under Panth > Banner Slider > Manage Sliders. Common identifiers include "homepage_banner", "category_banner", and "product_banner". The template is auto-detected based on theme (Luma or Hyva).',
        'tags' => 'banner,slider,widget,cms,embedding',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'banner_slider',
        'title' => 'Panth BannerSlider - Template Options',
        'content' => 'BannerSlider supports two templates: the default Luma template "Panth_BannerSlider::widget/banner_slider.phtml" and the Hyva template "Panth_BannerSlider::widget/banner_slider_hyva.phtml". Template auto-detection is built in via the Panth\\Core\\Helper\\Theme helper -- if the active theme is Hyva, the Hyva template is used automatically. You can also force a template: {{widget type="Panth\\BannerSlider\\Block\\Widget\\BannerSlider" identifier="homepage_banner" template="Panth_BannerSlider::widget/banner_slider_hyva.phtml"}}.',
        'tags' => 'banner,slider,template,hyva,luma,theme',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'banner_slider',
        'title' => 'Panth BannerSlider - Slider Configuration',
        'content' => 'Each banner slider entity in the database (panth_banner_slider table) supports these configuration options: autoplay (on/off), autoplay_speed (milliseconds, default 5000), transition_speed (default 600ms), effect ("fade" or "slide"), is_loop (infinite loop), show_arrows, show_dots, and pause_on_hover. These are configured per slider in the admin panel and output as a JSON config object in the frontend.',
        'tags' => 'banner,slider,configuration,autoplay,transition,animation',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'banner_slider',
        'title' => 'Panth BannerSlider - Slide Properties',
        'content' => 'Each slide (panth_banner_slide table) supports: title, desktop_image, tablet_image, mobile_image (responsive images), content_html (HTML overlay content), link_url, link_target (_self or _blank), alt_text for SEO, sort_order, and date scheduling (date_from / date_to). Slides are linked to a parent slider via slider_id. The template uses the <picture> element with <source> tags for responsive image delivery.',
        'tags' => 'banner,slide,responsive,images,content,overlay',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'banner_slider',
        'title' => 'Panth BannerSlider - SEO Best Practices',
        'content' => 'For SEO optimization of banner sliders: 1) Always set descriptive alt_text on each slide -- the template falls back to "Banner N" if empty. 2) Use meaningful link_url values for slides that link to product or category pages. 3) The Hyva template uses proper ARIA attributes: role="region", aria-roledescription="carousel", aria-label on slides ("Slide N of M"), and role="tablist" on pagination dots. 4) First slide image uses loading="eager" and fetchpriority="high", subsequent slides use loading="lazy".',
        'tags' => 'banner,slider,seo,alt-text,aria,accessibility,lazy-loading',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'banner_slider',
        'title' => 'Panth BannerSlider - Hyva Alpine.js Component',
        'content' => 'The Hyva banner slider uses an Alpine.js component "panthBannerSlider(total, config)". It supports: next()/prev() navigation, goTo(index), touch swipe detection (50px threshold), keyboard navigation (left/right arrows), pause on hover, and autoplay with configurable interval. The component uses x-show with x-transition for fade or slide effects. CSS variables control visual aspects: --banner-height-desktop (500px), --banner-height-tablet (400px), --banner-height-mobile (320px), --banner-border-radius, --banner-overlay-bg, --banner-content-max-width (800px), --banner-transition-speed (600ms).',
        'tags' => 'banner,slider,hyva,alpine,javascript,css-variables',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'banner_slider',
        'title' => 'Panth BannerSlider - Responsive Design',
        'content' => 'The BannerSlider is fully responsive with three breakpoints. Desktop (1024px+): full-height slider with hover-reveal arrows, absolute-positioned dot navigation inside the slider. Tablet (768-1023px): reduced height, arrows always visible. Mobile (below 768px): compact height, inline arrows and dots are hidden inside the slider and instead shown in a controls bar below the slider (dots on left, prev/next arrows on right), matching the Hyva product carousel pattern.',
        'tags' => 'banner,slider,responsive,mobile,tablet,breakpoints',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'banner_slider',
        'title' => 'Panth BannerSlider - Content Overlay',
        'content' => 'Each slide can have a content_html overlay rendered on top of the slide image. The overlay uses a semi-transparent background (var(--banner-overlay-bg, rgba(0,0,0,0.25))) for text readability. Content is centered within a max-width container (var(--banner-content-max-width, 800px)). The content_html field supports full CMS/HTML including headings, paragraphs, and CTA buttons. If content_html is empty and link_url is set, the entire slide becomes a clickable link. If content_html is present, the link is not wrapping the whole slide.',
        'tags' => 'banner,slider,content,overlay,html,cta',
    ],

    // =========================================================================
    // MEGA MENU (8 entries)
    // =========================================================================
    [
        'category' => 'panth_modules',
        'subcategory' => 'mega_menu',
        'title' => 'Panth MegaMenu - Overview and Block Integration',
        'content' => 'The Panth MegaMenu module replaces the default Magento navigation with a customizable mega menu. The main block class is Panth\\MegaMenu\\Block\\Menu with template "Panth_MegaMenu::menu.phtml". It is inserted via layout XML into the header-wrapper container, after the logo, when enabled via config "panth_megamenu/general/enabled". It automatically removes Luma\'s default navigation (navigation.sections and catalog.topnav blocks). The module does not use a widget -- it is layout-driven.',
        'tags' => 'megamenu,navigation,block,layout,header',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'mega_menu',
        'title' => 'Panth MegaMenu - Dual Theme Support',
        'content' => 'The MegaMenu supports both Luma and Hyva themes. In default.xml, it adds the Luma menu block (Panth_MegaMenu::luma/menu.phtml). In default_hyva.xml, this Luma block is removed, and the Hyva-specific Alpine.js menu is loaded from the theme-level layout override at app/design/frontend/Panth/Infotech/Panth_MegaMenu/layout/default.xml. Hyva templates are in view/frontend/templates/hyva/ (desktop/menu.phtml, mobile/menu.phtml, sticky/header.phtml). Luma templates are in view/frontend/templates/luma/.',
        'tags' => 'megamenu,hyva,luma,theme,templates,alpine',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'mega_menu',
        'title' => 'Panth MegaMenu - Menu Configuration',
        'content' => 'Menus are loaded by identifier via $block->getMenu("identifier"). The menu tree is built from a flat JSON structure stored in the menu entity (items_json field). Each item has item_id, parent_id, title, url, and optional properties. The block method getMenuConfig() returns a comprehensive config array for Alpine.js including: hoverDelay, animationType (fade), animationDuration (200ms), maxDepth (5), columns (4), showIcons, showImages, showCategoryCount, hoverEffect (underline), and extensive sticky menu settings.',
        'tags' => 'megamenu,configuration,json,tree,menu-items',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'mega_menu',
        'title' => 'Panth MegaMenu - Sticky Menu Feature',
        'content' => 'MegaMenu includes a built-in sticky header feature. Configuration is available both per-menu (via menu entity getIsSticky()) and globally via system config. Sticky settings include: stickyEnabled, stickyOffset (default 100px), stickyHideOnScrollDown, stickyShowOnScrollUp, stickyCompactMode, stickyShadow, and stickyAnimationSpeed (300ms). The sticky header template is at Panth_MegaMenu::hyva/sticky/header.phtml for Hyva theme.',
        'tags' => 'megamenu,sticky,header,scroll,navigation',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'mega_menu',
        'title' => 'Panth MegaMenu - Mobile Menu',
        'content' => 'The MegaMenu provides dedicated mobile menu support via Panth\\MegaMenu\\Block\\MobileMenu. Mobile templates include: hyva/mobile/menu.phtml, hyva/mobile/drawer.phtml, hyva/mobile/item.phtml, and hyva/menu_mobile_premium.phtml. Mobile configuration options: mobileEnabled, mobilePosition (left/right), mobileOverlay, mobileAccordion. The mobile layout can be "accordion" by default. The mobile breakpoint is configurable via getMobileBreakpoint().',
        'tags' => 'megamenu,mobile,drawer,accordion,responsive',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'mega_menu',
        'title' => 'Panth MegaMenu - SEO and Accessibility',
        'content' => 'The MegaMenu renders proper semantic HTML for SEO: <nav> element with role="navigation", <ul>/<li> structure with level classes (level-0, level-1, etc.), proper link attributes (href, title, target, rel). Items with children get aria-haspopup="true" and aria-expanded="false" attributes. The menu supports custom CSS classes per menu (getMenuCssClass()) and custom inline CSS (getMenuCustomCss()). Link rel attributes can be configured per item for nofollow/noopener handling.',
        'tags' => 'megamenu,seo,accessibility,aria,semantic-html,navigation',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'mega_menu',
        'title' => 'Panth MegaMenu - Custom Content Blocks',
        'content' => 'Menu items can include custom content blocks rendered via CMS content areas. The menu item content is processed through processItemContent() in the MenuViewModel and rendered with column width classes via getColumnWidthClass(). Items can display icons (showIcons config), images (showImages config), and category product counts (showCategoryCount config). Custom blocks can be enabled/disabled via enableCustomBlocks config. Each item supports the show_on_frontend flag to toggle visibility.',
        'tags' => 'megamenu,content-blocks,cms,icons,images,customization',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'mega_menu',
        'title' => 'Panth MegaMenu - Menu Import/Export and Versioning',
        'content' => 'The MegaMenu module supports menu import/export functionality (visible in admin layout files: panth_menu_menu_importform.xml, import form UI component). The admin also includes versioning support via menu_version_listing UI component. Menus can be previewed before publishing through the Preview block (Panth\\MegaMenu\\Block\\Preview) and dedicated preview route/templates. This allows content teams to test menu changes before deploying them to production.',
        'tags' => 'megamenu,import,export,versioning,preview,admin',
    ],

    // =========================================================================
    // PRODUCT SLIDER (10 entries)
    // =========================================================================
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_slider',
        'title' => 'Panth ProductSlider - Advanced Widget Embedding',
        'content' => 'To embed a product slider in CMS content, use: {{widget type="Panth\\ProductSlider\\Block\\Widget\\ProductSlider" title="Featured Products" category_ids="42,43" page_size="8" columns_desktop="4" columns_tablet="2" columns_mobile="1"}}. This is the "Advanced" widget that lets you configure all parameters inline. The block class is Panth\\ProductSlider\\Block\\Widget\\ProductSlider.',
        'tags' => 'product,slider,widget,cms,embedding,advanced',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_slider',
        'title' => 'Panth ProductSlider - Slider By Identifier Widget',
        'content' => 'For sliders pre-configured in the admin (Panth > Product Slider > Manage Sliders), use the simpler widget: {{widget type="Panth\\ProductSlider\\Block\\SliderById" identifier="homepage_featured"}}. The SliderById block (Panth\\ProductSlider\\Block\\SliderById) extends the base ProductSlider block and loads all configuration from the CRUD-managed slider entity by its unique identifier. The slider must exist and be active (is_active = 1).',
        'tags' => 'product,slider,widget,identifier,admin,crud',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_slider',
        'title' => 'Panth ProductSlider - Product Selection Filters',
        'content' => 'The ProductSlider widget supports powerful product filtering: category_ids (comma-separated, e.g., "42,43,44"), include_child_categories (yes/no), product_ids (specific products by ID, preserves order using FIELD()), product_skus (filter by SKU), sale_products_only (show only products with special prices), new_products_days (products created within N days), price_from and price_to (price range filter), and exclude_out_of_stock. Multiple filters can be combined.',
        'tags' => 'product,slider,filters,category,sale,new,price,stock',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_slider',
        'title' => 'Panth ProductSlider - Sorting Options',
        'content' => 'Products in the slider can be sorted using sort_by parameter: "position" (default), "name", "price", "created_at" (newest first), or "random". Sort direction is configurable via sort_direction: "ASC" (ascending, default) or "DESC" (descending). The page_size parameter limits the number of products displayed (default: 8). When using product_ids, the order is preserved using MySQL FIELD() function.',
        'tags' => 'product,slider,sorting,order,random,position',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_slider',
        'title' => 'Panth ProductSlider - Layout and Column Configuration',
        'content' => 'The slider layout is responsive with per-breakpoint column settings: columns_mobile (default: 1), columns_tablet (default: 2), columns_desktop (default: 4). Show/hide pagination dots with show_pager (default: yes). The heading can be controlled with: title (text), heading_tag (h1-h6, default h2), and show_heading (yes/no). Example: {{widget type="Panth\\ProductSlider\\Block\\Widget\\ProductSlider" title="New Arrivals" heading_tag="h3" columns_desktop="3" columns_tablet="2" columns_mobile="1" page_size="6"}}.',
        'tags' => 'product,slider,layout,columns,responsive,heading',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_slider',
        'title' => 'Panth ProductSlider - Style Presets and Custom Styling',
        'content' => 'The ProductSlider supports style presets via style_preset parameter: "default" (from config), "modern", "minimal", "bold", or "custom" (use individual settings). Fine-grained styling includes: card_shadow (none/sm/md/lg/xl, default md), card_hover_effect (none/lift/scale/both, default lift), and custom_css_class for additional CSS classes on the slider container. These control the visual appearance of product cards within the slider.',
        'tags' => 'product,slider,styles,preset,shadow,hover,css',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_slider',
        'title' => 'Panth ProductSlider - Badge Integration',
        'content' => 'The ProductSlider has built-in badge support via Panth\\ProductSlider\\Helper\\Badge. Enable badges with enable_badges="1" parameter. Configure badge_types as a comma-separated list: "sale", "new", "stock", "featured" (default: "sale,new"). Badge position is controlled by badge_position: "top-left" (default), "top-right", "bottom-left", or "bottom-right". Badges are calculated per product based on special prices, creation date, and stock status.',
        'tags' => 'product,slider,badges,sale,new,stock,featured',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_slider',
        'title' => 'Panth ProductSlider - Autoplay and Animation',
        'content' => 'Enable auto-scrolling with enable_autoplay="1" and set the interval with autoplay_interval (milliseconds, default 3000). In the Hyva template, autoplay pauses on mouse hover and resumes on mouse leave. The Hyva template uses the native x-snap-slider directive with x-defer="intersect" for performance (only initializes when visible in viewport). A "skip carousel" link is included for accessibility.',
        'tags' => 'product,slider,autoplay,animation,performance,hyva',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_slider',
        'title' => 'Panth ProductSlider - Display Options',
        'content' => 'Control product card content visibility: hide_details="1" hides product name/price details, hide_rating="1" hides star ratings, show_add_to_cart="1" (default) shows the Add to Cart button. The Hyva template uses Hyva\'s native ProductListItem view model for consistent product card rendering, matching the look of category listing pages.',
        'tags' => 'product,slider,display,rating,add-to-cart,product-card',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_slider',
        'title' => 'Panth ProductSlider - SEO Considerations',
        'content' => 'For SEO when using product sliders: 1) Use descriptive title text for the heading (rendered as h2 by default). 2) The Hyva template wraps the slider in a <section> element with aria-label for accessibility. 3) Product images, names, and URLs are rendered by Hyva\'s native ProductListItem, ensuring consistent structured data. 4) Use heading_tag parameter to choose the right heading level for your page hierarchy. 5) Consider using the "skip carousel" accessibility link that is automatically included.',
        'tags' => 'product,slider,seo,heading,accessibility,aria,structured-data',
    ],

    // =========================================================================
    // TESTIMONIALS (8 entries)
    // =========================================================================
    [
        'category' => 'panth_modules',
        'subcategory' => 'testimonials',
        'title' => 'Panth Testimonials - Widget Embedding',
        'content' => 'To embed a testimonial slider in CMS content, use: {{widget type="Panth\\Testimonials\\Block\\Widget\\TestimonialSlider" title="What Our Customers Say" count="8" show_rating="1" show_company="1" show_image="1" autoplay="1" autoplay_interval="5000"}}. The widget class is Panth\\Testimonials\\Block\\Widget\\TestimonialSlider. Template is auto-detected: Luma uses "Panth_Testimonials::widget/slider.phtml", Hyva uses "Panth_Testimonials::hyva/widget/slider.phtml".',
        'tags' => 'testimonials,widget,slider,cms,embedding',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'testimonials',
        'title' => 'Panth Testimonials - Widget Parameters',
        'content' => 'The TestimonialSlider widget supports these parameters: title (widget title text), category_id (filter by testimonial category, uses Panth\\Testimonials\\Model\\Config\\Source\\CategoryList), count (number of testimonials, default 8), featured_only (0 or 1, show only featured testimonials), show_rating (1/0, default yes), show_company (1/0, default yes), show_image (1/0, show customer photo, default yes), autoplay (1/0, default yes), autoplay_interval (milliseconds, default 5000).',
        'tags' => 'testimonials,widget,parameters,category,featured,rating',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'testimonials',
        'title' => 'Panth Testimonials - Frontend Pages',
        'content' => 'The Testimonials module has its own frontend route at /testimonials (configurable in config). Available pages: testimonials/index/index (listing page), testimonials/view/index (individual testimonial), testimonials/category/view (testimonials by category), testimonials/submit/index (customer submission form). Each page has both Luma and Hyva templates. The default page title is "Customer Testimonials" and items per page is 12.',
        'tags' => 'testimonials,pages,listing,submit,category,route',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'testimonials',
        'title' => 'Panth Testimonials - Customer Submission',
        'content' => 'Customers can submit testimonials via the /testimonials/submit page. Submissions require admin approval by default (require_approval = 1 in config). The submission form template is available in both Luma (Panth_Testimonials::submit.phtml) and Hyva (Panth_Testimonials::hyva/submit.phtml) versions. Enable/disable submissions via panth_testimonials/submit/enabled config.',
        'tags' => 'testimonials,submit,form,customer,approval',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'testimonials',
        'title' => 'Panth Testimonials - Schema.org Structured Data',
        'content' => 'The Testimonials module automatically generates JSON-LD structured data via Panth\\Testimonials\\Block\\Schema. It outputs Organization schema with aggregateRating and individual Review entries. Each review includes: author (Person type with name, jobTitle, worksFor if company is set), reviewBody, name (title), reviewRating (Rating with ratingValue, bestRating 5, worstRating 1), and datePublished. The aggregate rating is calculated from all approved testimonials. This structured data helps Google show rich review snippets.',
        'tags' => 'testimonials,schema,json-ld,structured-data,seo,rich-snippets,aggregate-rating',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'testimonials',
        'title' => 'Panth Testimonials - Categories',
        'content' => 'Testimonials can be organized into categories (managed in admin under Panth > Testimonials > Categories). The widget\'s category_id parameter filters testimonials by category. Category pages are accessible at /testimonials/category/view. Both the admin and frontend support category-based organization, and the testimonial slider widget supports filtering by a single category ID.',
        'tags' => 'testimonials,categories,organization,filter',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'testimonials',
        'title' => 'Panth Testimonials - Widget Display on Homepage',
        'content' => 'To add a testimonial slider to the homepage, insert the widget code in the CMS page content: {{widget type="Panth\\Testimonials\\Block\\Widget\\TestimonialSlider" title="Customer Reviews" count="6" featured_only="1" show_rating="1" autoplay="1" autoplay_interval="4000"}}. For a compact display showing only featured reviews, use featured_only="1" with a lower count. Testimonials are displayed in random order by default for variety across page loads.',
        'tags' => 'testimonials,homepage,widget,featured,display',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'testimonials',
        'title' => 'Panth Testimonials - SEO and Content Tips',
        'content' => 'For optimal SEO with testimonials: 1) The module automatically generates Review schema markup (JSON-LD) with aggregate ratings -- no manual configuration needed. 2) Customer names and companies are included in the schema for trust signals. 3) The testimonials listing page (/testimonials) has configurable meta title and meta description in system config under panth_testimonials/general. 4) Use the widget on product or category pages to add social proof where it matters most. 5) Featured testimonials can be prioritized for homepage display.',
        'tags' => 'testimonials,seo,schema,meta,social-proof,trust',
    ],

    // =========================================================================
    // FAQ (10 entries)
    // =========================================================================
    [
        'category' => 'panth_modules',
        'subcategory' => 'faq',
        'title' => 'Panth FAQ - Widget Embedding',
        'content' => 'To embed FAQs in CMS content, use: {{widget type="Panth\\Faq\\Block\\Widget\\Faq" title="Frequently Asked Questions" limit="10" show_view_all="1"}}. The widget class is Panth\\Faq\\Block\\Widget\\Faq with template "Panth_Faq::widget/faq.phtml". You can select specific FAQ items: {{widget type="Panth\\Faq\\Block\\Widget\\Faq" faq_items="1,5,8,12" title="Common Questions"}}. Items are displayed in the order specified when using faq_items.',
        'tags' => 'faq,widget,cms,embedding,questions',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'faq',
        'title' => 'Panth FAQ - Widget Parameters',
        'content' => 'The FAQ widget supports these parameters: title (custom section title, defaults to "Frequently Asked Questions"), faq_items (comma-separated FAQ item IDs for specific selection -- uses multiselect in admin), limit (maximum number of FAQs, default 10), show_view_all (1/0, show link to main FAQ page, default yes). When faq_items are specified, they display in the given order using MySQL FIELD(). When not specified, FAQs are sorted by sort_order ascending.',
        'tags' => 'faq,widget,parameters,limit,view-all',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'faq',
        'title' => 'Panth FAQ - Frontend Pages',
        'content' => 'The FAQ module has its own route at /faq (configurable via panth_faq/general/faq_route). Available pages: /faq (main listing with search and category filter), /faq/index/view/id/N (individual FAQ view), /faq/category/view/id/N (category-filtered FAQ listing). Each page has both standard and Hyva templates (hyva/index/index.phtml, hyva/index/view.phtml, hyva/category/view.phtml). Default meta title: "Frequently Asked Questions".',
        'tags' => 'faq,pages,route,listing,category,frontend',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'faq',
        'title' => 'Panth FAQ - Schema.org FAQPage Structured Data',
        'content' => 'The FAQ module automatically generates FAQPage JSON-LD schema via Panth\\Faq\\Block\\Schema. The schema includes @type "FAQPage" with mainEntity array of Question/Answer pairs. Each question has @type "Question" with name, and acceptedAnswer of @type "Answer" with text (HTML stripped). Schema is generated for FAQ pages, product pages (if product FAQ is enabled), category pages, and CMS pages. Enable/disable via panth_faq/seo/enable_schema config (default enabled).',
        'tags' => 'faq,schema,json-ld,faqpage,structured-data,seo,rich-snippets',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'faq',
        'title' => 'Panth FAQ - Product Page Integration',
        'content' => 'FAQs can be displayed on product pages. Enable via panth_faq/product_page/enabled (default yes). Configure: title (default "Frequently Asked Questions"), position ("tab" to show in product tabs), and limit (default 10). FAQs are assigned to products in the admin (FAQ item edit form has a product assignment tab). The layout file catalog_product_view.xml adds the FAQ block. Schema markup is also generated for product-specific FAQs.',
        'tags' => 'faq,product-page,tab,product-faq,assignment',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'faq',
        'title' => 'Panth FAQ - Category Page Integration',
        'content' => 'FAQs can be displayed on category pages. Enable via panth_faq/category_page/enabled (default yes). Configure: title (default "Category FAQs"), position ("bottom" of category page), and limit (default 10). FAQs are assigned to categories in the admin. The layout file catalog_category_view.xml adds the FAQ block. Category-specific FAQ schema markup is also generated when enabled.',
        'tags' => 'faq,category-page,category-faq,assignment,bottom',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'faq',
        'title' => 'Panth FAQ - CMS Page Integration',
        'content' => 'FAQs can be embedded on CMS pages in two ways: 1) Via the FAQ widget: {{widget type="Panth\\Faq\\Block\\Widget\\Faq" title="Page FAQs" limit="5"}}. 2) Via automatic assignment: FAQs can be assigned to CMS pages in the admin (FAQ item has a Pages tab), and the layout file cms_page_view.xml adds them automatically. CMS page FAQs are enabled via panth_faq/cms_page/enabled (default yes) with configurable title.',
        'tags' => 'faq,cms-page,widget,assignment,automatic',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'faq',
        'title' => 'Panth FAQ - Widget HTML Structure and Accordion',
        'content' => 'The FAQ widget renders an accordion-style UI with: a search box for filtering FAQs in real-time, individual FAQ items with clickable headers (h3 for question, expandable answer div), and an optional "View All FAQs" link. Each FAQ item stores data-question and data-answer attributes for client-side search filtering. The accordion works on both Hyva (vanilla JS) and Luma (RequireJS). Search is debounced at 300ms and supports real-time filtering as you type.',
        'tags' => 'faq,accordion,search,html,ui,interaction',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'faq',
        'title' => 'Panth FAQ - Display Configuration',
        'content' => 'The FAQ module has extensive display configuration under panth_faq/display: items_per_page (default 20), show_category_description (show category descriptions on listing), show_search (enable search box), show_category_filter (enable category dropdown filter), default_open_faqs (how many FAQs to show expanded by default, 0 = all collapsed), show_view_count (show how many times an FAQ has been viewed), and enable_helpful_voting (allow users to rate FAQs as helpful).',
        'tags' => 'faq,display,configuration,search,filter,voting',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'faq',
        'title' => 'Panth FAQ - SEO Best Practices',
        'content' => 'For optimal FAQ SEO: 1) FAQPage schema is automatically generated -- ensure panth_faq/seo/enable_schema is enabled. 2) Canonical URLs are supported via panth_faq/seo/canonical_url config. 3) Set meta title and meta description in panth_faq/general for the main FAQ page. 4) Assign FAQs to relevant product and category pages to generate page-specific FAQ schema. 5) Use clear, concise questions and detailed answers -- the schema strips HTML from answers. 6) The FAQ widget on landing pages adds FAQ schema to those pages too, improving visibility in Google search.',
        'tags' => 'faq,seo,schema,canonical,meta,rich-results',
    ],

    // =========================================================================
    // PRODUCT ATTACHMENTS (8 entries)
    // =========================================================================
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_attachments',
        'title' => 'Panth ProductAttachments - Widget Embedding',
        'content' => 'To embed attachments in CMS content, use: {{widget type="Panth\\ProductAttachments\\Block\\Widget\\Attachments" title="Downloads" display_mode="table"}}. The widget class is Panth\\ProductAttachments\\Block\\Widget\\Attachments. Templates auto-detect theme: Luma uses "Panth_ProductAttachments::attachment/renderer.phtml", Hyva uses "Panth_ProductAttachments::attachment/renderer_hyva.phtml". You can also force a template: template="Panth_ProductAttachments::widget/attachments_hyva.phtml".',
        'tags' => 'attachments,widget,cms,downloads,embedding',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_attachments',
        'title' => 'Panth ProductAttachments - Widget Parameters',
        'content' => 'The Attachments widget supports: title (heading text, default "Attachments"), attachment_ids (comma-separated IDs, e.g., "1,2,3" -- leave empty for all), type_id (filter by attachment type, uses Panth\\ProductAttachments\\Model\\Config\\Source\\AttachmentType), limit (max number of attachments), display_mode ("table" for table view or "list" for list view, default table), template (override template selection). Example: {{widget type="Panth\\ProductAttachments\\Block\\Widget\\Attachments" attachment_ids="1,5,8" display_mode="list" limit="5"}}.',
        'tags' => 'attachments,widget,parameters,table,list,type,limit',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_attachments',
        'title' => 'Panth ProductAttachments - Product Page Display',
        'content' => 'Attachments automatically appear on product pages via the catalog_product_view.xml layout. The block Panth\\ProductAttachments\\Block\\Product\\View\\Attachments handles product-specific attachment display. Attachments are assigned to products in the admin (attachment edit form has a Products tab). The display supports customer group restrictions -- attachments can be limited to specific customer groups. Both table and list view modes are available with Hyva-specific templates.',
        'tags' => 'attachments,product-page,display,customer-group,assignment',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_attachments',
        'title' => 'Panth ProductAttachments - Category and CMS Page Integration',
        'content' => 'Attachments can be assigned to categories (via catalog_category_view.xml layout, Panth\\ProductAttachments\\Block\\Category\\View\\Attachments) and CMS pages (via cms_page_view.xml layout, Panth\\ProductAttachments\\Block\\Cms\\Attachments). Attachments have multi-entity assignment: a single attachment can be linked to multiple products, categories, and CMS pages simultaneously via the admin attachment edit tabs.',
        'tags' => 'attachments,category,cms-page,assignment,multi-entity',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_attachments',
        'title' => 'Panth ProductAttachments - Attachment Types',
        'content' => 'Attachments are organized by configurable types (managed in admin). The type system allows categorizing attachments as manuals, datasheets, certificates, warranty docs, etc. Types are managed via Panth > Product Attachments > Manage Types in the admin. The widget\'s type_id parameter filters attachments by type. Attachments can also be grouped by type in the display using the getAttachmentsByType() method.',
        'tags' => 'attachments,types,organization,manuals,datasheets,certificates',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_attachments',
        'title' => 'Panth ProductAttachments - File Management Features',
        'content' => 'The module includes: version tracking for attachments (version_listing.xml in admin), download analytics (analytics_listing.xml), file expiration dates (addNotExpiredFilter on collection), file preview capability (isPreviewable check based on file extension), file size display (configurable via showFileSize()), description display (configurable via showDescription()), and unused file cleanup (unusedfiles_listing.xml). Download permissions are checked via canDownload() based on customer group.',
        'tags' => 'attachments,versioning,analytics,preview,download,file-management',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_attachments',
        'title' => 'Panth ProductAttachments - Display Modes',
        'content' => 'Attachments can be displayed in two modes: "table" view (Panth_ProductAttachments::attachment/view-modes/table.phtml or table_hyva.phtml) shows attachments in a structured table with columns for name, type, size, and download link. "list" view (Panth_ProductAttachments::attachment/view-modes/list.phtml or list_hyva.phtml) shows a simpler vertical list. Both modes have Hyva-specific templates with Tailwind CSS styling. The renderer block (Panth\\ProductAttachments\\Block\\Attachment\\Renderer) handles the view mode switching.',
        'tags' => 'attachments,display-modes,table,list,templates,hyva',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_attachments',
        'title' => 'Panth ProductAttachments - SEO and Content Strategy',
        'content' => 'For SEO with product attachments: 1) Provide downloadable resources (manuals, guides, spec sheets) on product pages to increase page value and dwell time. 2) Attachments on CMS landing pages can serve as lead magnets. 3) Use descriptive attachment titles and descriptions for internal search optimization. 4) The module supports email notification templates for new attachments (email_templates.xml). 5) Attachment analytics help track which resources are most valuable to customers. 6) Consider adding product-specific FAQs alongside attachments for comprehensive product information.',
        'tags' => 'attachments,seo,content-strategy,downloads,analytics,email',
    ],

    // =========================================================================
    // SMART BADGE (7 entries)
    // =========================================================================
    [
        'category' => 'panth_modules',
        'subcategory' => 'smart_badge',
        'title' => 'Panth SmartBadge - Overview',
        'content' => 'The SmartBadge module automatically displays promotional badges on products (e.g., "NEW", "SALE", "HOT", "LOW STOCK"). The main block is Panth\\SmartBadge\\Block\\Badge with template "Panth_SmartBadge::badge.phtml". Badges appear on product detail pages (catalog_product_view.xml) and category listing pages (catalog_category_view.xml). The module does not use a widget -- badges are applied automatically based on rules configured in the admin under Panth > Smart Badge > Manage Rules.',
        'tags' => 'badge,smart-badge,automatic,product,promotion',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'smart_badge',
        'title' => 'Panth SmartBadge - Auto Badge Types',
        'content' => 'SmartBadge supports these automatic badge types: "new" (products created within configurable days, default 30 -- uses green #16A34A), "sale" (products with active special price -- uses red #DC2626), "low stock" / "limited" (stock below configurable threshold, default 5 units -- uses purple #7C3AED), "hot" (trending products -- uses orange #F97316), "bestseller" (top-selling products -- uses purple #7C3AED), "exclusive" (#6D28D9), "featured" (#0D9488), and "trending" (#0891B2). Each type can be individually enabled/disabled in config.',
        'tags' => 'badge,types,new,sale,low-stock,hot,bestseller,colors',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'smart_badge',
        'title' => 'Panth SmartBadge - Configuration',
        'content' => 'SmartBadge configuration is under smart_badge/ in system config: general/enabled (on/off), general/show_multiple (show multiple badges per product, default yes), general/max_badges (maximum badges to display, default 3). Auto badge settings: auto_badges/new_enabled, auto_badges/new_days (30), auto_badges/sale_enabled, auto_badges/low_stock_enabled, auto_badges/low_stock_threshold (5), auto_badges/bestseller_enabled, auto_badges/hot_enabled. Display settings: display/badge_layout ("vertical" default), display/badge_spacing ("gap-2" default).',
        'tags' => 'badge,configuration,settings,threshold,multiple,layout',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'smart_badge',
        'title' => 'Panth SmartBadge - Custom Badge Rules',
        'content' => 'Beyond auto-badges, SmartBadge supports custom rules managed via Panth > Smart Badge > Manage Rules in admin. The rule form (smartbadge_rule_form.xml) includes SmartConditions for product matching and AdvancedStyling for visual customization. Rules can target specific products via the AssignProducts tab. Each rule defines a badge with custom label, icon, color, and CSS class. The block supports setProduct() for programmatic use in product lists.',
        'tags' => 'badge,rules,custom,conditions,styling,admin',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'smart_badge',
        'title' => 'Panth SmartBadge - Visual Design and Animations',
        'content' => 'Badge styling uses CSS custom properties: --badge-sale (#DC2626), --badge-new (#16A34A), --badge-hot (#F97316), --badge-limited (#7C3AED), --badge-exclusive (#6D28D9), --badge-bestseller (#7C3AED), --badge-trending (#0891B2), --badge-featured (#0D9488). Badges support custom colors via customColor or cssVar per badge. Each badge type has a unique animation: "new" pulses, "sale" glows, "stock" shakes, "hot" bounces. Badges are positioned absolutely at top-left of the product image container. Staggered entry animation via CSS variable --i.',
        'tags' => 'badge,css,animations,colors,design,variables',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'smart_badge',
        'title' => 'Panth SmartBadge - HTML Structure',
        'content' => 'The badge template renders: <div class="smart-badges-container"> wrapping individual <div class="smart-badge badge-{type} {class}"> elements. Each badge contains a <span class="badge-icon"> and <span class="badge-text">. The container uses position:absolute, z-index:10, flex-direction:column layout. Badges are pointer-events:none to not interfere with product links. CSS is inlined in the template for zero-FOUC rendering. Mobile badges use smaller font-size (0.625rem) and padding.',
        'tags' => 'badge,html,structure,template,css,mobile',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'smart_badge',
        'title' => 'Panth SmartBadge - SEO and Conversion Tips',
        'content' => 'SmartBadge does not directly affect SEO markup but influences user behavior and conversion rates. Tips: 1) Use sale badges to highlight discounted products -- the glow animation draws attention. 2) "New" badges on recently added products help with product discovery. 3) "Low Stock" badges create urgency (shake animation reinforces this). 4) Limit to max 3 badges per product to avoid visual clutter. 5) Badge labels are rendered as text (not images), so they are accessible to screen readers. 6) When writing CMS content about promotions, mention that products will automatically show sale/new badges.',
        'tags' => 'badge,seo,conversion,urgency,promotion,tips',
    ],

    // =========================================================================
    // PRODUCT GALLERY (7 entries)
    // =========================================================================
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_gallery',
        'title' => 'Panth ProductGallery - Overview',
        'content' => 'The ProductGallery module enhances the default Magento product image gallery with zoom, lightbox, and configurable thumbnail layout. The main block is Panth\\ProductGallery\\Block\\Gallery and the widget block is Panth\\ProductGallery\\Block\\Widget\\Gallery. It replaces the standard gallery on product detail pages via the catalog_product_view.xml and hyva_catalog_product_view.xml layouts. Template auto-detection switches between "Panth_ProductGallery::gallery.phtml" (Luma) and "Panth_ProductGallery::hyva/gallery.phtml" (Hyva).',
        'tags' => 'gallery,product,images,zoom,lightbox,thumbnails',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_gallery',
        'title' => 'Panth ProductGallery - Widget Embedding',
        'content' => 'The ProductGallery can be embedded as a widget: {{widget type="Panth\\ProductGallery\\Block\\Widget\\Gallery" template="Panth_ProductGallery::gallery.phtml"}}. The widget extends the Gallery block and inherits all its functionality. However, the gallery requires a current product context (it reads from the registry), so the widget is primarily useful on product-related pages. On product detail pages, the gallery is loaded automatically via layout XML.',
        'tags' => 'gallery,widget,product-page,embedding',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_gallery',
        'title' => 'Panth ProductGallery - Layout Configuration',
        'content' => 'Gallery layout is configured under panth_productgallery/layout: layout_type ("horizontal" default), thumb_position ("bottom" default), main_image_width (700px), main_image_height (700px), thumb_width (72px), thumb_height (72px), visible_thumbs (5). These settings control the gallery dimensions and thumbnail strip appearance. The Hyva template reads these values via the ViewModel (Panth\\ProductGallery\\ViewModel\\Config).',
        'tags' => 'gallery,layout,thumbnails,dimensions,configuration',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_gallery',
        'title' => 'Panth ProductGallery - Zoom Feature',
        'content' => 'The gallery includes built-in image zoom configured under panth_productgallery/zoom: enable_zoom (default yes), zoom_type ("inner" -- zoom appears inside the main image area), zoom_level (magnification factor, default 3x). The Hyva template implements zoom with Alpine.js. When enabled, hovering over the main product image shows a magnified view at the configured zoom level.',
        'tags' => 'gallery,zoom,magnification,hover,alpine',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_gallery',
        'title' => 'Panth ProductGallery - Lightbox Feature',
        'content' => 'The gallery includes a fullscreen lightbox configured under panth_productgallery/lightbox: enable_lightbox (default yes), show_counter (show image counter like "2 of 8", default yes), enable_keyboard_nav (arrow key navigation in lightbox, default yes). When enabled, clicking the main product image opens a fullscreen overlay showing the large version of the image with navigation controls.',
        'tags' => 'gallery,lightbox,fullscreen,keyboard,navigation',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_gallery',
        'title' => 'Panth ProductGallery - Navigation and Swipe',
        'content' => 'Gallery navigation is configured under panth_productgallery/navigation: show_arrows (prev/next buttons, default yes), enable_swipe (touch swipe for mobile, default yes), infinite_loop (loop back to first image after last, default no). The gallery image data includes thumb, medium, and large URLs for each image, plus alt text (falls back to product name if image label is empty) and position for sorting.',
        'tags' => 'gallery,navigation,swipe,arrows,mobile,touch',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'product_gallery',
        'title' => 'Panth ProductGallery - SEO and Image Optimization',
        'content' => 'For SEO with the ProductGallery: 1) All gallery images include alt text -- the block uses the image label or falls back to the product name. 2) Images are resized server-side to configured dimensions (main: 700x700, thumb: 72x72) for performance. 3) Three image sizes are generated: thumb (for thumbnails), medium (for main display), and large (for lightbox/zoom). 4) Disabled images are automatically excluded. 5) Images are sorted by their position attribute. 6) The Hyva template uses Alpine.js for interactive features without heavy JavaScript libraries.',
        'tags' => 'gallery,seo,alt-text,image-optimization,performance,responsive',
    ],

    // =========================================================================
    // CROSS-MODULE ENTRIES (5 entries)
    // =========================================================================
    [
        'category' => 'panth_modules',
        'subcategory' => 'cross_module',
        'title' => 'Panth Modules - Common Widget Embedding Patterns',
        'content' => 'All Panth widget-enabled modules follow the same embedding pattern in CMS pages/blocks: {{widget type="Full\\Block\\Class\\Name" param1="value1" param2="value2"}}. Key widget classes: Panth\\BannerSlider\\Block\\Widget\\BannerSlider (identifier required), Panth\\ProductSlider\\Block\\Widget\\ProductSlider (advanced config), Panth\\ProductSlider\\Block\\SliderById (identifier required), Panth\\Testimonials\\Block\\Widget\\TestimonialSlider, Panth\\Faq\\Block\\Widget\\Faq, Panth\\ProductAttachments\\Block\\Widget\\Attachments, Panth\\ProductGallery\\Block\\Widget\\Gallery. All support the template parameter to override template selection.',
        'tags' => 'widget,embedding,cms,all-modules,reference',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'cross_module',
        'title' => 'Panth Modules - Hyva Theme Compatibility',
        'content' => 'All Panth modules support dual-theme operation (Luma and Hyva). Theme detection is handled by Panth\\Core\\Helper\\Theme::isHyva(). Each module auto-switches templates: BannerSlider (Alpine.js carousel with CSS transitions), ProductSlider (Hyva snap-slider with ProductListItem), MegaMenu (Alpine.js components), Testimonials (Hyva slider template), FAQ (vanilla JS accordion), ProductAttachments (Tailwind-styled table/list), SmartBadge (CSS animations), ProductGallery (Alpine.js zoom/lightbox). No manual template selection is needed -- auto-detection works transparently.',
        'tags' => 'hyva,theme,compatibility,alpine,tailwind,auto-detection',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'cross_module',
        'title' => 'Panth Modules - Schema Markup Summary',
        'content' => 'Two Panth modules generate structured data automatically: 1) Panth_Faq generates FAQPage schema (JSON-LD) with Question/Answer pairs on FAQ pages, product pages, category pages, and CMS pages where FAQs are assigned. 2) Panth_Testimonials generates Organization schema with aggregateRating and Review entries on testimonial pages. Both use JSON-LD format in <script type="application/ld+json"> tags. These help achieve rich results in Google Search (FAQ rich results, review stars).',
        'tags' => 'schema,structured-data,json-ld,faq,testimonials,rich-results',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'cross_module',
        'title' => 'Panth Modules - Homepage Content Strategy',
        'content' => 'For a complete homepage using Panth modules, combine: 1) BannerSlider at top: {{widget type="Panth\\BannerSlider\\Block\\Widget\\BannerSlider" identifier="homepage_banner"}}. 2) Featured products: {{widget type="Panth\\ProductSlider\\Block\\Widget\\ProductSlider" title="Featured Products" category_ids="42" page_size="8" style_preset="modern"}}. 3) New arrivals: {{widget type="Panth\\ProductSlider\\Block\\Widget\\ProductSlider" title="New Arrivals" new_products_days="30" sort_by="created_at" sort_direction="DESC"}}. 4) Testimonials: {{widget type="Panth\\Testimonials\\Block\\Widget\\TestimonialSlider" title="Customer Reviews" featured_only="1" count="6"}}. 5) FAQs: {{widget type="Panth\\Faq\\Block\\Widget\\Faq" title="Common Questions" limit="5"}}.',
        'tags' => 'homepage,content-strategy,all-modules,widget-examples',
    ],
    [
        'category' => 'panth_modules',
        'subcategory' => 'cross_module',
        'title' => 'Panth Modules - Product Page Enhancement Strategy',
        'content' => 'For enhanced product pages using Panth modules: 1) ProductGallery replaces the default gallery with zoom, lightbox, and swipe. 2) SmartBadge auto-shows sale/new/low-stock badges on product images. 3) FAQ module adds a product-specific FAQ tab (configurable position). 4) ProductAttachments shows downloadable files (manuals, specs) assigned to the product. 5) ProductSlider can show related/upsell products: {{widget type="Panth\\ProductSlider\\Block\\Widget\\ProductSlider" title="You May Also Like" category_ids="SAME_CAT" page_size="4"}}. All these work together automatically via layout XML on catalog_product_view.',
        'tags' => 'product-page,enhancement,gallery,badges,faq,attachments,slider',
    ],
];
