# Panth PageBuilder AI — User Guide

This guide walks a Magento store administrator through every feature
of the Panth PageBuilder AI extension. No coding required.

---

## Table of contents

1. [Installation](#1-installation)
2. [Prerequisites](#2-prerequisites)
3. [Using the toolbar AI button](#3-using-the-toolbar-ai-button)
4. [Page-type presets](#4-page-type-presets)
5. [Using field-level AI buttons](#5-using-field-level-ai-buttons)
6. [Uploading reference images](#6-uploading-reference-images)
7. [Saved prompt templates](#7-saved-prompt-templates)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. Installation

### Composer (recommended)

```bash
composer require mage2kishan/module-pagebuilder-ai
bin/magento module:enable Panth_Core Panth_PageBuilderAi
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Manual zip

1. Download the extension package zip
2. Extract to `app/code/Panth/PageBuilderAi`
3. Make sure `app/code/Panth/Core` is also present
4. Run the same `module:enable ... cache:flush` commands above

### Confirm

```bash
bin/magento module:status Panth_PageBuilderAi
# Module is enabled
```

---

## 2. Prerequisites

This extension requires **Panth_AdvancedSEO** to be installed and
configured with a working AI provider (OpenAI, Anthropic, etc.). The
PageBuilder AI buttons call the AI generation backend that lives in
AdvancedSEO.

If AdvancedSEO is not installed, not enabled, or has AI generation
disabled in its configuration, the PageBuilder AI buttons will simply
not render. The extension gracefully degrades and will never cause
errors.

To configure the AI backend:
1. Install Panth_AdvancedSEO
2. Go to Stores -> Configuration -> Panth Extensions -> Advanced SEO
3. Enable AI Content Generation
4. Enter your AI provider API key
5. Save and flush cache

---

## 3. Using the toolbar AI button

1. Navigate to Content -> Pages and edit (or create) a CMS page
2. In the PageBuilder editor, look for the "AI Content" button in
   the toolbar area (next to the template buttons)
3. Click the button to open the AI generation dialog
4. Select a page type from the dropdown or leave it on "Custom Page"
5. Edit the prompt in the textarea to customise what you want
6. Optionally upload reference images
7. Click "Generate Page"
8. Wait for the AI to generate content (typically 5-15 seconds)
9. The generated HTML is automatically inserted into the PageBuilder
   stage

---

## 4. Page-type presets

The toolbar dialog includes 8 page-type presets:

| Preset | What it generates |
|---|---|
| **Custom Page** | Generic full-page layout based on your prompt |
| **Homepage** | Hero banner, featured categories, new arrivals, USP badges, testimonials, newsletter signup |
| **About Us** | Company story, mission and values, team section, milestones, why choose us, CTA |
| **Contact Page** | Contact info, FAQ about support, map placeholder, social media links |
| **FAQ Page** | FAQ with details/summary accordion, 8-10 questions in categories, FAQPage schema |
| **Product Landing** | Hero, problem/solution, feature grid, comparison table, testimonials, pricing/CTA |
| **Category Landing** | Category description, buying guide, comparison guide, care tips, FAQ |
| **Shipping & Returns** | Shipping methods table, return policy, refund timeline, exchange policy, FAQ |
| **404 Page** | Friendly heading, search suggestion, helpful links, popular products placeholder |

Each preset populates the prompt textarea with an optimised prompt.
You can further edit the prompt before generating.

---

## 5. Using field-level AI buttons

When you open any PageBuilder edit panel (click the pencil icon on
a content type), small AI buttons appear next to every text input and
textarea field.

1. Click the small "AI" button next to any field
2. A dialog opens with a pre-filled prompt for that specific field
3. Edit the prompt if desired
4. Click "Generate"
5. The generated content is inserted directly into the field
6. The field's change events are triggered so Knockout bindings and
   TinyMCE editors update correctly

This works for all content types: Text, Heading, Buttons, Banner,
Slider, Tabs, Columns, and any custom content types that use standard
text inputs or textareas.

---

## 6. Uploading reference images

Both the toolbar dialog and field-level dialogs support uploading up
to 5 reference images (max 5 MB each).

Reference images are sent to the AI provider as context. This is
useful when you want the AI to:
- Match the style of an existing page design
- Describe products shown in the images
- Generate content that references visual elements

Supported formats: JPG, PNG, GIF, WebP, and any image format accepted
by your browser.

---

## 7. Saved prompt templates

If you have saved prompt templates in Panth_AdvancedSEO (in the
`panth_seo_ai_prompt` table) with entity types `cms_page`,
`pagebuilder`, or `all`, they will be available in the PageBuilder
AI dialogs.

Templates are sorted with default templates first, then by sort order.

---

## 8. Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| AI button not appearing in toolbar | Panth_AdvancedSEO not installed or AI disabled | Install AdvancedSEO and enable AI generation in its config |
| AI button not appearing in toolbar | PageBuilder not loaded yet | Wait for the page to fully load; the button polls for up to 60 seconds |
| "Calling AI..." hangs forever | AI provider API key not configured | Check Stores -> Configuration -> Panth Extensions -> Advanced SEO -> API Key |
| "Error: Unknown error" after clicking Generate | AI backend returned an error | Check `var/log/system.log` for details. Common causes: invalid API key, rate limiting, provider outage |
| Generated content not appearing in editor | PageBuilder stage not found | Try switching to the WYSIWYG editor view and back to PageBuilder |
| Field-level AI button missing on some fields | Field appeared before the observer fired | Close and reopen the edit panel; the polling interval is 2 seconds |
| Extension causes DI compile error | Should not happen — soft dependency pattern | Ensure you are running `setup:di:compile` after `setup:upgrade` |

---

## Support

For all questions, bug reports, or feature requests:

- **Email:** kishansavaliyakb@gmail.com
- **Website:** https://kishansavaliya.com
- **WhatsApp:** +91 84012 70422

Response time: 1-2 business days for paid licenses.
