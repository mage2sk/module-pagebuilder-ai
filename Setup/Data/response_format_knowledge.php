<?php
/**
 * Panth AdvancedSEO - AI Response Format Knowledge Base
 *
 * Training data that teaches the AI HOW to format its responses for different
 * Magento entity fields. Each entry provides exact formatting rules, character
 * limits, structural templates, and concrete examples showing the JSON output
 * the AI should return for each field type.
 *
 * @category  Panth
 * @package   Panth_AdvancedSEO
 */

return [

    // =========================================================================
    // PRODUCT NAME FORMATTING (5 entries)
    // =========================================================================

    [
        'category'    => 'response_format',
        'subcategory' => 'product_name',
        'title'       => 'Product Name Capitalization and Title Case Rules',
        'content'     => 'Product names in Magento should use Title Case: capitalize the first letter of every major word. Do NOT capitalize articles (a, an, the), prepositions (in, on, at, for, with, of, to), or conjunctions (and, but, or) unless they are the first word. Always capitalize brand names exactly as the brand writes them (iPhone, adidas, YETI). Technical model numbers preserve their original casing (XPS 15, A2894). Examples of correct title case: "Nike Air Max 270 React Running Shoes" (not "Nike air max 270 react running shoes"), "The North Face Thermoball Eco Jacket" (not "THE NORTH FACE THERMOBALL ECO JACKET"). Never use ALL CAPS for product names as it harms readability and looks spammy. When the AI generates a product name, the JSON response should be: {"name": "Samsung Galaxy S25 Ultra 256GB Titanium Black"} — every significant word capitalized, brand casing preserved, specs in standard notation.',
        'tags'        => ['product-name', 'capitalization', 'title-case', 'formatting', 'brand'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'product_name',
        'title'       => 'Product Name Structure: Brand + Product + Key Feature',
        'content'     => 'The ideal product name follows the pattern: [Brand] [Product Line] [Model/Variant] [Key Differentiator]. This structure helps both SEO and user scanning. The brand comes first for brand-loyal shoppers and search filters. The product line groups related items. The model or variant distinguishes within the line. The key differentiator adds the one attribute shoppers filter by most (size, color, material, capacity). Examples: "Sony WH-1000XM5 Wireless Noise-Cancelling Headphones" follows Brand + Model + Key Feature. "Patagonia Better Sweater Fleece Jacket in Navy" follows Brand + Line + Type + Color. "KitchenAid Artisan 5-Quart Stand Mixer Pistachio" follows Brand + Line + Size + Type + Color. Avoid stuffing the name with every attribute — that belongs in the description. The AI response format: {"name": "Dyson V15 Detect Cordless Vacuum Cleaner"} — concise, scannable, exactly what belongs in an H1 tag and browser title bar.',
        'tags'        => ['product-name', 'structure', 'brand', 'model', 'naming-convention'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'product_name',
        'title'       => 'Product Name Character Limits and Truncation Rules',
        'content'     => 'Magento product names should target 50-80 characters. Under 50 characters may lack necessary detail for SEO. Over 80 characters risks truncation in category grids, search results, and breadcrumbs. Google typically displays 50-60 characters in SERPs before truncating with an ellipsis. Magento category grid tiles often truncate at 40-55 characters depending on the theme. When the full name exceeds the limit, front-load the most important words. "Samsung Galaxy S25 Ultra 256GB Titanium Black Smartphone" is better than "Smartphone Samsung Galaxy S25 Ultra in Titanium Black Color with 256GB Storage." If truncation is unavoidable, ensure the truncated version still makes sense: "Samsung Galaxy S25 Ultra 256GB Tit..." is acceptable; "Smartphone Samsung Galaxy S25 Ultra in T..." loses the key differentiator. The AI should validate character count before returning: {"name": "Bose QuietComfort 45 Wireless Headphones Midnight Blue"} (54 characters — within optimal range). If a generated name exceeds 80 characters, the AI should trim the least critical descriptor while preserving brand, product type, and primary variant.',
        'tags'        => ['product-name', 'character-limit', 'truncation', 'length', 'seo'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'product_name',
        'title'       => 'SEO Keyword Placement in Product Names',
        'content'     => 'The product name is the single most important on-page SEO element because it populates the H1, the URL slug, and often the default meta title. Place the primary keyword as close to the beginning as possible. If someone searches "wireless noise cancelling headphones," the name "Sony WH-1000XM5 Wireless Noise-Cancelling Headphones" matches the query better than "Sony WH-1000XM5 Over-Ear Premium Audio Device." Avoid keyword stuffing: "Best Wireless Headphones Noise Cancelling Bluetooth Headphones Over Ear" is spam. Use one primary keyword phrase naturally. For long-tail targeting, let the product name handle the head term and let the meta title, description, and content handle variations. Do NOT include price, promotional text, or symbols in the product name: "50% OFF Sony Headphones!!!" is never acceptable — use catalog price rules and labels for promotions. The AI response should be: {"name": "Le Creuset Enameled Cast Iron Dutch Oven 5.5 Qt"} — the primary keyword "cast iron dutch oven" appears naturally within the brand-first naming convention.',
        'tags'        => ['product-name', 'seo', 'keywords', 'placement', 'url-slug'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'product_name',
        'title'       => 'Good vs Bad Product Name Examples Across Industries',
        'content'     => 'GOOD product names are specific, scannable, and keyword-rich without being spammy. BAD names are vague, keyword-stuffed, or poorly formatted. Electronics — GOOD: "Apple MacBook Air 15-Inch M3 Chip 16GB 512GB Starlight" BAD: "APPLE laptop macbook air new 2025 best laptop for students cheap". Clothing — GOOD: "Levi\'s 501 Original Fit Straight Leg Jeans Dark Wash" BAD: "jeans men blue denim pants levis 501 original classic vintage". Furniture — GOOD: "West Elm Mid-Century Walnut Dining Table 72 Inch" BAD: "Table Dining Room Kitchen Table Wood Modern Contemporary Mid Century". Beauty — GOOD: "CeraVe Moisturizing Cream for Dry Skin 16 oz" BAD: "BEST Face Cream Moisturizer Lotion Dry Skin Sensitive Skin Eczema CeraVe". Food — GOOD: "Lavazza Super Crema Whole Bean Espresso Coffee 2.2 lb" BAD: "coffee beans espresso italian whole bean lavazza best coffee 2025". The pattern: good names read like a product label you would hold in your hand. Bad names read like a keyword research spreadsheet. The AI should always generate names matching the GOOD pattern: {"name": "Lavazza Super Crema Whole Bean Espresso Coffee 2.2 lb"}.',
        'tags'        => ['product-name', 'examples', 'good-vs-bad', 'best-practices', 'formatting'],
    ],

    // =========================================================================
    // META TITLE FORMATTING (5 entries)
    // =========================================================================

    [
        'category'    => 'response_format',
        'subcategory' => 'meta_title',
        'title'       => 'Meta Title Character Count and Pixel Width Requirements',
        'content'     => 'Meta titles must be 50-60 characters including spaces. Google displays approximately 580 pixels of title width in desktop SERPs and 920 pixels on mobile. At average character width, this translates to roughly 55-60 characters, but wide characters (W, M) consume more pixels than narrow ones (i, l, t). The AI should target 55 characters as the sweet spot. Titles under 30 characters waste valuable SERP real estate and miss keyword opportunities. Titles over 60 characters will be truncated with an ellipsis, potentially cutting off your brand name or CTA. The AI must count characters before returning and adjust if needed. Response format: {"meta_title": "Buy Sony WH-1000XM5 Wireless Headphones | AudioShop"} (52 characters — optimal). If the generated title exceeds 60 characters, the AI should shorten descriptors, abbreviate the store name, or remove filler words like "Online" or "Today." Never sacrifice the primary keyword to fit the limit — sacrifice the store name suffix first.',
        'tags'        => ['meta-title', 'character-count', 'pixel-width', 'serp', 'truncation'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'meta_title',
        'title'       => 'Meta Title Format Templates by Intent',
        'content'     => 'The AI should select a meta title template based on the page type and search intent. Product pages (transactional): "Buy [Product Name] [Key Spec] | [Store]" or "[Product Name] - [Benefit] | [Store]". Examples: "Buy Nike Air Max 270 Running Shoes | ShoeWorld", "Samsung Galaxy S25 Ultra - Free 2-Day Shipping | TechMart". Category pages (navigational/commercial): "Shop [Category] - [USP] | [Store]" or "[Category]: [Selection Claim] | [Store]". Examples: "Shop Men\'s Running Shoes - Free Returns | ShoeWorld", "Wireless Headphones: Top Brands from $29 | AudioShop". CMS/informational pages: "[Topic] - [Value Prop] | [Store]" or "[Question] - [Authority Claim] | [Store]". Examples: "Headphone Buying Guide - Expert Reviews | AudioShop", "How to Choose Running Shoes - Pro Tips | ShoeWorld". The AI should return: {"meta_title": "Shop Women\'s Winter Coats - Free Shipping Over $50 | StyleHQ"} — the template chosen based on the entity type passed in the request.',
        'tags'        => ['meta-title', 'templates', 'format', 'product', 'category', 'cms'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'meta_title',
        'title'       => 'Meta Title Power Words That Increase CTR',
        'content'     => 'Certain words in meta titles measurably increase click-through rate in search results. The AI should incorporate one or two of these when appropriate, but never force them unnaturally. Transactional power words: "Buy" (signals availability), "Shop" (implies selection), "Get" (action-oriented), "Order" (direct CTA), "Save" (implies value). Value words: "Free Shipping," "Sale," "Deal," "Discount," "Lowest Price," "Affordable" — use only when the store actually offers these. Quality words: "Best," "Top-Rated," "Premium," "Genuine," "Official," "Authentic" — use "Best" sparingly as it is overused; prefer "Top-Rated" which implies social proof. Urgency words: "Now," "Today," "Limited," "New" — "New" works well for product launches; avoid fake urgency. Trust words: "Official," "Authorized," "Certified" — especially valuable for brand-name electronics and luxury goods. Example with power words: {"meta_title": "Shop Premium Leather Wallets - Free Shipping | LeatherCo"} — "Shop," "Premium," and "Free Shipping" each add CTR value. Avoid stacking more than two power words as it becomes spammy: "Buy Best Top Premium Cheap Wallets" is counterproductive.',
        'tags'        => ['meta-title', 'power-words', 'ctr', 'click-through-rate', 'persuasion'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'meta_title',
        'title'       => 'Meta Title Truncation Handling Strategy',
        'content'     => 'When a meta title exceeds 60 characters, the AI must intelligently truncate without losing meaning. Google truncates with "..." and may rewrite titles entirely if they are too long or mismatched with content. Truncation priority (what to cut first, in order): (1) Remove the store name suffix — Google often appends the site name from the domain anyway. (2) Remove filler words: "Online," "for Sale," "Available," "at Our Store." (3) Abbreviate sizes and units: "Inches" to "In," "Ounces" to "oz," "Pounds" to "lb." (4) Shorten adjectives: "Professional-Grade" to "Pro," "High-Performance" to "High-Perf." (5) Remove the secondary keyword if the primary is intact. Never truncate: the brand name, the product type keyword, or the core differentiator (size, color, model number). Example — too long (68 chars): "Buy Samsung Galaxy S25 Ultra 256GB Titanium Black Smartphone Online | TechMart" — truncated correctly (56 chars): {"meta_title": "Buy Samsung Galaxy S25 Ultra 256GB Titanium Black | TechMart"} — "Smartphone" and "Online" removed, core identity preserved.',
        'tags'        => ['meta-title', 'truncation', 'character-limit', 'optimization', 'rewriting'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'meta_title',
        'title'       => 'Meta Title Examples by Industry',
        'content'     => 'The AI should adapt meta title tone and format by industry. Fashion/Apparel: "Shop Levi\'s 501 Original Jeans - Free Returns | DenimHQ" — casual, benefit-led. Electronics: "Buy iPhone 16 Pro Max 256GB - Next Day Delivery | TechStore" — spec-focused, urgency. Home/Furniture: "West Elm Mid-Century Dining Table - Ships Free | HomeStyle" — brand + style, shipping incentive. Beauty/Skincare: "CeraVe Moisturizing Cream 16 oz - Dermatologist Tested | SkinShop" — trust signal (dermatologist), size included. Food/Grocery: "Lavazza Super Crema Coffee Beans 2.2 lb | FreshGrocer" — simple, weight included for comparison shoppers. Jewelry: "14K Gold Diamond Solitaire Ring 0.5 ct | LuxeJewelers" — materials and carat weight are the keywords. Sports/Fitness: "Rogue Ohio Barbell 20kg - Cerakote | FitGear" — athlete-grade language, weight in metric. B2B/Industrial: "3M N95 Respirator Mask 50-Pack | SafetySupply" — pack size is the differentiator. Each follows the formula: [Action/Brand] [Product + Key Spec] - [Benefit/USP] | [Store]. Response format: {"meta_title": "Rogue Ohio Barbell 20kg Cerakote Black | FitGear"} (50 characters).',
        'tags'        => ['meta-title', 'examples', 'industry', 'fashion', 'electronics', 'beauty'],
    ],

    // =========================================================================
    // META DESCRIPTION FORMATTING (5 entries)
    // =========================================================================

    [
        'category'    => 'response_format',
        'subcategory' => 'meta_description',
        'title'       => 'Meta Description Character Count and Structure',
        'content'     => 'Meta descriptions must be 140-156 characters. Google displays up to approximately 920 pixels on desktop, which translates to roughly 155-160 characters. However, Google frequently truncates at 150-155 characters, so the AI should target 145-155 as the safe zone. Under 120 characters wastes SERP real estate and may cause Google to auto-generate a description from page content instead. The two-sentence structure is optimal: Sentence 1 (descriptive, keyword-rich) + Sentence 2 (CTA or USP). Example response: {"meta_description": "The Sony WH-1000XM5 delivers industry-leading noise cancellation with 30-hour battery life and crystal-clear calls. Shop now with free 2-day shipping and easy returns."} (168 characters — needs trimming). Corrected: {"meta_description": "Sony WH-1000XM5 delivers industry-leading noise cancellation with 30-hour battery life. Shop now with free 2-day shipping and easy returns."} (141 characters — within range). The AI must always count and adjust before returning.',
        'tags'        => ['meta-description', 'character-count', 'structure', 'two-sentence', 'serp'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'meta_description',
        'title'       => 'Meta Description Must Include: Keyword, USP, and CTA',
        'content'     => 'Every meta description the AI generates must contain three elements: (1) Primary keyword — ideally within the first 60 characters so it appears in bold when it matches the search query. Google bolds matching terms, which draws the eye and increases CTR. (2) Unique Selling Proposition (USP) — what makes this product or page different: price point, free shipping, exclusive feature, brand authority, selection size, guarantee. (3) Call to Action (CTA) — a verb phrase that tells the searcher what to do next: "Shop now," "Order today," "Browse our collection," "Get yours," "Discover," "Find your perfect." The CTA should be the last phrase so it is the final impression before the click decision. Format: "[Primary keyword context sentence with USP]. [CTA phrase]." Response example: {"meta_description": "Handcrafted 14K gold diamond rings with lifetime warranty and free resizing. Browse our collection and find your perfect engagement ring today."} — keyword "14K gold diamond rings" is front-loaded, USP is "lifetime warranty and free resizing," CTA is "browse our collection and find your perfect engagement ring today."',
        'tags'        => ['meta-description', 'keyword', 'usp', 'cta', 'call-to-action', 'click-through'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'meta_description',
        'title'       => 'Meta Description Special Characters for Visual Separation',
        'content'     => 'Special characters in meta descriptions create visual structure in SERPs and improve scannability. Approved separators: pipe (|), dash (-/em dash), bullet (.), checkmark (check mark entity), and star (star entity). Use them to list multiple benefits concisely. However, do not overuse them — one to two separators per description is optimal. More than three makes the description look spammy. Format with separators: "[Keyword sentence] | [Benefit] | [CTA]" or "[Keyword sentence]. [Benefit 1]. [Benefit 2]. [CTA]." Example responses — Pipe style: {"meta_description": "Premium leather wallets handcrafted in Italy | RFID blocking | Lifetime guarantee. Shop the collection with free shipping over $50."} Bullet style: {"meta_description": "CeraVe Moisturizing Cream for dry skin. Dermatologist recommended. Fragrance-free. Non-comedogenic. Order now with free samples on every purchase."} Dash style: {"meta_description": "Shop men\'s running shoes - Nike, Adidas, New Balance and more. Free returns within 60 days and price match guarantee at ShoeWorld."} Avoid using emojis in meta descriptions — Google strips most of them and they can make the brand appear unprofessional in search results.',
        'tags'        => ['meta-description', 'special-characters', 'separators', 'visual', 'formatting'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'meta_description',
        'title'       => 'Meta Description with Price, Discount, and Shipping Mentions',
        'content'     => 'Including price, discount, or shipping information in meta descriptions significantly increases CTR for transactional queries because it pre-qualifies the click — users know what to expect before landing on the page. Price mention: use "from $X" or "starting at $X" for configurable products, exact price for simple products. Google may also display price via structured data, but including it in the meta description provides a fallback. Discount mention: "Save X%," "Up to X% off," or "$X off" — only when there is an active promotion. Shipping mention: "Free shipping," "Free 2-day shipping," "Ships in 24 hours" — these are among the highest-CTR phrases in e-commerce meta descriptions. Examples — With price: {"meta_description": "KitchenAid Artisan Stand Mixer starting at $349. 10 colors available with free shipping and 1-year hassle-free warranty. Order yours today."} With discount: {"meta_description": "Save 30% on all winter coats this week. Shop premium brands like North Face and Patagonia with free returns. Limited time offer — shop now."} With shipping: {"meta_description": "Bose QuietComfort 45 wireless headphones with free next-day shipping. 24-hour battery, world-class noise cancellation. Buy now at AudioShop."} The AI should only include price/discount data if the store provides it in the product or promotion data — never fabricate pricing.',
        'tags'        => ['meta-description', 'price', 'discount', 'shipping', 'ctr', 'transactional'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'meta_description',
        'title'       => 'Meta Description Examples for Products, Categories, and CMS Pages',
        'content'     => 'The AI should vary its meta description format based on entity type. Product page: {"meta_description": "The Dyson V15 Detect reveals hidden dust with a laser and auto-adjusts suction power. 60-minute runtime on a full charge. Shop with free delivery."} — specific, feature-focused, single product. Category page: {"meta_description": "Shop 200+ wireless headphones from Sony, Bose, Apple and more. Compare prices, read reviews, and enjoy free shipping on orders over $49 at AudioShop."} — emphasizes selection breadth, multiple brands, comparison shopping. CMS About page: {"meta_description": "AudioShop has been the trusted destination for premium audio equipment since 2005. Expert staff, 30-day returns, and price match guarantee."} — trust, credentials, policies. CMS FAQ page: {"meta_description": "Find answers to common questions about shipping, returns, warranty, and order tracking at AudioShop. Contact our support team for help."} — practical, covers multiple query intents. Landing/promotional page: {"meta_description": "Black Friday deals on headphones, speakers, and turntables. Up to 50% off top brands. Sale ends Monday — shop early for best selection at AudioShop."} — urgency, discount range, time-bound. Each format prioritizes what matters most for that page type.',
        'tags'        => ['meta-description', 'examples', 'product', 'category', 'cms', 'entity-type'],
    ],

    // =========================================================================
    // PRODUCT DESCRIPTION (HTML) FORMATTING (10 entries)
    // =========================================================================

    [
        'category'    => 'response_format',
        'subcategory' => 'product_description_html',
        'title'       => 'Product Description Must Output Valid HTML Tags',
        'content'     => 'When the AI generates a product description, the response MUST contain valid HTML markup — never plain text. Magento stores product descriptions as HTML in the catalog_product_entity_text table and renders them directly on the frontend. The AI should use these tags: <p> for paragraphs, <ul> and <li> for feature/benefit lists, <strong> for emphasis on key terms (also signals relevance to search engines), <em> for secondary emphasis or brand voice, <h2> and <h3> for section headings within the description, <table> for specifications. Never use: <div> without purpose (adds no semantic value), <br> for spacing (use proper <p> tags instead), <font> or <center> (deprecated HTML), inline JavaScript, or <style> blocks. The AI response must be: {"description": "<p>Opening hook paragraph with primary keyword.</p><ul><li><strong>Feature 1</strong> - Benefit explanation</li><li><strong>Feature 2</strong> - Benefit explanation</li><li><strong>Feature 3</strong> - Benefit explanation</li></ul><p>Closing paragraph with call to action.</p>"} — valid, semantic HTML that renders cleanly in any Magento theme.',
        'tags'        => ['description', 'html', 'markup', 'tags', 'valid-html', 'semantic'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'product_description_html',
        'title'       => 'Product Description Structure: Hook, Features, Benefits, CTA',
        'content'     => 'The AI must follow a four-part HTML structure for every product description. Part 1 — Opening Hook (one <p> tag): A compelling first sentence that addresses the customer\'s need or desire. Include the primary keyword naturally. "Upgrade your morning coffee ritual with the Breville Barista Express — a prosumer espresso machine that grinds, doses, and extracts cafe-quality shots in under 60 seconds." Part 2 — Feature List (one <ul> with 3-6 <li> items): Each list item leads with the feature in <strong> tags and follows with the benefit. "<li><strong>Integrated Conical Burr Grinder</strong> — Grinds fresh beans directly into the portafilter for maximum flavor extraction</li>." Part 3 — Benefits/Value Paragraph (one or two <p> tags): Expand on why the features matter to the buyer\'s life. This is where emotion lives. "No more waiting in line or paying $6 for a latte. With the Barista Express on your counter, every morning starts with barista-quality espresso at a fraction of the cost." Part 4 — Call to Action (one <p> tag): A single confident CTA. "Add to cart and taste the difference tomorrow morning." Full response: {"description": "<p>Upgrade your morning coffee ritual with the Breville Barista Express...</p><ul><li><strong>Integrated Burr Grinder</strong> — fresh grounds for maximum flavor</li>...</ul><p>No more $6 lattes...</p><p>Add to cart and taste the difference tomorrow morning.</p>"}',
        'tags'        => ['description', 'structure', 'hook', 'features', 'benefits', 'cta', 'html'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'product_description_html',
        'title'       => 'Product Description PageBuilder Compatibility',
        'content'     => 'When a Magento store uses PageBuilder for product descriptions, the AI-generated HTML should include PageBuilder data attributes so it renders correctly in the PageBuilder editor and does not get stripped or corrupted when the admin edits the content. The minimum PageBuilder-compatible wrapper is: <div data-content-type="text" data-appearance="default" data-element="main">YOUR HTML HERE</div>. For descriptions with multiple sections, wrap each section in its own PageBuilder text block. A PageBuilder-compatible description response: {"description": "<div data-content-type=\"text\" data-appearance=\"default\" data-element=\"main\"><p>Opening hook paragraph.</p><ul><li><strong>Feature 1</strong> - Benefit</li><li><strong>Feature 2</strong> - Benefit</li></ul><p>Closing CTA paragraph.</p></div>"}. If the description includes headings, they can remain inside the text block — PageBuilder preserves <h2> and <h3> tags within text content types. For stores NOT using PageBuilder (detected by checking if the content already lacks data-content-type attributes), the AI should output raw HTML without the PageBuilder wrapper div. The AI should check existing product descriptions to determine whether PageBuilder formatting is expected.',
        'tags'        => ['description', 'pagebuilder', 'data-attributes', 'compatibility', 'magento-admin'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'product_description_html',
        'title'       => 'Product Description Word Count Targets',
        'content'     => 'The AI should calibrate description length to the product complexity and price point. Short descriptions (150-300 words) suit low-consideration products: basic apparel, consumables, accessories under $50. These need a hook, 3-4 features, and a CTA — no deep storytelling. Medium descriptions (300-500 words) suit mid-range products: electronics under $500, furniture, specialty food, fitness equipment. Include a hook, 5-6 features, a benefits paragraph, brief specs, and a CTA. Detailed descriptions (500-800 words) suit high-consideration products: luxury goods, electronics over $500, B2B equipment, configurable products with many variants. Include a hook, features, benefits, comparison context, specs table, FAQ section, and CTA. The AI must never pad descriptions with filler to reach a word count — every sentence must add value. If a simple product only warrants 180 words, that is the right length. Response format for a short product: {"description": "<p>Hook sentence.</p><ul><li><strong>Feature</strong> - Benefit</li><li><strong>Feature</strong> - Benefit</li><li><strong>Feature</strong> - Benefit</li></ul><p>CTA sentence.</p>"} — approximately 150-200 words of substantive content, no fluff.',
        'tags'        => ['description', 'word-count', 'length', 'short', 'medium', 'detailed'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'product_description_html',
        'title'       => 'Product Description Headings: H2 and H3 Usage',
        'content'     => 'For medium and detailed product descriptions, the AI should embed <h2> and <h3> headings to create scannable sections. The product name is already in the <h1> tag on the page, so description headings start at <h2>. Never use <h1> inside a product description. Recommended heading structure for a detailed description: <h2>Why Choose [Product Name]</h2> or <h2>Key Features</h2> — introduces the feature section. <h2>Specifications</h2> — introduces the specs table. <h2>Frequently Asked Questions</h2> — if FAQ section is included. <h3> tags are used for subsections under an <h2>: <h3>Performance</h3>, <h3>Design</h3>, <h3>Compatibility</h3>. Headings should include secondary keywords naturally: <h2>Key Features of the Breville Barista Express Espresso Machine</h2> reinforces the product keyword. Response example: {"description": "<p>Hook paragraph.</p><h2>Key Features</h2><ul><li>...</li></ul><h2>Specifications</h2><table><tr><th>Dimension</th><td>Value</td></tr></table><h2>Frequently Asked Questions</h2><p><strong>Q: Question?</strong></p><p>A: Answer.</p><p>CTA paragraph.</p>"} — semantic heading hierarchy for SEO and accessibility.',
        'tags'        => ['description', 'headings', 'h2', 'h3', 'hierarchy', 'accessibility', 'seo'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'product_description_html',
        'title'       => 'Product Description Comparison Tables in HTML',
        'content'     => 'Comparison tables in product descriptions help shoppers who are evaluating multiple options. The AI should generate them when the product has a clear competitor or predecessor to compare against, or when the product comes in multiple variants. Use semantic HTML tables with <thead>, <tbody>, <th>, and <td>. Always include a scope attribute on <th> for accessibility. Format: {"description": "...<h2>How It Compares</h2><table><thead><tr><th scope=\"col\">Feature</th><th scope=\"col\">Model X (This Product)</th><th scope=\"col\">Model Y (Previous)</th></tr></thead><tbody><tr><td>Battery Life</td><td><strong>30 hours</strong></td><td>20 hours</td></tr><tr><td>Weight</td><td><strong>250g</strong></td><td>280g</td></tr><tr><td>Noise Cancellation</td><td><strong>Adaptive ANC</strong></td><td>Standard ANC</td></tr></tbody></table>..."} — the current product\'s superior values are wrapped in <strong> to draw the eye. Keep comparison tables to 4-6 rows — enough to demonstrate value without overwhelming. The table must be mobile-friendly: avoid wide tables with more than 3 columns as they force horizontal scrolling on phones.',
        'tags'        => ['description', 'comparison', 'table', 'html-table', 'versus', 'specs'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'product_description_html',
        'title'       => 'Product Description Feature Icons and Badges Inline',
        'content'     => 'The AI can enhance product descriptions with inline badge-style elements using <span> tags with descriptive classes that the frontend theme can style. This approach is semantic and does not require images. Format for badges: <span class="product-badge product-badge--free-shipping">Free Shipping</span>, <span class="product-badge product-badge--bestseller">Bestseller</span>, <span class="product-badge product-badge--eco-friendly">Eco-Friendly</span>. For a feature icon list (commonly styled as a grid), use a dedicated class: {"description": "...<ul class=\"feature-icon-list\"><li class=\"feature-icon-list__item feature-icon-list__item--warranty\"><strong>5-Year Warranty</strong></li><li class=\"feature-icon-list__item feature-icon-list__item--shipping\"><strong>Free Shipping</strong></li><li class=\"feature-icon-list__item feature-icon-list__item--returns\"><strong>30-Day Returns</strong></li><li class=\"feature-icon-list__item feature-icon-list__item--support\"><strong>24/7 Support</strong></li></ul>..."} — the CSS classes follow BEM naming convention. The theme stylesheet can add icons via ::before pseudo-elements. The AI should only add these when the store theme supports them — check existing product descriptions for class patterns before using custom classes. If no custom classes are found, use plain <strong> text in a standard <ul> instead.',
        'tags'        => ['description', 'badges', 'icons', 'features', 'css-classes', 'bem'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'product_description_html',
        'title'       => 'Product Description Specifications Formatting',
        'content'     => 'Specifications should be formatted as an HTML table or definition list for easy scanning. The AI should choose the format based on data density. For products with 5+ spec rows, use a table: {"description": "...<h2>Specifications</h2><table class=\"product-specs\"><tbody><tr><th scope=\"row\">Dimensions</th><td>14.2 x 9.7 x 0.6 inches</td></tr><tr><th scope=\"row\">Weight</th><td>3.4 lbs (1.54 kg)</td></tr><tr><th scope=\"row\">Display</th><td>15.3-inch Liquid Retina XDR</td></tr><tr><th scope=\"row\">Processor</th><td>Apple M3 chip, 8-core CPU, 10-core GPU</td></tr><tr><th scope=\"row\">Memory</th><td>16GB unified memory</td></tr><tr><th scope=\"row\">Storage</th><td>512GB SSD</td></tr><tr><th scope=\"row\">Battery</th><td>Up to 18 hours</td></tr></tbody></table>..."} — each <th> has scope="row" for accessibility. For fewer specs (2-4), use a compact definition list: <dl><dt>Weight</dt><dd>250g</dd><dt>Battery</dt><dd>30 hours</dd></dl>. Include units in every value. Use both metric and imperial when selling internationally. Bold standout specs using <strong> inside <td> to highlight best-in-class values.',
        'tags'        => ['description', 'specifications', 'specs', 'table', 'definition-list', 'technical'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'product_description_html',
        'title'       => 'Product Description FAQ Section Formatting',
        'content'     => 'An FAQ section within the product description serves two purposes: it answers pre-purchase questions that reduce support tickets, and it creates content eligible for Google\'s FAQ rich results. The AI should format FAQs as structured HTML that can also be paired with FAQ schema: {"description": "...<h2>Frequently Asked Questions</h2><div class=\"product-faq\" itemscope itemtype=\"https://schema.org/FAQPage\"><div class=\"product-faq__item\" itemscope itemprop=\"mainEntity\" itemtype=\"https://schema.org/Question\"><h3 itemprop=\"name\">Is the Breville Barista Express good for beginners?</h3><div itemscope itemprop=\"acceptedAnswer\" itemtype=\"https://schema.org/Answer\"><p itemprop=\"text\">Yes. The built-in grinder and preset shot volumes make it approachable for first-time home baristas, while the manual controls let you grow into more advanced techniques.</p></div></div><div class=\"product-faq__item\" itemscope itemprop=\"mainEntity\" itemtype=\"https://schema.org/Question\"><h3 itemprop=\"name\">What grind size should I use?</h3><div itemscope itemprop=\"acceptedAnswer\" itemtype=\"https://schema.org/Answer\"><p itemprop=\"text\">Start at grind setting 5 (middle) and adjust finer if your shot runs too fast, or coarser if it chokes.</p></div></div></div>..."} — each Q&A pair is in its own div with proper Schema.org microdata. Limit to 3-5 questions per product. Questions should address the most common objections or confusion points.',
        'tags'        => ['description', 'faq', 'questions', 'schema', 'rich-results', 'microdata'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'product_description_html',
        'title'       => 'Product Description Schema-Ready Content Patterns',
        'content'     => 'The AI should structure product description HTML so it complements the JSON-LD Product schema that Magento outputs in the page head. Key patterns: (1) Mention the brand name in the first paragraph so NLP extractors and schema validators can confirm brand consistency. (2) Include a clear price mention or "starting at" phrase if the description references pricing — this aligns with the schema offers.price. (3) Reference ratings or reviews naturally: "Rated 4.8 out of 5 by over 2,000 customers" supports the schema aggregateRating. (4) Mention availability: "In stock and ready to ship" aligns with schema availability. (5) Include GTIN/SKU when relevant: "Model: BES870XL" reinforces schema identifiers. (6) Use product condition language: "Brand new" or "Factory sealed" supports schema itemCondition. The AI response should weave these naturally: {"description": "<p>The <strong>Breville Barista Express (BES870XL)</strong> is the top-rated home espresso machine, trusted by over 15,000 home baristas. In stock and ships within 24 hours.</p><ul><li>...</li></ul><p>Order today and join thousands of happy customers who rated this machine 4.8 out of 5 stars.</p>"} — brand, model, availability, and social proof are all present and extractable.',
        'tags'        => ['description', 'schema', 'structured-data', 'json-ld', 'product-schema', 'seo'],
    ],

    // =========================================================================
    // SHORT DESCRIPTION FORMATTING (3 entries)
    // =========================================================================

    [
        'category'    => 'response_format',
        'subcategory' => 'short_description',
        'title'       => 'Short Description Length and Sentence Count',
        'content'     => 'The product short description in Magento appears on category listing pages, search results within the site, and above the fold on the product page (depending on theme). It must be 1-3 sentences, maximum 250 characters. The AI should treat it as the "elevator pitch" — if a shopper reads nothing else, this must convey the single most important reason to buy. One-sentence format (for simple products): "Ultra-lightweight wireless earbuds with 8-hour battery and IPX5 water resistance for active lifestyles." Two-sentence format (most products): "Professional-grade espresso machine with integrated burr grinder for cafe-quality shots at home. Programmable shot volume and auto-purge for consistent results." Three-sentence format (complex products): "Flagship noise-cancelling headphones with 30-hour battery and multipoint Bluetooth. Industry-leading ANC adapts to your environment in real time. Includes carrying case and flight adapter." Response format: {"short_description": "Ultra-lightweight wireless earbuds with 8-hour battery and IPX5 water resistance for active lifestyles."} — plain text, no HTML tags (some themes render it inside a <p> automatically). If the theme expects HTML, wrap in a single <p> tag.',
        'tags'        => ['short-description', 'length', 'character-limit', 'elevator-pitch', 'concise'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'short_description',
        'title'       => 'Short Description Must Highlight the Key Benefit',
        'content'     => 'The short description is NOT a summary of the full description — it is a benefit-focused hook. Lead with the outcome, not the feature. BAD: "This vacuum has a 150W motor and HEPA filter." GOOD: "Captures 99.97% of allergens for cleaner air in every room — powered by a 150W motor with true HEPA filtration." The benefit (cleaner air, fewer allergens) comes before the proof (motor power, HEPA spec). For each product type, identify the single most persuasive benefit: Clothing — fit and feel ("Relaxed-fit cotton tee that feels broken-in from the first wear"). Electronics — the problem it solves ("Never miss a word on calls, even in noisy cafes, with adaptive noise cancellation"). Food — the sensory experience ("Single-origin Ethiopian beans with notes of blueberry and dark chocolate"). Furniture — how it fits the buyer\'s life ("Seats four daily, six on holidays — sized perfectly for apartment dining rooms"). Response: {"short_description": "Captures 99.97% of allergens for visibly cleaner air in every room. True HEPA filtration with a washable pre-filter that saves you $50/year on replacements."} — benefit first, proof second, value reinforcement third.',
        'tags'        => ['short-description', 'benefit', 'hook', 'persuasion', 'outcome-focused'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'short_description',
        'title'       => 'Short Description Must Not Duplicate Meta Description',
        'content'     => 'The short description and meta description serve different audiences and contexts — the AI must generate distinct content for each even though both are brief. The meta description is for search engine result pages: it needs to include the primary keyword early, a USP, and a CTA that encourages the click. The short description is for on-site visitors who are already on the product page or browsing a category: it needs to convey the product\'s core benefit and spark interest in reading the full description. Duplication causes two problems: (1) Google may see the repeated text as thin content. (2) On-site, the visitor sees the same text twice if the meta description appears in internal search results and the short description on the product page. Example of properly differentiated content: {"meta_description": "Buy the Dyson V15 Detect cordless vacuum with laser dust detection. Free shipping and 2-year warranty at CleanHome.", "short_description": "Reveals hidden dust with a built-in laser and automatically adjusts suction power across different floor types. 60-minute runtime on a single charge."} — the meta description has "Buy," "Free shipping," and store name (SERP elements). The short description focuses on product capability (on-site element). The AI should generate both in a single response when both fields are requested, ensuring no sentence overlap.',
        'tags'        => ['short-description', 'meta-description', 'duplication', 'unique-content', 'differentiation'],
    ],

    // =========================================================================
    // META KEYWORDS FORMATTING (3 entries)
    // =========================================================================

    [
        'category'    => 'response_format',
        'subcategory' => 'meta_keywords',
        'title'       => 'Meta Keywords Comma-Separated Format and Count',
        'content'     => 'While Google has publicly stated it ignores the meta keywords tag, other search engines (Bing, Yandex, Baidu) and internal Magento site search may still use them. The AI should generate 5-10 comma-separated keywords. Each keyword or phrase should be lowercase unless it is a brand name. No trailing comma. No spaces before commas, one space after each comma. Response format: {"meta_keywords": "sony headphones, wireless headphones, noise cancelling headphones, wh-1000xm5, bluetooth headphones, over ear headphones, sony wh1000xm5, best noise cancelling"} — 8 keywords, mix of specific and general, all lowercase except brand names. Never exceed 10 keywords — more dilutes relevance. Never include generic words alone ("good," "best," "buy") — they must be part of a phrase ("best noise cancelling headphones"). Never repeat the exact same keyword. Avoid single-word keywords unless they are brand names or highly specific product types.',
        'tags'        => ['meta-keywords', 'format', 'comma-separated', 'count', 'lowercase'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'meta_keywords',
        'title'       => 'Meta Keywords: Mix of Short-Tail and Long-Tail',
        'content'     => 'The AI should generate a balanced mix of keyword types: 2-3 short-tail (broad, high-volume) keywords, 3-4 medium-tail (more specific) keywords, and 2-3 long-tail (very specific, low-competition) keywords. Short-tail examples: "wireless headphones," "bluetooth headphones." Medium-tail examples: "noise cancelling wireless headphones," "sony over ear headphones." Long-tail examples: "sony wh-1000xm5 replacement ear pads," "best wireless headphones for working from home." This mix ensures visibility across different search intent levels. The AI should derive these from the product attributes: name, brand, category, features, use cases, and compatible products. Response example for a KitchenAid mixer: {"meta_keywords": "stand mixer, kitchenaid mixer, kitchenaid artisan, 5 quart stand mixer, tilt head stand mixer, kitchenaid artisan stand mixer pistachio, best stand mixer for baking, kitchenaid mixer attachments compatible"} — progresses from broad to specific, covers brand, type, model, color, use case, and accessory searches.',
        'tags'        => ['meta-keywords', 'short-tail', 'long-tail', 'keyword-mix', 'search-intent'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'meta_keywords',
        'title'       => 'Meta Keywords: Brand, Product Type, and Use Case Coverage',
        'content'     => 'Every meta keywords list should cover three dimensions: (1) Brand keywords — the brand name, brand + product line, brand + model number. These capture branded searches from loyal customers. Examples: "breville," "breville barista express," "breville bes870xl." (2) Product type keywords — the generic category and subcategory terms. These capture non-branded shoppers who know what they want but not which brand. Examples: "espresso machine," "home espresso machine," "espresso maker with grinder." (3) Use case keywords — how the product is used, who uses it, or what problem it solves. These capture long-tail informational and commercial queries. Examples: "beginner espresso machine," "home barista equipment," "espresso machine for small kitchen." Response: {"meta_keywords": "breville, breville barista express, breville bes870xl, espresso machine, home espresso machine, espresso maker with grinder, beginner espresso machine, home barista equipment, best espresso machine under 500"} — covers all three dimensions with no redundancy.',
        'tags'        => ['meta-keywords', 'brand', 'product-type', 'use-case', 'search-coverage'],
    ],

    // =========================================================================
    // OG TITLE / OG DESCRIPTION FORMATTING (3 entries)
    // =========================================================================

    [
        'category'    => 'response_format',
        'subcategory' => 'og_title',
        'title'       => 'OG Title Format: 60-90 Characters, Social-First Tone',
        'content'     => 'The Open Graph title (og:title) appears when a page is shared on Facebook, LinkedIn, Twitter/X, Pinterest, Slack, and messaging apps. It should be 60-90 characters — longer than a meta title because social platforms display more text, but not so long that it wraps awkwardly. The tone should be slightly more casual and engaging than the meta title, which is SEO-focused. The OG title does NOT need a store name suffix — social shares carry the domain link separately. Include the product\'s most share-worthy attribute. Compare: Meta title: "Buy Sony WH-1000XM5 Wireless Headphones | AudioShop" (SEO-optimized). OG title: "Sony WH-1000XM5: 30-Hour Battery and the Best Noise Cancellation We\'ve Tested" (social-optimized). The OG title uses a colon for readability, includes a superlative claim that sparks interest, and omits the store name. Response format: {"og_title": "Sony WH-1000XM5: 30 Hours of Silence in a World of Noise"} — 53 characters, intriguing, shareable. For products without a standout feature, use the benefit angle: {"og_title": "Finally, an Espresso Machine That Makes Cafe-Quality Shots at Home"} — speaks to the desire, not the specs.',
        'tags'        => ['og-title', 'open-graph', 'social', 'facebook', 'sharing', 'tone'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'og_description',
        'title'       => 'OG Description: Social Engagement Over SEO',
        'content'     => 'The Open Graph description (og:description) appears below the OG title in social share previews. It should be 100-200 characters and focus on engagement, not keyword optimization. Unlike the meta description, the OG description does NOT need to include the primary keyword, a store name, or a CTA like "Shop now." Instead, it should answer "Why would someone click this link in their social feed?" Focus on: emotional appeal, curiosity gaps, social proof, or relatable scenarios. Compare: Meta description: "Buy the Sony WH-1000XM5 noise cancelling headphones. 30-hour battery, free shipping. Shop now at AudioShop." OG description: "30 hours of battery, noise cancellation that adapts to your environment, and calls so clear people forget you\'re not in the office." The OG version reads like a friend recommending the product, not like an ad. Response format: {"og_description": "30 hours of battery, adaptive noise cancellation, and calls so clear your coworkers forget you\'re working from a coffee shop."} — conversational, benefit-focused, no brand name or store name. For category pages: {"og_description": "Over 200 wireless headphones from every major brand. Filter by price, features, and reviews to find your perfect pair."} — emphasizes the discovery experience.',
        'tags'        => ['og-description', 'open-graph', 'social', 'engagement', 'conversational'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'og_social',
        'title'       => 'OG Content: Social Sharing Language and Emotional Hooks',
        'content'     => 'When generating OG titles and descriptions, the AI should use language patterns that perform well on social media. Emotional hooks that drive clicks: Curiosity: "The one feature that made us switch from our old headphones" — creates an information gap. Social proof: "Why 15,000 home baristas chose this over the competition" — leverages herd behavior. Aspiration: "Your kitchen, but make it cafe-quality" — paints a lifestyle picture. Surprise: "This $349 espresso machine outperformed a $2,000 commercial unit in our test" — unexpected value claim. Relatability: "For everyone who\'s tired of tangled earbuds on their morning commute" — identifies with a common frustration. Avoid corporate speak: "Leveraging cutting-edge technology to deliver premium audio experiences" is how no human talks on social media. Instead: "Honestly the best headphones we\'ve ever tested — and we\'ve tested a lot." Response for a full OG set: {"og_title": "This $349 Espresso Machine Changed Our Morning Routine", "og_description": "Cafe-quality shots in 60 seconds, built-in grinder, and it looks incredible on the counter. The Breville Barista Express is the upgrade your kitchen deserves."} — both pieces work together, the title hooks with a claim, the description delivers the specifics.',
        'tags'        => ['og-title', 'og-description', 'social-sharing', 'emotional-hooks', 'language'],
    ],

    // =========================================================================
    // CATEGORY DESCRIPTION FORMATTING (3 entries)
    // =========================================================================

    [
        'category'    => 'response_format',
        'subcategory' => 'category_description',
        'title'       => 'Category Description: 2-3 Paragraphs with Category Keywords',
        'content'     => 'Category descriptions in Magento appear at the top or bottom of category pages and are critical for SEO — they are often the only unique text content on pages that are otherwise just product grids. The AI should generate 2-3 paragraphs (150-300 words total) with the category keyword appearing 2-3 times naturally. Paragraph 1 — Introduction and primary keyword: Define the category and what shoppers will find. "Discover our complete collection of wireless headphones, featuring top brands like Sony, Bose, and Apple. Whether you need noise-cancelling headphones for focused work or sport earbuds for your workout, we have options for every listening style and budget." Paragraph 2 — Selection details and secondary keywords: Highlight what differentiates the selection. "Our wireless headphones range from affordable Bluetooth earbuds starting at $29 to premium over-ear models with active noise cancellation and hi-res audio support. Every pair ships free and includes our 30-day satisfaction guarantee." Paragraph 3 (optional) — Guidance or trust: Help the shopper navigate. "Not sure which headphones are right for you? Use our filters to compare by brand, price, battery life, and features, or check out our Headphone Buying Guide for expert recommendations." Response: {"description": "<p>Discover our complete collection of wireless headphones...</p><p>Our wireless headphones range from affordable Bluetooth earbuds...</p><p>Not sure which headphones are right for you?...</p>"}',
        'tags'        => ['category-description', 'seo', 'paragraphs', 'keywords', 'collection'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'category_description',
        'title'       => 'Category Description: Internal Links to Subcategories',
        'content'     => 'Category descriptions should include internal links to related subcategories and popular product types within the category. This distributes link equity, aids crawlability, and helps shoppers navigate. The AI should embed these as natural anchor text within sentences, not as a list of naked URLs. Format: "Browse our <a href=\"{{store url=\'wireless-headphones/over-ear\'}}\">over-ear wireless headphones</a> for maximum comfort during long listening sessions, or explore <a href=\"{{store url=\'wireless-headphones/earbuds\'}}\">true wireless earbuds</a> for a compact, on-the-go option." Use Magento\'s {{store url}} directive for links so they resolve correctly across store views. If the AI does not know the exact URL paths, it should use descriptive placeholders: <a href=\"#subcategory-over-ear\">over-ear headphones</a> and note that the store admin should update the href values. Response: {"description": "<p>Shop our curated selection of <strong>running shoes</strong> from Nike, Adidas, and New Balance. Whether you need <a href=\"{{store url=\'running-shoes/trail\'}}\">trail running shoes</a> for off-road adventures or <a href=\"{{store url=\'running-shoes/road\'}}\">road running shoes</a> for daily training, we have your next pair.</p><p>Looking for guidance? Visit our <a href=\"{{store url=\'running-shoe-guide\'}}\">Running Shoe Fit Guide</a> to find the perfect match for your gait and distance goals.</p>"} — links are contextual, keyword-rich anchor text, using Magento URL directives.',
        'tags'        => ['category-description', 'internal-links', 'subcategory', 'navigation', 'link-equity'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'category_description',
        'title'       => 'Category Description: Product Count and Variety Mentions',
        'content'     => 'Mentioning the size and variety of the product selection in category descriptions provides social proof and sets expectations. Shoppers are more likely to browse a category that promises breadth. Use specific numbers when available: "Choose from over 150 wireless headphones" is more convincing than "Browse our wide selection." Mention the number of brands: "Featuring 25+ trusted brands including Sony, Bose, Jabra, and Sennheiser." Reference the price range to signal inclusivity: "With options from $29 to $549, there is a perfect pair for every budget." Highlight variety in product types: "From compact true wireless earbuds to studio-grade over-ear monitors, our collection covers every use case." If the exact product count is available as dynamic data, the AI can use a Magento directive or note it as a placeholder: "Choose from over {{count}} products" or note "[INSERT PRODUCT COUNT]" for the admin to update. Response: {"description": "<p>Explore over 200 wireless headphones from 30+ brands including Sony, Bose, Apple, and Sennheiser. Our collection spans true wireless earbuds, on-ear models, and over-ear headphones from $29 to $549.</p><p>Every pair includes free shipping, easy 60-day returns, and our price match guarantee. Filter by battery life, noise cancellation level, or connectivity to find exactly what you need.</p>"} — count, brand variety, price range, and product type breadth are all present.',
        'tags'        => ['category-description', 'product-count', 'variety', 'selection', 'social-proof'],
    ],

    // =========================================================================
    // CMS PAGE CONTENT FORMATTING (5 entries)
    // =========================================================================

    [
        'category'    => 'response_format',
        'subcategory' => 'cms_about_us',
        'title'       => 'CMS About Us Page: Storytelling, Credentials, and Trust',
        'content'     => 'The About Us page is the most visited non-product page on most e-commerce sites. The AI should generate content that builds trust through story, credentials, and social proof. Structure: (1) Origin story (1 paragraph): When and why the company started, the founder\'s motivation. "AudioShop was founded in 2005 by Sarah Chen, a professional sound engineer who was frustrated by how difficult it was to find honest, expert advice on audio equipment online." (2) Mission/values (1 paragraph): What the company stands for. "Our mission is simple: help every customer find the right audio gear for their ears, their lifestyle, and their budget — backed by advice from people who actually use the products." (3) Credentials and numbers (1 paragraph): concrete proof. "Today, we serve over 500,000 customers across North America, carry 2,000+ products from 80 brands, and maintain a 4.9-star Trustpilot rating from 12,000+ verified reviews." (4) Team/expertise (optional): "Our support team includes certified audio engineers, not script-reading call center agents." (5) Trust badges: mention guarantees, certifications, memberships. Response format: {"content": "<h2>Our Story</h2><p>AudioShop was founded in 2005 by Sarah Chen...</p><h2>Our Mission</h2><p>We believe everyone deserves great sound...</p><h2>Why Shop With Us</h2><ul><li><strong>500,000+ Happy Customers</strong></li><li><strong>4.9-Star Trustpilot Rating</strong></li><li><strong>Free Shipping on Every Order</strong></li><li><strong>30-Day No-Questions Returns</strong></li></ul>"} — story-led, data-backed, formatted with headings and lists.',
        'tags'        => ['cms', 'about-us', 'storytelling', 'trust', 'credentials', 'brand'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'cms_contact',
        'title'       => 'CMS Contact Page: Clear, Concise, and Accessible',
        'content'     => 'Contact page content should be minimal and functional — shoppers visit this page with a specific task in mind. Avoid long paragraphs. Structure: (1) Heading and one-sentence intro: "Get in touch — we are here to help with orders, product questions, and everything in between." (2) Contact methods in a scannable list: phone, email, live chat hours. (3) Response time expectations: "We respond to all emails within 4 business hours." (4) Physical address if applicable (required for trust and local SEO). (5) Optional: embedded map or link to Google Maps. (6) Link to FAQ to deflect common questions: "For quick answers about shipping, returns, and order tracking, visit our FAQ page." Response format: {"content": "<h2>Contact Us</h2><p>We are here to help with orders, product questions, and returns.</p><ul><li><strong>Phone:</strong> 1-800-555-0199 (Mon-Fri 9am-6pm EST)</li><li><strong>Email:</strong> <a href=\"mailto:support@audiostore.com\">support@audiostore.com</a></li><li><strong>Live Chat:</strong> Available Mon-Fri 9am-8pm EST via the chat icon</li></ul><p>We respond to all emails within 4 business hours during business days.</p><h3>Our Address</h3><p>AudioShop Inc.<br>123 Sound Street, Suite 400<br>Austin, TX 78701</p><p>Looking for quick answers? <a href=\"{{store url=\'faq\'}}\">Visit our FAQ page</a>.</p>"} — scannable, all contact methods visible without scrolling, includes a FAQ deflection link.',
        'tags'        => ['cms', 'contact', 'accessibility', 'phone', 'email', 'address'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'cms_faq',
        'title'       => 'CMS FAQ Page: Schema Markup and Accordion-Ready HTML',
        'content'     => 'FAQ pages must be formatted for two consumers: human readers (via accordion UI) and search engines (via FAQPage schema). The AI should output HTML that supports both. Use <details> and <summary> elements for native accordion behavior that works without JavaScript, or use div-based structures with classes that the theme\'s JS can enhance. Include Schema.org FAQPage microdata for rich result eligibility. Response format: {"content": "<div itemscope itemtype=\"https://schema.org/FAQPage\"><h2>Frequently Asked Questions</h2><div class=\"faq-section\"><h3>Shipping</h3><div class=\"faq-item\" itemscope itemprop=\"mainEntity\" itemtype=\"https://schema.org/Question\"><details><summary itemprop=\"name\">How long does shipping take?</summary><div itemscope itemprop=\"acceptedAnswer\" itemtype=\"https://schema.org/Answer\"><p itemprop=\"text\">Standard shipping takes 5-7 business days. Express shipping (2-day) is available for $9.99. Free standard shipping on all orders over $49.</p></div></details></div><div class=\"faq-item\" itemscope itemprop=\"mainEntity\" itemtype=\"https://schema.org/Question\"><details><summary itemprop=\"name\">Do you ship internationally?</summary><div itemscope itemprop=\"acceptedAnswer\" itemtype=\"https://schema.org/Answer\"><p itemprop=\"text\">Yes, we ship to over 40 countries. International shipping rates and delivery times are calculated at checkout based on your location.</p></div></details></div></div></div>"} — each question uses <details>/<summary> for accordion behavior, wrapped in FAQPage schema microdata. Group questions by topic (Shipping, Returns, Orders, Products) using <h3> headings.',
        'tags'        => ['cms', 'faq', 'schema', 'accordion', 'details-summary', 'rich-results'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'cms_policy',
        'title'       => 'CMS Policy Pages: Clear Sections and Bullet Points',
        'content'     => 'Policy pages (Shipping, Returns, Privacy, Terms) must be crystal clear because ambiguity costs the store money in support tickets and chargebacks. The AI should use short paragraphs, bullet points, and bold key terms so shoppers can scan to find their answer. Structure with <h2> for main sections and <h3> for subsections. Bold the critical terms: timeframes, costs, exclusions. Use <ul> lists for conditions and steps. Avoid legal jargon where possible — write at an 8th-grade reading level. Response format for a Returns page: {"content": "<h2>Return Policy</h2><p>We accept returns within <strong>30 days</strong> of delivery for a full refund or exchange.</p><h3>How to Return an Item</h3><ol><li>Log in to your account and go to <strong>My Orders</strong></li><li>Select the item and click <strong>Request Return</strong></li><li>Print the prepaid shipping label</li><li>Pack the item in its original packaging and drop it off at any UPS location</li></ol><h3>Return Conditions</h3><ul><li>Items must be <strong>unused and in original packaging</strong></li><li>Clearance items are <strong>final sale</strong> and cannot be returned</li><li>Electronics must include all original accessories</li></ul><h3>Refund Timeline</h3><p>Refunds are processed within <strong>3-5 business days</strong> after we receive the item. The refund appears on your statement within 1-2 billing cycles.</p>"} — numbered steps for processes, bullet lists for conditions, bold for key terms.',
        'tags'        => ['cms', 'policy', 'returns', 'shipping', 'privacy', 'terms', 'clarity'],
    ],

    [
        'category'    => 'response_format',
        'subcategory' => 'cms_landing',
        'title'       => 'CMS Landing Pages: Hero, Features, and CTA Structure',
        'content'     => 'Landing pages (seasonal sales, brand showcases, product launches) need a strong visual hierarchy: Hero section, value propositions, featured products/categories, and a clear CTA. The AI should output PageBuilder-compatible HTML for each section. Response format: {"content": "<div data-content-type=\"row\" data-appearance=\"full-width\" data-element=\"main\"><div data-element=\"inner\"><div data-content-type=\"text\" data-appearance=\"default\" data-element=\"main\"><h1>Black Friday Audio Deals</h1><p class=\"hero-subtitle\">Up to 50% off headphones, speakers, and turntables. Sale ends Monday at midnight.</p></div></div></div><div data-content-type=\"row\" data-appearance=\"contained\" data-element=\"main\"><div data-element=\"inner\"><div data-content-type=\"text\" data-appearance=\"default\" data-element=\"main\"><h2>Why Shop Black Friday at AudioShop</h2><ul class=\"value-props\"><li><strong>Lowest Prices Guaranteed</strong> — We price-match any authorized retailer</li><li><strong>Free Express Shipping</strong> — On all orders, no minimum</li><li><strong>Extended Returns</strong> — 60-day holiday return window</li></ul></div></div></div><div data-content-type=\"row\" data-appearance=\"contained\" data-element=\"main\"><div data-element=\"inner\"><div data-content-type=\"text\" data-appearance=\"default\" data-element=\"main\"><h2>Top Deals by Category</h2><p>Shop <a href=\"{{store url=\'headphones\'}}\">Headphones</a> | <a href=\"{{store url=\'speakers\'}}\">Speakers</a> | <a href=\"{{store url=\'turntables\'}}\">Turntables</a> | <a href=\"{{store url=\'accessories\'}}\">Accessories</a></p></div></div></div><div data-content-type=\"row\" data-appearance=\"contained\" data-element=\"main\"><div data-element=\"inner\"><div data-content-type=\"text\" data-appearance=\"default\" data-element=\"main\"><p><strong>Do not miss out.</strong> These deals are live until Monday, November 28 at 11:59 PM EST. <a href=\"{{store url=\'sale\'}}\">Shop All Deals Now</a></p></div></div></div>"} — hero with H1, value props list, category navigation links, urgency-driven CTA. Each section uses PageBuilder row and text wrappers for admin editability.',
        'tags'        => ['cms', 'landing-page', 'hero', 'cta', 'pagebuilder', 'promotional', 'sale'],
    ],

];
