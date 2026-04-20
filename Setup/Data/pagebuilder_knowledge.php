<?php
/**
 * Panth AdvancedSEO - PageBuilder Knowledge Base
 *
 * Comprehensive AI training data about Magento PageBuilder components,
 * HTML output structures, CSS classes, appearance variants, and SEO implications.
 *
 * Sourced from: vendor/magento/module-page-builder/view/adminhtml/pagebuilder/content_type/*.xml
 * and vendor/magento/module-page-builder/view/adminhtml/web/template/content-type/ master templates.
 */

return [

    // =========================================================================
    // ROW COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'row',
        'title' => 'PageBuilder Row - Contained Appearance (Default)',
        'content' => 'The "contained" appearance is the default row type. It wraps content in a max-width container centered on the page. HTML output: <div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-element="inner" style="justify-content: flex-start; display: flex; flex-direction: column; background-position: left top; background-size: cover; background-repeat: no-repeat; background-attachment: scroll; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 10px;">CHILD CONTENT HERE</div></div>. The outer div has data-content-type="row" and data-appearance="contained". The inner div carries all style properties: background, borders, margins, padding, parallax data attributes, and video background attributes. A third nested div (container element) provides flex layout with justify-content, display:flex, and flex-direction:column. If video overlay is set, a <div class="video-overlay"> appears inside the inner div.',
        'tags' => 'row,contained,layout,wrapper,section,container,default',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'row',
        'title' => 'PageBuilder Row - Full Width Appearance',
        'content' => 'The "full-width" row stretches its background to the full browser width while keeping inner content constrained. HTML output: <div data-content-type="row" data-appearance="full-width" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-element="main" style="justify-content: flex-start; display: flex; flex-direction: column; background-color: rgb(255,255,255); background-position: left top; background-size: cover; background-repeat: no-repeat; background-attachment: scroll; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 10px;"><div class="row-full-width-inner" data-element="inner">CHILD CONTENT HERE</div></div>. Key differences from contained: the main div carries all background/style/parallax attributes directly (not the inner div). The inner div has class "row-full-width-inner" and is used for max-width constraint. Video overlay div appears before the inner div if configured.',
        'tags' => 'row,full-width,layout,wrapper,section,background,stretch',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'row',
        'title' => 'PageBuilder Row - Full Bleed Appearance',
        'content' => 'The "full-bleed" row stretches both background AND content to the full browser width with no inner container constraint. HTML output: <div data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-element="main" style="justify-content: flex-start; display: flex; flex-direction: column; background-position: left top; background-size: cover; background-repeat: no-repeat; background-attachment: scroll; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 10px;">CHILD CONTENT HERE</div>. This is the simplest row structure - a single div with all styles applied directly. No inner wrapper div. Child content renders directly inside the main div. Video overlay div appears inside main if configured.',
        'tags' => 'row,full-bleed,layout,edge-to-edge,full-screen',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'row',
        'title' => 'PageBuilder Row - Data Attributes Reference',
        'content' => 'Every row outputs these data attributes on the appropriate element: data-content-type="row" (always "row"), data-appearance="contained|full-width|full-bleed", data-enable-parallax="0|1", data-parallax-speed="0.5" (decimal 0-1), data-background-images="{}" (JSON object with desktop/mobile keys like {"desktop_image":"{{media url=wysiwyg/image.jpg}}"}), data-background-type="image|video". Video-specific attributes: data-video-src="URL", data-video-loop="true|false", data-video-play-only-visible="true|false", data-video-lazy-load="true|false", data-video-fallback-src="URL". The video overlay element uses data-video-overlay-color for its color attribute.',
        'tags' => 'row,attributes,parallax,video,background,data-attributes',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'row',
        'title' => 'PageBuilder Row - Style Properties',
        'content' => 'Row style properties applied via inline styles: background_color, background_image (as CSS url()), background_position (e.g., "left top", "center center"), background_size (e.g., "cover", "contain"), background_repeat ("no-repeat", "repeat"), background_attachment ("scroll", "fixed"), text_align ("left", "center", "right"), border_style ("none", "solid", "dashed", "dotted", "double", "groove", "ridge", "inset", "outset"), border_color, border_width (px), border_radius (px), min_height (px), justify_content ("flex-start", "center", "flex-end"), display ("flex", "none"), margins (margin shorthand or individual), padding (padding shorthand or individual). The container element always has: display:flex; flex-direction:column.',
        'tags' => 'row,styles,css,inline-styles,background,border,padding,margin',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'row',
        'title' => 'PageBuilder Row - Parent/Child Rules',
        'content' => 'Row content type hierarchy rules: Parents - rows can only be placed inside root-container (the top-level page content area). Children - rows accept all content types EXCEPT other rows (child name="row" policy="deny"). This means you cannot nest rows inside rows. Rows are the primary top-level layout container. Common child types: column-group (columns), heading, text, image, banner, slider, tabs, buttons, divider, html, block, products, map, video.',
        'tags' => 'row,hierarchy,parent,children,nesting,rules,structure',
    ],

    // =========================================================================
    // COLUMN GROUP COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'column-group',
        'title' => 'PageBuilder Column Group - Default Appearance',
        'content' => 'Column Group is the container for column layouts. HTML output: <div class="pagebuilder-column-group" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" style="display: flex; width: 100%; align-self: stretch;">COLUMN-LINE AND COLUMN CHILDREN</div>. The column group uses a 12-column grid system specified by data-grid-size="12". It always has the CSS class "pagebuilder-column-group". Style properties: background_color, background_image, background_position, background_size, background_repeat, background_attachment, text_align, border styles, justify_content, min_height, width, margins, padding, display. Static style: align-self: stretch. Data attributes: data-content-type="column-group", data-appearance="default", data-grid-size (grid column count, default 12), data-background-images (JSON).',
        'tags' => 'column-group,columns,grid,layout,12-column,container',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'column-group',
        'title' => 'PageBuilder Column Group - Parent/Child Rules',
        'content' => 'Column Group hierarchy: Parents - can be placed inside root-container, row, column-group (nested), or tab-item. Children - only allows column-line and column as direct children. The column-group acts as a wrapper that establishes the grid context. Inside it, column-line elements hold the actual columns. This two-level nesting (column-group > column-line > column) allows for complex multi-row column layouts within a single column group.',
        'tags' => 'column-group,hierarchy,nesting,column-line,parent,children',
    ],

    // =========================================================================
    // COLUMN LINE COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'column-line',
        'title' => 'PageBuilder Column Line - Default Appearance',
        'content' => 'Column Line is a system component that represents a single row of columns within a column group. HTML output: <div class="pagebuilder-column-line" data-content-type="column-line" data-appearance="default" data-grid-size="12" data-element="main" style="display: flex; width: 100%;">COLUMN CHILDREN</div>. The column-line always has class "pagebuilder-column-line" and static styles display:flex and width:100%. It carries data-grid-size to define the grid. Column-line is a system type (is_system=false means it does not appear in the menu). It can only exist inside a column-group and only accepts column children.',
        'tags' => 'column-line,layout,flex,system,grid,columns',
    ],

    // =========================================================================
    // COLUMN COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'column',
        'title' => 'PageBuilder Column - Full Height Appearance (Default)',
        'content' => 'Column with "full-height" appearance (default). HTML output: <div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" style="justify-content: flex-start; display: flex; flex-direction: column; align-self: stretch; background-position: left top; background-size: cover; background-repeat: no-repeat; background-attachment: scroll; border-style: none; border-width: 1px; border-radius: 0px; width: 50%; margin: 0px; padding: 10px;">CHILD CONTENT</div>. The column always has class "pagebuilder-column". The width is set as a percentage (e.g., "50%", "33.3333%", "25%") based on the grid. Static style: align-self: stretch (makes column fill parent height). Container element inside provides flex column layout.',
        'tags' => 'column,layout,full-height,flex,width,percentage,grid',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'column',
        'title' => 'PageBuilder Column - Alignment Appearances',
        'content' => 'Columns have four appearance variants controlling vertical content alignment: 1) "full-height" (default) - align-self: stretch - column stretches to fill parent height. 2) "align-top" - align-self: flex-start - column content aligns to top, column does not stretch. 3) "align-center" - align-self: center - column content centers vertically. 4) "align-bottom" - align-self: flex-end - column content aligns to bottom. All four use the same master template (column/full-height/master.html) producing: <div class="pagebuilder-column" data-content-type="column" data-appearance="full-height|align-top|align-center|align-bottom" style="align-self: stretch|flex-start|center|flex-end; ...">. The container element inside always has display:flex; flex-direction:column; and the configured justify-content value.',
        'tags' => 'column,alignment,vertical,stretch,top,center,bottom,flex',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'column',
        'title' => 'PageBuilder Column - Width and Grid System',
        'content' => 'Column widths use percentage values based on a 12-column grid (configurable via data-grid-size on column-group). Common width values: 1/12 = 8.3333%, 2/12 = 16.6667%, 3/12 = 25%, 4/12 = 33.3333%, 5/12 = 41.6667%, 6/12 = 50%, 7/12 = 58.3333%, 8/12 = 66.6667%, 9/12 = 75%, 10/12 = 83.3333%, 11/12 = 91.6667%, 12/12 = 100%. Width is applied as inline style: style="width: 50%". Columns within a column-line should have widths that sum to 100%. The column-line uses display:flex so columns sit side by side. Column parents: can only be inside column-line or column (nested columns). Column children: accepts all types EXCEPT row, column, and column-line.',
        'tags' => 'column,width,grid,percentage,12-column,responsive,sizing',
    ],

    // =========================================================================
    // HEADING COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'heading',
        'title' => 'PageBuilder Heading - HTML Output Structure',
        'content' => 'The heading component renders as a semantic HTML heading tag (h1-h6). HTML output varies by heading_type: <h1 data-content-type="heading" data-appearance="default" data-element="main" style="text-align: left; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 0px;">Heading Text</h1>. Or <h2 ...>, <h3 ...>, etc. The template conditionally renders the correct tag: h1 if heading_type=="h1", h2 if heading_type=="h2", through h6. The heading text is placed as inner HTML content. Style properties: text_align, border_style, border_color, border_width, border_radius, display, margins, padding. Data attributes: data-content-type="heading", data-appearance="default". Custom CSS classes can be added.',
        'tags' => 'heading,h1,h2,h3,h4,h5,h6,semantic,title,text',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'heading',
        'title' => 'PageBuilder Heading - SEO Implications',
        'content' => 'PageBuilder headings have critical SEO implications: 1) Heading hierarchy - h1 should be used once per page (usually the page title). AI-generated content should use h2 for main sections and h3-h6 for subsections. 2) The heading text is rendered as innerHTML, supporting basic HTML formatting. 3) When generating PageBuilder content for SEO, ensure proper heading hierarchy: use h2 for product feature sections, h3 for individual features. 4) Example SEO-optimized heading: <h2 data-content-type="heading" data-appearance="default" style="">Product Features</h2>. 5) Avoid skipping heading levels (e.g., h1 then h3 without h2). 6) The heading_type attribute controls which tag renders - this directly impacts how search engines understand page structure.',
        'tags' => 'heading,seo,hierarchy,h1,h2,accessibility,semantic,structure',
    ],

    // =========================================================================
    // TEXT COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'text',
        'title' => 'PageBuilder Text - HTML Output Structure',
        'content' => 'The text component renders rich HTML content inside a div. HTML output: <div data-content-type="text" data-appearance="default" data-element="main" style="border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 0px;" class="bypass-html-filter"><p>Your paragraph text here</p><p>Second paragraph</p></div>. The div has class "bypass-html-filter" which ensures the WYSIWYG HTML content is not sanitized on output. The inner HTML comes from the WYSIWYG editor and can contain: paragraphs (<p>), bold (<strong>), italic (<em>), links (<a>), lists (<ul>/<ol>), and Magento widget directives like {{widget type="..." }}. Style properties: text_align, border styles, display, margins, padding. Supports custom CSS classes.',
        'tags' => 'text,paragraph,wysiwyg,rich-text,content,html',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'text',
        'title' => 'PageBuilder Text - SEO Best Practices',
        'content' => 'Text component SEO considerations: 1) Content inside the text div is fully crawlable by search engines. 2) Use semantic HTML within text content: <p> for paragraphs, <strong> for important keywords (not just bold), <em> for emphasis, <ul>/<ol> for lists. 3) Widget directives ({{widget ...}}) are rendered server-side before output, so their content is SEO-friendly. 4) Internal links using <a href="{{store url=...}}"> are resolved to proper URLs. 5) Avoid excessive inline styles in WYSIWYG content - use CSS classes instead. 6) Example SEO text: <div data-content-type="text" data-appearance="default" style=""><p>Our <strong>premium quality</strong> products are designed for...</p></div>. 7) The text component supports Magento directives: {{media url="..."}} for images, {{store url="..."}} for links, {{widget ...}} for dynamic content.',
        'tags' => 'text,seo,content,semantic,html,links,keywords',
    ],

    // =========================================================================
    // HTML CODE COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'html',
        'title' => 'PageBuilder HTML Code - Output Structure',
        'content' => 'The HTML Code component renders raw HTML/CSS/JS content. HTML output: <div data-content-type="html" data-appearance="default" data-element="main" style="border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 0px;" class="bypass-html-filter">RAW HTML CONTENT HERE</div>. Unlike the Text component which uses html binding, the HTML component uses text binding (content is HTML-decoded). The "bypass-html-filter" class prevents content sanitization. The HTML content is decoded from stored format using the html/decode converter. Can contain: custom HTML, CSS (<style> tags), JavaScript (<script> tags), Magento widgets and directives. Parents: root-container, row, column, tab-item. Style properties: text_align, border styles, display, margins, padding.',
        'tags' => 'html,code,raw,custom,script,style,embed',
    ],

    // =========================================================================
    // IMAGE COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'image',
        'title' => 'PageBuilder Image - Full Width Appearance (Default)',
        'content' => 'The image component uses a semantic <figure> element. HTML output: <figure data-content-type="image" data-appearance="full-width" data-element="main" style="text-align: center; margin: 0px; padding: 0px;"><a href="https://example.com" target="_blank" data-link-type="default" title="Link Title"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/image.jpg}}" alt="Alt text" title="Image title" style="border-style: none; border-width: 1px; border-radius: 0px; max-width: 100%; height: auto;" /><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/mobile-image.jpg}}" alt="Alt text" title="Image title" style="border-style: none; border-width: 1px; border-radius: 0px; max-width: 100%; height: auto;" /></a><figcaption data-element="caption">Image caption text</figcaption></figure>. When no link is configured, the <a> wrapper is omitted and <img> tags render directly inside <figure>.',
        'tags' => 'image,figure,responsive,mobile,desktop,alt,caption,media',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'image',
        'title' => 'PageBuilder Image - Responsive Behavior',
        'content' => 'PageBuilder images support separate desktop and mobile images: 1) Desktop image: <img class="pagebuilder-mobile-hidden" src="..." /> - hidden on mobile via CSS class pagebuilder-mobile-hidden. 2) Mobile image: <img class="pagebuilder-mobile-only" src="..." /> - only shown on mobile via CSS class pagebuilder-mobile-only. 3) Both images have static styles: max-width: 100%; height: auto; for responsive scaling. 4) If no mobile image is specified, the desktop image is shown on all devices (mobile image element is conditionally rendered only when src exists). 5) The mobile breakpoint is defined in PageBuilder CSS. 6) Image border styles are applied to the img elements directly, not the figure wrapper.',
        'tags' => 'image,responsive,mobile,desktop,breakpoint,adaptive',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'image',
        'title' => 'PageBuilder Image - SEO Attributes',
        'content' => 'Image SEO attributes in PageBuilder: 1) alt attribute - required for accessibility and SEO, set on both desktop and mobile img elements. Always provide descriptive alt text. 2) title attribute - tooltip text, set on both img elements. 3) figcaption - semantic caption rendered as <figcaption> inside <figure>, only appears when caption text is provided. Search engines use figcaption for image context. 4) Link title - when image is wrapped in <a>, the link title attribute provides additional context. 5) data-link-type attribute on links indicates link type ("default", "product", "category", "page"). 6) Image src uses Magento media directives: {{media url=wysiwyg/path/to/image.jpg}} which resolves to the full media URL. 7) Example: <figure data-content-type="image" data-appearance="full-width"><img src="{{media url=wysiwyg/product-hero.jpg}}" alt="Premium leather handbag in brown" title="Leather Handbag Collection" style="max-width:100%;height:auto;" /><figcaption>Our handcrafted leather collection</figcaption></figure>.',
        'tags' => 'image,seo,alt,title,figcaption,accessibility,descriptive',
    ],

    // =========================================================================
    // VIDEO COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'video',
        'title' => 'PageBuilder Video - HTML Output Structure',
        'content' => 'The video component supports both hosted (YouTube/Vimeo) and direct video URLs. HTML output: <div data-content-type="video" data-appearance="default" data-element="main" style="text-align: center; margin: 0px;"><div class="pagebuilder-video-inner" data-element="inner" style="max-width: 960px;"><div class="pagebuilder-video-wrapper" data-element="wrapper" style="border-style: solid; border-color: #000; border-width: 1px; border-radius: 0px; padding: 0px;"><div class="pagebuilder-video-container"><iframe frameborder="0" allowfullscreen src="https://www.youtube.com/embed/VIDEO_ID"></iframe></div></div></div></div>. For non-hosted (direct) video URLs, a <video> element replaces the <iframe>: <video frameborder="0" controls src="video.mp4"></video>. The component conditionally renders iframe for hosted platforms (YouTube, Vimeo) and video tag for direct files. Video attributes: src, autoplay, muted, playsinline.',
        'tags' => 'video,iframe,youtube,vimeo,embed,media,player',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'video',
        'title' => 'PageBuilder Video - SEO Implications',
        'content' => 'Video SEO in PageBuilder: 1) Hosted videos (YouTube/Vimeo) render as iframes - search engines can follow the iframe src to discover video content. 2) Direct video files use the <video> tag with controls attribute for accessibility. 3) The pagebuilder-video-container class provides responsive aspect ratio (typically 16:9 via CSS). 4) max-width on the inner element controls maximum video display size. 5) For SEO, complement videos with text content describing the video - PageBuilder videos lack built-in schema markup. 6) Consider adding structured data (VideoObject schema) via the HTML Code component alongside video components. 7) Video autoplay with muted attribute is used for background-style videos.',
        'tags' => 'video,seo,iframe,accessibility,schema,structured-data',
    ],

    // =========================================================================
    // BANNER COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'banner',
        'title' => 'PageBuilder Banner - Poster Appearance (Default)',
        'content' => 'Banner with "poster" appearance (default) centers overlay content over the full banner area. HTML output with link: <div data-content-type="banner" data-appearance="poster" data-show-button="always" data-show-overlay="always" data-element="main" style="margin: 0px;"><a href="https://example.com" target="_blank" data-link-type="default" title="Banner Link"><div class="pagebuilder-banner-wrapper" data-background-images="{\"desktop_image\":\"{{media url=wysiwyg/banner.jpg}}\"}" data-background-type="image" data-element="wrapper" style="background-position: center center; background-size: cover; background-repeat: no-repeat; border-style: none; border-width: 1px; border-radius: 0px; text-align: center;"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="rgba(0,0,0,0.5)" aria-label="Banner alt" title="Banner title" data-element="overlay" style="min-height: 300px; padding: 40px; background-color: rgba(0,0,0,0.5); border-radius: 0px;"><div class="pagebuilder-poster-content"><div data-element="content"><p>Banner message HTML</p></div><button type="button" class="pagebuilder-banner-button pagebuilder-button-primary" data-element="button" style="">Shop Now</button></div></div></div></a></div>. Without link, the <a> is replaced by a plain <div>.',
        'tags' => 'banner,poster,overlay,hero,promotion,cta,button',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'banner',
        'title' => 'PageBuilder Banner - Collage Left Appearance',
        'content' => 'Banner with "collage-left" appearance positions the content overlay on the left side. HTML structure is identical to poster except: 1) data-appearance="collage-left" on main div. 2) Overlay class is "pagebuilder-overlay" (without "pagebuilder-poster-overlay"). 3) Content wrapper class is "pagebuilder-collage-content" instead of "pagebuilder-poster-content". 4) Padding/min-height are on the wrapper element, not the overlay (in collage modes). HTML: <div data-content-type="banner" data-appearance="collage-left" ...><a href="..."><div class="pagebuilder-banner-wrapper" style="background-image: url(...); min-height: 300px; padding: 40px; ..."><div class="pagebuilder-overlay" data-overlay-color="..." style="background-color: rgba(0,0,0,0.5);"><div class="pagebuilder-collage-content"><div data-element="content">Message</div><button class="pagebuilder-banner-button pagebuilder-button-primary">Button Text</button></div></div></div></a></div>.',
        'tags' => 'banner,collage-left,overlay,promotion,layout',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'banner',
        'title' => 'PageBuilder Banner - Collage Centered and Collage Right',
        'content' => 'Collage-centered and collage-right are structurally identical to collage-left, differing only in the data-appearance value and CSS positioning. Collage-centered: data-appearance="collage-centered" - content overlay is centered horizontally. Collage-right: data-appearance="collage-right" - content overlay is positioned on the right side. All three collage variants use: class "pagebuilder-collage-content" for the content wrapper, class "pagebuilder-overlay" (without poster-overlay), and the same element structure. The CSS for each appearance handles the actual positioning of the overlay content within the banner.',
        'tags' => 'banner,collage-centered,collage-right,appearance,variants',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'banner',
        'title' => 'PageBuilder Banner - Button Types and Show/Hide',
        'content' => 'Banner buttons have configurable visibility and styling: 1) data-show-button attribute: "always" (always visible), "hover" (visible on hover), "never" (hidden). 2) data-show-overlay attribute: "always" (always visible), "hover" (visible on hover), "never" (hidden). 3) Button CSS classes follow Magento button types: pagebuilder-button-primary, pagebuilder-button-secondary, pagebuilder-button-link. The class "pagebuilder-banner-button" is always present on banner buttons. 4) Button visibility uses inline opacity and visibility styles: visible = opacity:1;visibility:visible, hidden = opacity:0;visibility:hidden. 5) Button text is HTML-escaped using tag-escaper converter. Example button: <button type="button" class="pagebuilder-banner-button pagebuilder-button-primary" style="opacity: 1; visibility: visible;">Shop Now</button>.',
        'tags' => 'banner,button,primary,secondary,link,hover,visibility,cta',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'banner',
        'title' => 'PageBuilder Banner - SEO and Accessibility',
        'content' => 'Banner SEO and accessibility features: 1) aria-label attribute on overlay element provides screen reader description. 2) title attribute on overlay and link elements. 3) Link target="_blank" for external links, no target for internal. 4) data-link-type indicates link destination: "default" (URL), "product", "category", "page". 5) Background images are set via data-background-images JSON attribute and CSS background-image, which are NOT directly crawlable by search engines. SEO recommendation: Always include descriptive text content in the banner message div to ensure search engines can read the banner content. 6) Button text should be descriptive (e.g., "Shop Winter Collection" not just "Click Here"). 7) The overlay color should ensure sufficient contrast ratio with text for accessibility (WCAG 2.1 AA requires 4.5:1 for normal text).',
        'tags' => 'banner,seo,accessibility,aria,contrast,alt,link',
    ],

    // =========================================================================
    // SLIDER COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'slider',
        'title' => 'PageBuilder Slider - HTML Output Structure',
        'content' => 'The slider is a container for slide content types, rendered as a carousel. HTML output: <div class="pagebuilder-slider" data-content-type="slider" data-appearance="default" data-autoplay="false" data-autoplay-speed="4000" data-fade="false" data-infinite-loop="false" data-show-arrows="false" data-show-dots="true" data-element="main" style="min-height: 300px; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px 0px 10px; padding: 0px;">SLIDE CHILDREN HERE</div>. Key attributes: data-autoplay="true|false", data-autoplay-speed="4000" (milliseconds), data-fade="true|false" (fade vs slide transition), data-infinite-loop="true|false", data-show-arrows="true|false", data-show-dots="true|false". The slider uses Slick carousel library on the frontend. Style properties: text_align, min_height, border styles, display, margins, padding. Only accepts slide children.',
        'tags' => 'slider,carousel,slick,autoplay,dots,arrows,transition',
    ],

    // =========================================================================
    // SLIDE COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'slide',
        'title' => 'PageBuilder Slide - Poster Appearance (Default)',
        'content' => 'Slide with "poster" appearance. HTML output: <div data-content-type="slide" data-slide-name="Slide Name" data-appearance="poster" data-show-button="always" data-show-overlay="always" data-element="main" style="min-height: 300px; margin: 0px;"><a href="https://example.com" target="_blank" data-link-type="default" title="Slide Link"><div class="pagebuilder-slide-wrapper" data-background-images="{}" data-background-type="image" data-element="wrapper" style="background-position: center center; background-size: cover; background-repeat: no-repeat; border-style: none; min-height: 300px; padding: 40px;"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="rgba(0,0,0,0.5)" data-element="overlay" style="background-color: rgba(0,0,0,0.5);"><div class="pagebuilder-poster-content"><div data-element="content">Slide content</div><button type="button" class="pagebuilder-slide-button pagebuilder-button-primary">Button Text</button></div></div></div></a></div>. Note: slide buttons use class "pagebuilder-slide-button" (not banner-button). Slides can only exist inside a slider parent.',
        'tags' => 'slide,poster,carousel,overlay,content,button',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'slide',
        'title' => 'PageBuilder Slide - Collage Appearances',
        'content' => 'Slides support the same collage variants as banners: collage-left, collage-centered, collage-right. The HTML structure mirrors banners but with slide-specific classes: 1) Main wrapper: class="pagebuilder-slide-wrapper" (instead of pagebuilder-banner-wrapper). 2) Content div: class="pagebuilder-collage-content" (same as banner collage). 3) Overlay div: class="pagebuilder-overlay" (without poster-overlay for collage). 4) Button: class="pagebuilder-slide-button" (instead of pagebuilder-banner-button). 5) data-slide-name attribute stores the slide name for identification. 6) Slide-specific attribute: data-slide-name="Slide Title". The slide component supports video backgrounds with the same data-video-* attributes as banners and rows.',
        'tags' => 'slide,collage-left,collage-centered,collage-right,variants',
    ],

    // =========================================================================
    // BUTTONS COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'buttons',
        'title' => 'PageBuilder Buttons Container - Inline Appearance (Default)',
        'content' => 'The Buttons container holds button-item children in an inline (horizontal) layout. HTML output: <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" style="text-align: center; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 0px;">BUTTON-ITEM CHILDREN</div>. The "inline" appearance displays buttons side by side horizontally. data-same-width="true|false" controls whether all buttons have equal width. Only accepts button-item children. Style properties: text_align, border styles, display, margins, padding.',
        'tags' => 'buttons,container,inline,horizontal,group',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'buttons',
        'title' => 'PageBuilder Buttons Container - Stacked Appearance',
        'content' => 'Buttons with "stacked" appearance displays buttons vertically. HTML output: <div data-content-type="buttons" data-appearance="stacked" data-same-width="false" data-element="main" style="display: flex; flex-direction: column; text-align: center; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 0px;">BUTTON-ITEM CHILDREN</div>. The stacked appearance adds static styles: display:flex and flex-direction:column, creating a vertical stack. Otherwise identical attributes to inline. The display property uses a different converter for stacked (flex-based) vs inline (boolean show/hide).',
        'tags' => 'buttons,container,stacked,vertical,group,flex',
    ],

    // =========================================================================
    // BUTTON ITEM COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'button-item',
        'title' => 'PageBuilder Button Item - HTML Output Structure',
        'content' => 'Individual button within a buttons container. HTML output with link: <div data-content-type="button-item" data-appearance="default" data-element="main"><a class="pagebuilder-button-primary" href="https://example.com" target="_blank" data-link-type="default" data-element="link" style="text-align: center; border-style: solid; border-color: #000; border-width: 2px; border-radius: 4px; margin: 0px; padding: 10px 20px;"><span data-element="link_text">Button Text</span></a></div>. Without link: <div data-content-type="button-item" data-appearance="default" data-element="main"><div class="pagebuilder-button-primary" data-element="empty_link" style="text-align: center; border-style: solid; ..."><span data-element="link_text">Button Text</span></div></div>. Button type classes: pagebuilder-button-primary, pagebuilder-button-secondary, pagebuilder-button-link. The button text is inside a <span> with HTML-escaped content. Styles are applied to the link/empty_link element, not the outer wrapper.',
        'tags' => 'button-item,link,cta,primary,secondary,action',
    ],

    // =========================================================================
    // TABS COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'tabs',
        'title' => 'PageBuilder Tabs - HTML Output Structure',
        'content' => 'Tabs component creates a tabbed interface. HTML output: <div data-content-type="tabs" data-appearance="default" data-active-tab="0" data-element="main" style="margin: 0px; padding: 0px;"><ul role="tablist" class="tabs-navigation" data-element="navigation" style="text-align: left;">FOR EACH TAB: <li role="tab" class="tab-header" data-element="headers"><a href="#TAB_ID" class="tab-title"><span class="tab-title">Tab Name</span></a></li></ul><div class="tabs-content" data-element="content" style="border-style: solid; border-color: #ccc; border-width: 1px; border-radius: 0px; min-height: 200px;">TAB-ITEM CHILDREN HERE</div></div>. Key features: role="tablist" on <ul> for ARIA accessibility, role="tab" on each <li>, data-active-tab="0" (zero-indexed) specifies default active tab. Tab alignment classes: tab-align-left, tab-align-center, tab-align-right (applied to main div via CSS classes). The tabs-navigation ul renders tab headers from child tab-items. The tabs-content div contains the actual tab-item content panels.',
        'tags' => 'tabs,tablist,navigation,aria,accessibility,tabbed',
    ],

    // =========================================================================
    // TAB ITEM COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'tab-item',
        'title' => 'PageBuilder Tab Item - HTML Output Structure',
        'content' => 'Individual tab panel within a tabs container. HTML output: <div data-content-type="tab-item" data-appearance="default" data-tab-name="Tab Name" data-background-images="{}" id="UNIQUE_TAB_ID" data-element="main" style="justify-content: flex-start; display: flex; flex-direction: column; background-position: left top; background-size: cover; background-repeat: no-repeat; background-attachment: scroll; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 10px;">TAB CONTENT CHILDREN</div>. The tab-item has a unique id attribute used for tab navigation linking. data-tab-name stores the tab title displayed in the tab header. Tab items accept all content types EXCEPT rows and tabs (no nested tabs). Style properties include background image/color, text alignment, borders, justify_content, min_height, margins, padding. The container element provides flex column layout.',
        'tags' => 'tab-item,panel,content,tabbed,id',
    ],

    // =========================================================================
    // DIVIDER COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'divider',
        'title' => 'PageBuilder Divider - HTML Output Structure',
        'content' => 'The divider renders a horizontal rule. HTML output: <div data-content-type="divider" data-appearance="default" data-element="main" style="text-align: center; border-style: none; border-width: 1px; border-radius: 0px; margin: 10px 0px; padding: 10px;"><hr data-element="line" style="width: 100%; border-width: 1px; border-color: #ccc; display: inline-block;" /></div>. The outer div is the main element with standard style properties. The <hr> element inside has configurable: width (line_width, e.g., "100%", "50%"), border-width (line_thickness), border-color (line_color), and a static display:inline-block. The divider is useful for visual separation between content sections. It renders as semantic HTML <hr> which search engines interpret as a thematic break.',
        'tags' => 'divider,hr,separator,line,horizontal-rule,thematic-break',
    ],

    // =========================================================================
    // BLOCK (CMS BLOCK) COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'block',
        'title' => 'PageBuilder Block - HTML Output Structure',
        'content' => 'The Block component embeds a CMS static block via widget directive. HTML output: <div data-content-type="block" data-appearance="default" data-element="main" style="text-align: left; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 0px;">{{widget type="Magento\\Cms\\Block\\Widget\\Block" template="widget/static_block/default.phtml" block_id="BLOCK_ID" type_name="CMS Static Block"}}</div>. The inner content is a Magento widget directive that gets rendered server-side. The widget directive is converted by the block/mass-converter/widget-directive converter. The rendered output replaces the directive with actual block HTML. Parents: root-container, row, column, tab-item. Block content is fully rendered HTML on the frontend, making it SEO-friendly.',
        'tags' => 'block,cms,static-block,widget,directive,embedded',
    ],

    // =========================================================================
    // PRODUCTS COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'products',
        'title' => 'PageBuilder Products - Grid Appearance (Default)',
        'content' => 'Products component in grid layout. HTML output: <div data-content-type="products" data-appearance="grid" data-element="main" style="text-align: center; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 0px;">{{widget type="Magento\\CatalogWidget\\Block\\Product\\ProductsList" template="Magento_CatalogWidget::product/widget/content/grid.phtml" anchor_text="" id_path="" show_pager="0" products_count="5" condition_option="category_ids" condition_option_value="3" type_name="Catalog Products List" conditions_encoded="..."}}</div>. The product listing is rendered server-side via the widget directive. The conditions_encoded attribute contains the product selection criteria (category, SKU, conditions). Grid appearance shows products in a responsive grid layout.',
        'tags' => 'products,grid,catalog,widget,listing,product-list',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'products',
        'title' => 'PageBuilder Products - Carousel Appearance',
        'content' => 'Products component in carousel layout. HTML output: <div data-content-type="products" data-appearance="carousel" data-autoplay="false" data-autoplay-speed="4000" data-infinite-loop="false" data-show-arrows="true" data-show-dots="true" data-carousel-mode="default" data-center-padding="90px" data-element="main" style="...">{{widget type="Magento\\CatalogWidget\\Block\\Product\\ProductsList" template="Magento_PageBuilder::catalog/product/widget/content/carousel.phtml" ...}}</div>. Additional carousel-specific data attributes: data-autoplay, data-autoplay-speed, data-infinite-loop, data-show-arrows, data-show-dots, data-carousel-mode="default|continuous", data-center-padding="90px" (static). The carousel template is PageBuilder-specific (not the standard CatalogWidget template). Products are rendered server-side and then initialized as a carousel on the frontend.',
        'tags' => 'products,carousel,slider,catalog,widget,slick',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'products',
        'title' => 'PageBuilder Products - SEO Implications',
        'content' => 'Product widget SEO considerations: 1) Product listings are rendered server-side via widget directives, making all product names, prices, and links fully crawlable. 2) Product images within the widget include alt text from the product catalog. 3) Product links are standard <a> tags to product pages, passing link equity. 4) The products_count attribute limits how many products render (default 5). 5) For SEO, product grids on CMS pages create internal links to product pages, boosting their crawl priority. 6) Conditions can be based on: category_ids, sku, new products, sale products. 7) The pager (show_pager="0|1") controls pagination - disabled by default in PageBuilder. 8) Schema markup for product listings should be added separately if needed.',
        'tags' => 'products,seo,internal-links,crawlable,catalog,widget',
    ],

    // =========================================================================
    // MAP COMPONENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'map',
        'title' => 'PageBuilder Map - HTML Output Structure',
        'content' => 'The map component renders a Google Maps embed. HTML output: <div data-content-type="map" data-appearance="default" data-show-controls="true" data-locations="[{\"location_name\":\"Store\",\"position\":{\"latitude\":40.7128,\"longitude\":-74.0060},\"comment\":\"Visit us\",\"phone\":\"555-0100\",\"address\":\"123 Main St\",\"city\":\"New York\",\"state\":\"NY\",\"zipcode\":\"10001\",\"country\":\"US\"}]" data-element="main" style="text-align: center; border-style: none; border-width: 1px; border-radius: 0px; height: 300px; margin: 0px; padding: 0px;"></div>. The map is an empty div that gets initialized by JavaScript using Google Maps API. data-locations contains a JSON-encoded array of location objects. data-show-controls="true|false" toggles map controls. The height style sets the map container height. SEO note: map content is JavaScript-rendered and not crawlable - include address information in text content alongside the map.',
        'tags' => 'map,google-maps,location,address,embed,javascript',
    ],

    // =========================================================================
    // LAYOUT PATTERNS
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'layout-pattern',
        'title' => 'PageBuilder Two-Column Layout Pattern',
        'content' => 'Standard two-column layout in PageBuilder HTML: <div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-element="inner" style="justify-content: flex-start; display: flex; flex-direction: column; background-position: left top; background-size: cover; background-repeat: no-repeat; background-attachment: scroll; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 10px;"><div class="pagebuilder-column-group" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" style="display: flex; width: 100%;"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" style="display: flex; width: 100%;"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-element="main" style="justify-content: flex-start; display: flex; flex-direction: column; align-self: stretch; width: 50%; margin: 0px; padding: 10px;">LEFT COLUMN CONTENT</div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-element="main" style="justify-content: flex-start; display: flex; flex-direction: column; align-self: stretch; width: 50%; margin: 0px; padding: 10px;">RIGHT COLUMN CONTENT</div></div></div></div></div>. The nesting order is: row > column-group > column-line > column + column. Both columns have width:50%.',
        'tags' => 'layout,two-column,50-50,grid,pattern,columns,responsive',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'layout-pattern',
        'title' => 'PageBuilder Three-Column Layout Pattern',
        'content' => 'Three-column layout uses three column elements with width:33.3333% each: row > column-group > column-line > column(33.3%) + column(33.3%) + column(33.3%). Asymmetric three-column example (sidebar-content-sidebar): column widths 25% + 50% + 25%. The column-line with display:flex handles the horizontal layout. Each column has style="width: 33.3333%;" (or appropriate percentages). Common three-column variations: 1) Equal thirds: 33.3333% + 33.3333% + 33.3333%. 2) Content-heavy center: 25% + 50% + 25%. 3) Wide left: 50% + 25% + 25%. Column widths must sum to 100% within a column-line.',
        'tags' => 'layout,three-column,grid,pattern,sidebar,responsive',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'layout-pattern',
        'title' => 'PageBuilder Hero Section Pattern',
        'content' => 'A hero section combines a full-width row with a banner or large image. Pattern: <div data-content-type="row" data-appearance="full-width" data-element="main" style="justify-content: flex-start; display: flex; flex-direction: column; background-position: center center; background-size: cover; background-repeat: no-repeat; margin: 0px; padding: 0px;"><div class="row-full-width-inner" data-element="inner"><div data-content-type="banner" data-appearance="poster" data-show-button="always" data-show-overlay="always" style="margin: 0px;"><a href="/shop"><div class="pagebuilder-banner-wrapper" style="background-image: url(hero.jpg); background-position: center center; background-size: cover; min-height: 500px; text-align: center;"><div class="pagebuilder-overlay pagebuilder-poster-overlay" style="padding: 100px 40px; background-color: rgba(0,0,0,0.3);"><div class="pagebuilder-poster-content"><div data-element="content"><h2 style="color: #fff; font-size: 48px;">Welcome to Our Store</h2><p style="color: #fff;">Discover our new collection</p></div><button class="pagebuilder-banner-button pagebuilder-button-primary">Shop Now</button></div></div></div></a></div></div></div>. This creates a full-width hero with background image, overlay, heading, text, and CTA button.',
        'tags' => 'layout,hero,banner,full-width,pattern,landing-page,cta',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'layout-pattern',
        'title' => 'PageBuilder Full Product Landing Page Structure',
        'content' => 'Complete product landing page structure using PageBuilder: 1) HERO ROW (full-width): Banner with poster appearance, background image, h1 heading, CTA button. 2) FEATURES ROW (contained): Three-column layout, each column with an image and h3 heading and text describing a feature. 3) PRODUCTS ROW (contained): Products widget in grid appearance showing featured category products. 4) TESTIMONIAL ROW (contained): Two-column layout with text quotes. 5) CTA ROW (full-width): Banner with call-to-action and button. 6) FAQ ROW (contained): Tabs component with tab-items for FAQ categories. SEO structure: h1 in hero, h2 for each section (Features, Products, Testimonials), h3 for individual items. Each section is a separate row for clean content separation.',
        'tags' => 'layout,landing-page,product-page,full-page,structure,seo',
    ],

    // =========================================================================
    // RESPONSIVE BEHAVIOR
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'responsive',
        'title' => 'PageBuilder Mobile Responsive CSS Classes',
        'content' => 'PageBuilder uses specific CSS classes for responsive behavior: 1) pagebuilder-mobile-hidden - hides element on mobile (used for desktop-only images). 2) pagebuilder-mobile-only - shows element only on mobile (used for mobile-specific images). 3) The mobile breakpoint is typically 768px. 4) Column layouts stack vertically on mobile - the flex container changes from row to column direction. 5) Row appearances: contained rows respect max-width, full-width stretches background, full-bleed stretches everything. 6) Mobile-specific forms exist for: row (pagebuilder_row_mobile_form), column (pagebuilder_column_mobile_form), slider (pagebuilder_slider_mobile_form), tabs (pagebuilder_tabs_mobile_form), banner (pagebuilder_banner_mobile_form), slide (pagebuilder_slide_mobile_form), tab-item (pagebuilder_tab_item_mobile_form). These allow different settings per breakpoint.',
        'tags' => 'responsive,mobile,breakpoint,adaptive,css-classes,hidden',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'responsive',
        'title' => 'PageBuilder Column Mobile Stacking',
        'content' => 'On mobile devices, PageBuilder columns stack vertically. The column-line element (display:flex) has its flex-direction changed from row to column via CSS media queries. Each column goes from its percentage width to width:100% on mobile. The order of stacking follows the DOM order (left column first, right column second). To create mobile-friendly layouts: 1) Put the most important content in the leftmost column (it appears first on mobile). 2) Avoid more than 3-4 columns in a row as it creates many stacked sections on mobile. 3) Use min-height carefully as it applies to both desktop and mobile. 4) Consider mobile padding separately from desktop padding. 5) Background images may need different mobile versions - use data-background-images with mobile_image key.',
        'tags' => 'responsive,mobile,columns,stacking,flex,order',
    ],

    // =========================================================================
    // COMMON CSS CLASSES
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'css-classes',
        'title' => 'PageBuilder Core CSS Classes Reference',
        'content' => 'Essential PageBuilder CSS classes: LAYOUT: pagebuilder-column-group (column group wrapper), pagebuilder-column-line (column row flex container), pagebuilder-column (individual column). BANNER/SLIDE: pagebuilder-banner-wrapper (banner background container), pagebuilder-slide-wrapper (slide background container), pagebuilder-overlay (overlay div), pagebuilder-poster-overlay (poster-specific overlay), pagebuilder-poster-content (poster content wrapper), pagebuilder-collage-content (collage content wrapper). BUTTONS: pagebuilder-banner-button (banner CTA button), pagebuilder-slide-button (slide CTA button), pagebuilder-button-primary (primary style), pagebuilder-button-secondary (secondary style), pagebuilder-button-link (link style). MEDIA: pagebuilder-slider (slider container), pagebuilder-video-inner (video inner), pagebuilder-video-wrapper (video border wrapper), pagebuilder-video-container (video embed container). RESPONSIVE: pagebuilder-mobile-hidden (desktop only), pagebuilder-mobile-only (mobile only). OTHER: bypass-html-filter (prevents HTML sanitization on text/html components), row-full-width-inner (full-width row inner container), video-overlay (video overlay div), tabs-navigation (tab headers list), tabs-content (tab panels container), tab-header (individual tab header), tab-title (tab title text).',
        'tags' => 'css-classes,reference,styling,selectors,complete-list',
    ],

    // =========================================================================
    // DATA ATTRIBUTES
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'data-attributes',
        'title' => 'PageBuilder Universal Data Attributes',
        'content' => 'Every PageBuilder content type outputs these core data attributes: 1) data-content-type="TYPE" - identifies the component type (row, column, column-group, column-line, heading, text, html, image, video, banner, slider, slide, tabs, tab-item, buttons, button-item, block, products, divider, map). 2) data-appearance="VARIANT" - specifies the appearance variant. 3) data-element="ELEMENT_NAME" - identifies which element definition from XML is being rendered (main, inner, wrapper, overlay, content, button, link, etc.). Additional type-specific attributes: data-enable-parallax (row), data-parallax-speed (row), data-background-images (row/column/banner/slide/tab-item - JSON), data-background-type (row/banner/slide), data-show-button (banner/slide), data-show-overlay (banner/slide), data-grid-size (column-group/column-line), data-active-tab (tabs), data-tab-name (tab-item), data-slide-name (slide), data-same-width (buttons), data-autoplay/data-autoplay-speed/data-fade/data-infinite-loop/data-show-arrows/data-show-dots (slider/products carousel), data-link-type (links), data-overlay-color (banner/slide overlay), data-video-overlay-color (video overlay), data-show-controls (map), data-locations (map - JSON).',
        'tags' => 'data-attributes,content-type,appearance,element,reference',
    ],

    // =========================================================================
    // WIDGET EMBEDDING
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'widgets',
        'title' => 'PageBuilder Widget Directive Syntax',
        'content' => 'Widgets are embedded in PageBuilder HTML using Magento directive syntax: {{widget type="WIDGET_CLASS" PARAMETERS}}. Common widget directives in PageBuilder: 1) CMS Block: {{widget type="Magento\\Cms\\Block\\Widget\\Block" template="widget/static_block/default.phtml" block_id="BLOCK_IDENTIFIER" type_name="CMS Static Block"}}. 2) Product List: {{widget type="Magento\\CatalogWidget\\Block\\Product\\ProductsList" template="Magento_CatalogWidget::product/widget/content/grid.phtml" products_count="5" condition_option="category_ids" condition_option_value="3" conditions_encoded="..."}}. 3) Product Link: {{widget type="Magento\\Catalog\\Block\\Product\\Widget\\Link" template="product/widget/link/link_inline.phtml" id_path="product/123"}}. 4) CMS Page Link: {{widget type="Magento\\Cms\\Block\\Widget\\Page\\Link" template="widget/link/link_inline.phtml" page_id="5"}}. Widget directives are processed server-side and replaced with rendered HTML before output to the browser.',
        'tags' => 'widget,directive,embed,cms-block,product-list,dynamic',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'widgets',
        'title' => 'PageBuilder Media and Store Directives',
        'content' => 'Magento directives used within PageBuilder content: 1) {{media url="wysiwyg/path/to/image.jpg"}} - resolves to full media URL (e.g., https://store.com/media/wysiwyg/path/to/image.jpg). Used in image src attributes and background-image data-background-images JSON. 2) {{store url="path/to/page"}} - resolves to full store URL. Used in link href attributes. 3) {{config path="general/store_information/name"}} - outputs store configuration value. 4) {{customVar code="variable_code"}} - outputs custom variable value. 5) Background images JSON format: {"desktop_image":"{{media url=wysiwyg/banner.jpg}}","mobile_image":"{{media url=wysiwyg/banner-mobile.jpg}}"}. These directives are processed by Magento filter chain before HTML output.',
        'tags' => 'media,directive,store-url,config,variable,image-path',
    ],

    // =========================================================================
    // ACCESSIBILITY
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'accessibility',
        'title' => 'PageBuilder Accessibility Features',
        'content' => 'Built-in accessibility features in PageBuilder components: 1) TABS: role="tablist" on navigation <ul>, role="tab" on each <li> tab header. Tab content panels linked via id/href attributes. 2) BANNERS/SLIDES: aria-label attribute on overlay elements for screen reader description. title attribute on overlays and links. 3) IMAGES: alt attribute on all <img> elements (both desktop and mobile). title attribute for tooltips. <figcaption> for image captions within <figure>. 4) BUTTONS: Semantic <a> elements for linked buttons with href for keyboard navigation. <button type="button"> elements for banner/slide CTA buttons. 5) VIDEO: <iframe allowfullscreen> for hosted videos. <video controls> for direct videos. 6) HEADINGS: Semantic h1-h6 tags (not styled divs). 7) DIVIDER: Semantic <hr> element for thematic breaks. For improved accessibility: ensure sufficient color contrast in overlays, provide alt text for all images, maintain heading hierarchy, add aria attributes via the HTML Code component where needed.',
        'tags' => 'accessibility,aria,wcag,screen-reader,keyboard,semantic',
    ],

    // =========================================================================
    // STYLE PATTERNS
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'styles',
        'title' => 'PageBuilder Inline Style Application Pattern',
        'content' => 'PageBuilder applies all styles as inline styles on elements. The style property mapping from XML definitions: background_color -> background-color, background_image -> background-image: url(), background_position -> background-position (e.g., "left top", "center center"), background_size -> background-size (e.g., "cover", "contain"), background_repeat -> background-repeat, background_attachment -> background-attachment, text_align -> text-align, border_style -> border-style (converted via border-style converter), border_color -> border-color, border_width -> border-width (px), border_radius -> border-radius (px), min_height -> min-height (px), justify_content -> justify-content, display -> display, margins -> margin (shorthand "Tpx Rpx Bpx Lpx"), padding -> padding (shorthand "Tpx Rpx Bpx Lpx"), width -> width (% for columns, px for others). Static styles are always output regardless of user settings (e.g., display:flex on column-line, align-self:stretch on full-height column).',
        'tags' => 'styles,inline,css,property,mapping,converter',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'styles',
        'title' => 'PageBuilder Background Image Patterns',
        'content' => 'Background images in PageBuilder use two mechanisms: 1) CSS inline style: background-image is applied for preview/edit mode via the converter. 2) data-background-images attribute: JSON object containing image paths for frontend JavaScript initialization. Format: data-background-images=\'{"desktop_image":"{{media url=wysiwyg/image.jpg}}","mobile_image":"{{media url=wysiwyg/image-mobile.jpg}}"}\'. On the frontend, JavaScript reads data-background-images and applies the appropriate image based on viewport. Components supporting background images: row (on inner element for contained, main element for full-width/full-bleed), column (on main element), column-group (on main element), banner (on wrapper element), slide (on wrapper element), tab-item (on main element). Background properties always include: background-position, background-size, background-repeat, background-attachment.',
        'tags' => 'background,image,responsive,mobile,desktop,json',
    ],

    // =========================================================================
    // CONTENT TYPE HIERARCHY
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'hierarchy',
        'title' => 'PageBuilder Content Type Nesting Rules',
        'content' => 'PageBuilder enforces strict parent-child rules: ROOT-CONTAINER accepts: row, column-group, tabs, heading, text, html, image, video, banner, slider, buttons, divider, block, products, map. ROW accepts: all types EXCEPT row (no nested rows). COLUMN-GROUP accepts: column-line, column only. COLUMN-LINE accepts: column only. COLUMN accepts: all types EXCEPT row, column, column-line. TABS accepts: tab-item only. TAB-ITEM accepts: all types EXCEPT row, tabs. SLIDER accepts: slide only. BUTTONS accepts: button-item only. SLIDE, BANNER, HEADING, TEXT, HTML, IMAGE, VIDEO, DIVIDER, BLOCK, PRODUCTS, MAP: no children (leaf nodes). This hierarchy ensures valid HTML structure and prevents infinite nesting.',
        'tags' => 'hierarchy,nesting,parent,children,rules,structure,validation',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'hierarchy',
        'title' => 'PageBuilder Menu Sections and Component Categories',
        'content' => 'PageBuilder organizes content types into menu sections: LAYOUT section: Row (sortOrder:1), Columns/column-group (sortOrder:10), Column (sortOrder:15), Tabs (sortOrder:20). ELEMENTS section: Text (sortOrder:1), Heading (sortOrder:20), Buttons (sortOrder:30), Divider (sortOrder:40), HTML Code (sortOrder:70). MEDIA section: Image (sortOrder:1), Video (sortOrder:20), Banner (sortOrder:30), Slider (sortOrder:40), Map (sortOrder:50). ADD CONTENT section: Block (sortOrder:1), Products (sortOrder:20). System types not in menu: column-line, button-item (is_system=false), column (is_system=false). These appear automatically when their parent is created.',
        'tags' => 'menu,sections,categories,organization,sortorder',
    ],

    // =========================================================================
    // GENERATING PAGEBUILDER CONTENT
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'generation',
        'title' => 'Generating Valid PageBuilder HTML - Essential Rules',
        'content' => 'Rules for generating valid PageBuilder-compatible HTML: 1) Every element MUST have data-content-type attribute matching the component name. 2) Every element MUST have data-appearance attribute matching a valid appearance name. 3) Style properties are ALWAYS inline (never external classes for layout properties). 4) The nesting hierarchy must be respected (row cannot contain row, etc.). 5) Columns must be inside column-line, inside column-group. Column widths must sum to 100%. 6) Widget directives use double curly braces: {{widget ...}} and backslash-escaped class names: Magento\\Cms\\Block\\Widget\\Block. 7) Media URLs use: {{media url=wysiwyg/path.jpg}} (no quotes around the path). 8) Background images use JSON in data-background-images with escaped quotes. 9) The data-element attribute is optional for rendering but present in PageBuilder-generated HTML. 10) Boolean attributes use string values "true"/"false" or "0"/"1", not actual booleans.',
        'tags' => 'generation,rules,validation,html,syntax,ai-content',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'generation',
        'title' => 'Generating PageBuilder Row with Heading and Text',
        'content' => 'Minimal valid PageBuilder content with a row, heading, and text paragraph: <div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-element="inner" style="justify-content: flex-start; display: flex; flex-direction: column; background-position: left top; background-size: cover; background-repeat: no-repeat; background-attachment: scroll; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 10px;"><h2 data-content-type="heading" data-appearance="default" data-element="main" style="border-style: none; border-width: 1px; border-radius: 0px;">Section Title</h2><div data-content-type="text" data-appearance="default" data-element="main" style="border-style: none; border-width: 1px; border-radius: 0px; margin: 0px 0px 10px; padding: 0px;"><p>Paragraph content goes here with <strong>important keywords</strong> and useful information.</p></div></div></div>.',
        'tags' => 'generation,example,row,heading,text,minimal,template',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'generation',
        'title' => 'Generating PageBuilder Two-Column with Images',
        'content' => 'Two-column layout with images and text: <div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-element="inner" style="justify-content: flex-start; display: flex; flex-direction: column; background-position: left top; background-size: cover; background-repeat: no-repeat; background-attachment: scroll; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 10px;"><div class="pagebuilder-column-group" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" style="display: flex; width: 100%;"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" style="display: flex; width: 100%;"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-element="main" style="justify-content: flex-start; display: flex; flex-direction: column; align-self: stretch; width: 50%; padding: 10px;"><figure data-content-type="image" data-appearance="full-width" data-element="main" style="margin: 0px; padding: 0px;"><img src="{{media url=wysiwyg/feature1.jpg}}" alt="Feature description" title="Feature 1" style="max-width: 100%; height: auto;" class="pagebuilder-mobile-hidden" /></figure></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-element="main" style="justify-content: flex-start; display: flex; flex-direction: column; align-self: stretch; width: 50%; padding: 10px;"><h3 data-content-type="heading" data-appearance="default" data-element="main">Feature Title</h3><div data-content-type="text" data-appearance="default" data-element="main"><p>Feature description text.</p></div></div></div></div></div></div>.',
        'tags' => 'generation,example,two-column,image,text,template',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'generation',
        'title' => 'Generating PageBuilder Banner with CTA',
        'content' => 'Full-width banner with call-to-action: <div data-content-type="row" data-appearance="full-width" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-element="main" style="justify-content: flex-start; display: flex; flex-direction: column; margin: 0px; padding: 0px;"><div class="row-full-width-inner" data-element="inner"><div data-content-type="banner" data-appearance="poster" data-show-button="always" data-show-overlay="always" data-element="main" style="margin: 0px;"><a href="{{store url=sale}}" data-link-type="default" title="Shop Sale"><div class="pagebuilder-banner-wrapper" data-background-images="{&quot;desktop_image&quot;:&quot;{{media url=wysiwyg/sale-banner.jpg}}&quot;}" data-background-type="image" data-element="wrapper" style="background-position: center center; background-size: cover; background-repeat: no-repeat;"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="rgba(0,0,0,0.4)" data-element="overlay" style="min-height: 400px; padding: 60px 40px; background-color: rgba(0,0,0,0.4);"><div class="pagebuilder-poster-content"><div data-element="content"><h2 style="color: #ffffff;">Summer Sale - Up to 50% Off</h2><p style="color: #ffffff;">Limited time offer on selected items</p></div><button type="button" class="pagebuilder-banner-button pagebuilder-button-primary" data-element="button" style="opacity: 1; visibility: visible;">Shop the Sale</button></div></div></div></a></div></div></div>.',
        'tags' => 'generation,example,banner,cta,full-width,sale,promotion',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'generation',
        'title' => 'Generating PageBuilder Product Section with Heading',
        'content' => 'Featured products section with heading: <div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-element="inner" style="justify-content: flex-start; display: flex; flex-direction: column; background-position: left top; background-size: cover; background-repeat: no-repeat; background-attachment: scroll; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 20px 10px;"><h2 data-content-type="heading" data-appearance="default" data-element="main" style="text-align: center; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px 0px 20px;">Featured Products</h2><div data-content-type="products" data-appearance="grid" data-element="main" style="text-align: center; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 0px;">{{widget type="Magento\\CatalogWidget\\Block\\Product\\ProductsList" template="Magento_CatalogWidget::product/widget/content/grid.phtml" anchor_text="" id_path="" show_pager="0" products_count="8" condition_option="category_ids" condition_option_value="CATEGORY_ID" type_name="Catalog Products List"}}</div></div></div>. Replace CATEGORY_ID with the actual category ID.',
        'tags' => 'generation,example,products,featured,grid,widget,heading',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'generation',
        'title' => 'Generating PageBuilder Tabs with Content',
        'content' => 'Tabs component with multiple tab panels: <div data-content-type="tabs" data-appearance="default" data-active-tab="0" data-element="main" style="margin: 0px; padding: 0px;"><ul role="tablist" class="tabs-navigation" data-element="navigation" style="text-align: left;"><li role="tab" class="tab-header" data-element="headers"><a href="#tab1" class="tab-title"><span class="tab-title">Description</span></a></li><li role="tab" class="tab-header" data-element="headers"><a href="#tab2" class="tab-title"><span class="tab-title">Specifications</span></a></li></ul><div class="tabs-content" data-element="content" style="border-style: solid; border-color: #cccccc; border-width: 1px; min-height: 200px;"><div data-content-type="tab-item" data-appearance="default" data-tab-name="Description" id="tab1" data-element="main" style="justify-content: flex-start; display: flex; flex-direction: column; background-position: left top; background-size: cover; background-repeat: no-repeat; background-attachment: scroll; padding: 15px;"><div data-content-type="text" data-appearance="default" data-element="main"><p>Product description content here.</p></div></div><div data-content-type="tab-item" data-appearance="default" data-tab-name="Specifications" id="tab2" data-element="main" style="justify-content: flex-start; display: flex; flex-direction: column; background-position: left top; background-size: cover; background-repeat: no-repeat; background-attachment: scroll; padding: 15px;"><div data-content-type="text" data-appearance="default" data-element="main"><p>Product specifications here.</p></div></div></div></div>.',
        'tags' => 'generation,example,tabs,tab-item,description,specifications',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'generation',
        'title' => 'Generating PageBuilder Buttons Group',
        'content' => 'Inline buttons group with two CTA buttons: <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" style="text-align: center; border-style: none; border-width: 1px; border-radius: 0px; margin: 20px 0px; padding: 0px;"><div data-content-type="button-item" data-appearance="default" data-element="main"><a class="pagebuilder-button-primary" href="{{store url=shop}}" data-link-type="default" data-element="link" style="text-align: center; border-style: solid; border-color: transparent; border-width: 2px; border-radius: 4px; margin: 0px 5px; padding: 10px 30px;"><span data-element="link_text">Shop Now</span></a></div><div data-content-type="button-item" data-appearance="default" data-element="main"><a class="pagebuilder-button-secondary" href="{{store url=about}}" data-link-type="default" data-element="link" style="text-align: center; border-style: solid; border-color: #333; border-width: 2px; border-radius: 4px; margin: 0px 5px; padding: 10px 30px;"><span data-element="link_text">Learn More</span></a></div></div>.',
        'tags' => 'generation,example,buttons,cta,primary,secondary,inline',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'generation',
        'title' => 'Generating PageBuilder Slider with Slides',
        'content' => 'Slider with multiple slides: <div class="pagebuilder-slider" data-content-type="slider" data-appearance="default" data-autoplay="true" data-autoplay-speed="5000" data-fade="false" data-infinite-loop="true" data-show-arrows="true" data-show-dots="true" data-element="main" style="min-height: 400px; border-style: none; border-width: 1px; border-radius: 0px; margin: 0px; padding: 0px;"><div data-content-type="slide" data-slide-name="Slide 1" data-appearance="poster" data-show-button="always" data-show-overlay="always" data-element="main" style="min-height: 400px; margin: 0px;"><a href="{{store url=new-arrivals}}" data-link-type="default"><div class="pagebuilder-slide-wrapper" data-background-images="{&quot;desktop_image&quot;:&quot;{{media url=wysiwyg/slide1.jpg}}&quot;}" data-background-type="image" data-element="wrapper" style="background-position: center center; background-size: cover; min-height: 400px; padding: 60px;"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="rgba(0,0,0,0.3)" style="background-color: rgba(0,0,0,0.3);"><div class="pagebuilder-poster-content"><div data-element="content"><h2 style="color:#fff;">New Arrivals</h2></div><button type="button" class="pagebuilder-slide-button pagebuilder-button-primary" style="opacity:1;visibility:visible;">Explore</button></div></div></div></a></div></div>. Add additional slide divs inside the slider for more slides.',
        'tags' => 'generation,example,slider,slide,carousel,autoplay',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'generation',
        'title' => 'Generating PageBuilder Divider Between Sections',
        'content' => 'Divider to separate content sections: <div data-content-type="divider" data-appearance="default" data-element="main" style="text-align: center; border-style: none; border-width: 1px; border-radius: 0px; margin: 20px 0px; padding: 10px;"><hr data-element="line" style="width: 50%; border-width: 2px; border-color: #cccccc; display: inline-block;" /></div>. Customize by changing: width (50% for centered short line, 100% for full width), border-width for thickness, border-color for line color, margin for spacing above/below.',
        'tags' => 'generation,example,divider,separator,hr,spacing',
    ],

    // =========================================================================
    // SEO COMPREHENSIVE GUIDE
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'seo',
        'title' => 'PageBuilder SEO - Complete Optimization Guide',
        'content' => 'Comprehensive SEO guide for PageBuilder content: HEADING HIERARCHY: Use exactly one h1 per page (usually the page title, not in PageBuilder). Use h2 for main sections, h3 for subsections. The heading component generates semantic h1-h6 tags. IMAGES: Always set alt text describing the image content. Use figcaption for captions. Desktop and mobile images share alt text. Image src uses {{media url=...}} which resolves to crawlable URLs. LINKS: Use descriptive anchor text in buttons and text links. data-link-type helps categorize links. Internal links ({{store url=...}}) pass link equity. CONTENT: Text and HTML components output crawlable content. Widget directives render server-side (SEO-friendly). Banner/slide message content is crawlable text inside the overlay div. STRUCTURE: Use rows to create logical page sections. Contained rows provide standard layout width. AVOID: Background images as primary content (not crawlable as img). JavaScript-only content (maps). Empty headings or decorative headings. SCHEMA: Add structured data via HTML Code component for rich snippets.',
        'tags' => 'seo,optimization,guide,headings,images,links,content,schema',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'seo',
        'title' => 'PageBuilder SEO - Content Crawlability Matrix',
        'content' => 'What search engines can and cannot crawl in PageBuilder: CRAWLABLE: Heading text (h1-h6 innerHTML), Text component content (div innerHTML with paragraphs, lists, etc.), HTML Code content (raw HTML), Image alt/title/figcaption, Banner/slide message content and button text, Link href attributes (resolved from directives), Widget-rendered content (products, blocks - server-side rendered), Tab content (all tabs rendered in HTML, only visibility toggled via JS). PARTIALLY CRAWLABLE: Background images (in data-background-images JSON attribute, not as <img> tags - Google may extract URLs but does not index them as images). NOT CRAWLABLE: Map content (JavaScript-rendered Google Maps), Parallax effects, CSS animations, Video content inside iframes (search engines follow iframe src separately), Dynamic carousel state (all slides are in DOM though). RECOMMENDATION: For every visual element (banner, slider), include equivalent text content for SEO. Do not rely solely on background images to convey information.',
        'tags' => 'seo,crawlability,indexing,google,search-engine,content',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'seo',
        'title' => 'PageBuilder SEO - Structured Data with HTML Code',
        'content' => 'Adding structured data to PageBuilder pages using the HTML Code component: Place an HTML Code block containing JSON-LD schema markup. Example for a product landing page: <div data-content-type="html" data-appearance="default" data-element="main" style="display:none;"><script type="application/ld+json">{"@context":"https://schema.org","@type":"WebPage","name":"Page Title","description":"Page description for SEO","mainEntity":{"@type":"ItemList","itemListElement":[{"@type":"ListItem","position":1,"name":"Product Name","url":"https://store.com/product"}]}}</script></div>. For FAQ sections using tabs: add FAQPage schema via HTML Code. For video content: add VideoObject schema alongside the video component. The display:none style hides the container visually while search engines still read the script tag. This approach works because the HTML Code component outputs content unfiltered (bypass-html-filter class).',
        'tags' => 'seo,structured-data,schema,json-ld,rich-snippets,faq',
    ],

    // =========================================================================
    // VIDEO BACKGROUND PATTERNS
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'video-background',
        'title' => 'PageBuilder Video Background on Rows and Banners',
        'content' => 'Rows, banners, and slides support video backgrounds via data attributes: data-background-type="video" (switches from image to video mode), data-video-src="https://www.youtube.com/embed/VIDEO_ID" (video URL), data-video-loop="true|false", data-video-play-only-visible="true|false", data-video-lazy-load="true|false", data-video-fallback-src="{{media url=wysiwyg/fallback.jpg}}" (image shown before video loads or on mobile). The video overlay element provides a color tint: <div class="video-overlay" data-video-overlay-color="rgba(0,0,0,0.5)" style="background-color: rgba(0,0,0,0.5);"></div>. Video backgrounds are initialized by JavaScript on the frontend. For SEO, always provide text content alongside video backgrounds since the video itself is not crawlable as text content.',
        'tags' => 'video,background,row,banner,slide,overlay,fallback',
    ],

    // =========================================================================
    // COMPLETE PAGE EXAMPLES
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'complete-page',
        'title' => 'PageBuilder Complete CMS Homepage Structure',
        'content' => 'A well-structured CMS homepage in PageBuilder follows this pattern: ROW 1 (full-width, hero): Banner poster with h1 heading "Welcome to Store Name", subtext, and primary CTA button. Background image with overlay. ROW 2 (contained, features): h2 "Why Shop With Us", three-column layout with icon images, h3 headings, and descriptive text for each feature (Free Shipping, Easy Returns, 24/7 Support). ROW 3 (contained, products): h2 "Featured Products", Products widget in grid showing 8 products from featured category. ROW 4 (full-width, promotion): Banner collage-left with promotional image, h2 "Special Offer", descriptive text, and secondary CTA button. ROW 5 (contained, content): Two-column layout - left column with image, right column with h2 "About Our Brand" and text paragraphs. ROW 6 (contained, newsletter/CTA): Centered text with h2 "Stay Connected", paragraph text, and centered buttons group with CTA. This structure provides: proper heading hierarchy (h1 > h2 > h3), crawlable text content, internal links via product widget and buttons, responsive layout, and semantic HTML.',
        'tags' => 'complete-page,homepage,cms,structure,seo,example,layout',
    ],

    [
        'category' => 'pagebuilder',
        'subcategory' => 'complete-page',
        'title' => 'PageBuilder Category Landing Page Structure',
        'content' => 'Category landing page optimized for SEO: ROW 1 (full-width): Banner with category hero image, h1 "Category Name" (if not auto-generated by Magento), brief category description. ROW 2 (contained): h2 "Popular in Category", Products carousel appearance showing bestsellers. ROW 3 (contained): Two-column layout - left column (66.6%) with h2 "Category Description" and rich text content with keywords, right column (33.3%) with image. ROW 4 (contained): Tabs component - Tab 1: "Buying Guide" with detailed text, Tab 2: "Size Chart" with HTML table, Tab 3: "FAQ" with questions and answers. ROW 5 (contained): h2 "Related Categories", Buttons inline with links to subcategories. ROW 6 (contained): Divider, then CMS Block widget for cross-category promotions. This approach maximizes keyword-rich content around product listings while maintaining good UX with organized tab content.',
        'tags' => 'complete-page,category,landing,seo,products,content',
    ],

    // =========================================================================
    // LINK TYPES
    // =========================================================================

    [
        'category' => 'pagebuilder',
        'subcategory' => 'links',
        'title' => 'PageBuilder Link Types and Attributes',
        'content' => 'PageBuilder link handling across components (banner, slide, button-item, image): Link attributes: href (resolved URL), target (blank for new window, empty for same window), data-link-type ("default" for URL, "product" for product page, "category" for category page, "page" for CMS page), title (link title for accessibility/SEO). Link types are stored internally and converted by link-href, link-target, and link-type converters. When a link is present, content wraps in <a> tag. When no link is configured, a plain <div> replaces the <a> tag (for banners/slides) or the link element is omitted (for images). Image component: link wraps around all img elements inside <figure>. Banner/slide: link wraps around the entire wrapper div. Button-item: link is the primary element (<a> with button class). Store URL directive: href="{{store url=path}}" resolves to absolute URL. Product link: href resolved from product URL key.',
        'tags' => 'links,href,target,link-type,product,category,page,url',
    ],
];
