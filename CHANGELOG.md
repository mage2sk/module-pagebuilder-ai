# Changelog

All notable changes to this extension are documented here. The format
is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.0] — Initial release

### Added — toolbar AI button
- "AI Content" button injected into the PageBuilder toolbar via
  MutationObserver + polling fallback (up to 60 seconds).
- Full-page content generation dialog with page-type selector
  (8 presets: Custom, Homepage, About Us, Contact, FAQ, Product
  Landing, Category Landing, Shipping & Returns, 404 Page).
- Editable prompt textarea for full customisation.
- Reference image upload (up to 5 images, max 5 MB each).

### Added — field-level AI buttons
- Small AI buttons automatically injected next to every text input
  and textarea in PageBuilder edit panels (slide-out forms).
- Works with all content types: Text, Heading, Buttons, Banner,
  Slider, Tabs, Columns, and custom content types.
- Generated content dispatches proper input/change events for
  Knockout and TinyMCE bindings.

### Added — saved prompt templates
- ViewModel loads saved prompt templates from the
  `panth_seo_ai_prompt` table for entity types cms_page, pagebuilder,
  and all.
- Sorted by is_default DESC, sort_order ASC.

### Added — soft dependency pattern
- Panth_AdvancedSEO is a soft optional dependency. The module
  gracefully renders nothing if AdvancedSEO is absent, disabled, or
  has AI toggled off.
- Uses class_exists() + guarded ObjectManager pattern in the ViewModel.
  No hard class dependencies that could cause DI compile errors.

### Quality
- Zero MEQP code-sniffer errors at severity 10.
- All template outputs properly escaped (escapeJs, escapeUrl).
- Constructor injection for all required dependencies.

### Compatibility
- Magento Open Source / Commerce / Cloud 2.4.4 — 2.4.8
- PHP 8.1, 8.2, 8.3, 8.4

---

## Support

For all questions, bug reports, or feature requests:

- **Email:** kishansavaliyakb@gmail.com
- **Website:** https://kishansavaliya.com
- **WhatsApp:** +91 84012 70422
