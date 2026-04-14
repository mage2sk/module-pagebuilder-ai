# Panth PageBuilder AI for Magento 2

[![Magento 2.4.4 - 2.4.8](https://img.shields.io/badge/Magento-2.4.4%20--%202.4.8-orange)]()
[![PHP 8.1 - 8.4](https://img.shields.io/badge/PHP-8.1%20--%208.4-blue)]()
[![Luma Compatible](https://img.shields.io/badge/Luma-Compatible-green)]()

**AI-powered content generation** directly inside the Magento
PageBuilder toolbar and edit panels. Generate full-page HTML layouts,
section content, and field-level copy from configurable prompts with
optional reference-image upload.

---

## Why this extension

| | Manual content creation | **Panth PageBuilder AI** |
|---|---|---|
| Speed | Hours per page | Seconds — one click generates a full page |
| SEO quality | Depends on writer | Built-in SEO-optimized prompt templates |
| Consistency | Varies | Repeatable via saved prompt templates |
| Page types | N/A | 8 presets: Homepage, About, Contact, FAQ, Landing, Category, Policy, 404 |
| Field-level AI | N/A | AI button on every text/textarea in edit panels |
| Image context | N/A | Upload reference images for context-aware generation |

---

## Features

### Toolbar AI button
- Adds a prominent "AI Content" button to the PageBuilder toolbar
- One-click full-page content generation
- Page-type selector with 8 presets (Custom, Homepage, About Us,
  Contact, FAQ, Product Landing, Category Landing, Shipping & Returns,
  404 Page)
- Editable prompt textarea for full customisation
- Reference image upload (up to 5 images, max 5 MB each)

### Field-level AI buttons
- Automatically injects small AI buttons next to every text input and
  textarea in PageBuilder edit panels (slide-out forms)
- Works with all content types: Text, Heading, Buttons, Banner, Slider,
  Tabs, Columns, and any custom content type
- Generated content is injected directly into the field and dispatches
  proper change events for Knockout/TinyMCE bindings

### Saved prompt templates
- Loads saved prompt templates from the Panth_AdvancedSEO prompt table
- Supports entity types: cms_page, pagebuilder, all
- Sorted by is_default DESC, sort_order ASC

### Soft dependency on Panth_AdvancedSEO
- The module gracefully degrades to rendering NOTHING if
  Panth_AdvancedSEO is not installed, not enabled, or has AI disabled
- No hard class dependencies — uses class_exists() + guarded
  ObjectManager pattern in the ViewModel
- Will never cause a DI compile error or ReflectionException on
  installations without AdvancedSEO

---

## Installation

### Via Composer (recommended)

```bash
composer require mage2kishan/module-pagebuilder-ai
bin/magento module:enable Panth_Core Panth_PageBuilderAi
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Via uploaded zip

1. Download the extension zip from the Marketplace
2. Extract to `app/code/Panth/PageBuilderAi`
3. Make sure `app/code/Panth/Core` is also installed
4. Run the same commands above starting from `module:enable`

### Verify

```bash
bin/magento module:status Panth_PageBuilderAi
# Module is enabled
```

---

## Requirements

| | Required |
|---|---|
| Magento | 2.4.4 — 2.4.8 (Open Source / Commerce / Cloud) |
| PHP | 8.1 / 8.2 / 8.3 / 8.4 |
| `mage2kishan/module-core` | ^1.0 (installed automatically as a composer dependency) |
| `Panth_AdvancedSEO` | Recommended — provides the AI generation backend. Without it the module renders nothing. |

---

## How it works

1. The module registers an adminhtml layout handle (`default.xml`) that
   loads CSS and a JavaScript file on every admin page
2. The JavaScript uses a MutationObserver + polling fallback to detect
   when the PageBuilder toolbar and edit panels render
3. It injects the "AI Content" toolbar button and per-field AI buttons
4. When the user clicks Generate, the JavaScript POSTs to the
   Panth_AdvancedSEO AI generation endpoint
   (`panth_seo/aigenerate/generate`) with the prompt, entity context,
   and optional images
5. The generated HTML is inserted into the PageBuilder stage or the
   target field

---

## Support

| Channel | Contact |
|---|---|
| Email | kishansavaliyakb@gmail.com |
| Website | https://kishansavaliya.com |
| WhatsApp | +91 84012 70422 |

Response time: 1-2 business days for paid licenses.

---

## License

Commercial — see `LICENSE.txt`. One license per Magento production
installation. Includes 12 months of free updates and email support.

---

## About the developer

Built and maintained by **Kishan Savaliya** — https://kishansavaliya.com.
Builds high-quality Magento 2 extensions and themes for both Hyva and
Luma storefronts.
