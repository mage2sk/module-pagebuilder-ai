# Changelog

All notable changes to this extension are documented here. The format
is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.1.5]

### Added
- **AI Dashboard menu link** — *Panth Infotech → AI Dashboard* pointing
  to `/panth_pagebuilderai/aisettings/index`. Matching ACL resource
  `Panth_PageBuilderAi::ai_settings` added.
- **AI button injection on native Magento product / category / CMS-page
  forms** — ported 4 admin plugins from AdvancedSEO:
  `Plugin/Admin/{ProductSeoFields, CategorySeoFields, CmsPageSeoFields,
  CmsPageSeoFieldsSave}Plugin.php`. Registered in
  `etc/adminhtml/di.xml` on `Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider`,
  `Magento\Catalog\Model\Category\DataProvider`,
  `Magento\Cms\Model\Page\DataProvider`, and
  `Magento\Cms\Controller\Adminhtml\Page\Save`. AI buttons now appear
  next to *Meta Title / Meta Description / Meta Keywords* fields on:
  - Catalog → Products → {edit product} → Search Engine Optimization
  - Catalog → Categories → {edit category} → Search Engine Optimization
  - Content → Pages → {edit CMS page} → Search Engine Optimization
- **`Model/Config/Source/MetaRobots.php`** ported — required by the
  SEO-fields plugins.

### Fixed
- Default AI prompts (11) and AI knowledge base entries (506) weren't
  seeded on initial 1.1.0 install because the data patches ran BEFORE
  the AI tables existed (patch-dependency ordering). Re-running
  `setup:upgrade` with patch_list entries cleared now seeds correctly.
  Documentation note: if a store upgrading from 1.1.x sees 0 prompts,
  run `DELETE FROM patch_list WHERE patch_name LIKE 'Panth\\PageBuilderAi\\Setup\\Patch\\Data\\Install%'; bin/magento setup:upgrade`.

## [1.1.4]

### Fixed
- **`AiPrompt/Edit/*` and `AiKnowledge/Edit/*` button classes were never
  ported in 1.1.0.** The UI-component XMLs referenced
  `Panth\PageBuilderAi\Block\Adminhtml\{AiPrompt,AiKnowledge}\Edit\{BackButton,SaveButton,SaveAndContinueButton}`,
  but the actual PHP classes stayed in AdvancedSEO. Every attempt to
  open the edit form 500'd with `ReflectionException: Class ... does
  not exist`. Copied all 6 classes from
  `vendor/mage2kishan/module-advanced-seo/Block/Adminhtml/` with the
  namespace rewritten from `Panth\AdvancedSEO` to `Panth\PageBuilderAi`.

### Verified
- `/panth_pagebuilderai/aiprompt/{new,edit}` → 200
- `/panth_pagebuilderai/aiknowledge/{new,edit}` → 200
- `/panth_pagebuilderai/aisettings/{index,jobs}` → 200

## [1.1.3]

### Fixed
- **Default `claude_model` was `claude-sonnet-4-5-20241022`, which is not
  a real Anthropic model ID** (mixes Claude 4.5 naming with an October
  2024 date that pre-dates Claude 4). Result: the toolbar AI call failed
  with `{"success":false,"message":"model: claude-sonnet-4-5-20241022"}`
  straight from the Anthropic error response. Changed the default in
  `etc/config.xml` to `claude-sonnet-4-6` — the current-generation
  Sonnet model as of this release. Admins can override in *Stores →
  Configuration → Panth Extensions → PageBuilder AI → AI → Claude Model*
  if they need the Opus 4.7 flagship, Haiku 4.5 economy, or a specific
  dated snapshot.

## [1.1.2]

### Fixed
- **AiPrompt / AiKnowledge edit form 500'd on `new` with
  `ReflectionException: Class Panth\PageBuilderAi\Block\Adminhtml\GenericDeleteButton does not exist`.**
  The 1.1.0 merge copied the UI component XMLs (which reference the 4
  Generic button classes `GenericBackButton`, `GenericSaveButton`,
  `GenericSaveAndContinueButton`, `GenericDeleteButton`) but forgot to
  port the classes themselves. Added all 4 under `Block/Adminhtml/`
  with namespace rewritten from `Panth\AdvancedSEO` to `Panth\PageBuilderAi`.

## [1.1.1]

### Fixed

- **`/panth_pagebuilderai/aiprompt/new` and `/aiknowledge/new` 404'd.** The
  1.1.0 merge added the Edit/Save/Delete/Index controllers but forgot the
  `NewAction.php` that the admin grid's *Add New* button targets.
  Added both — each simply forwards to `edit` (which already renders a
  blank form when no `id` is present). No `__construct` override; they
  rely on the inherited `$resultFactory` from `AbstractAction` (redeclaring
  it as readonly in a subclass fails the "Cannot redeclare non-readonly
  property" engine check).
- **PageBuilder toolbar "Generate Full Page Content with AI" returned an
  empty body.** The 1.1.0 refactor routed `Model/AiService::generate()`
  through the ported `Model/Generator/AdapterFactory`, but those adapters
  are built for SEO-meta output (they parse the LLM response as JSON and
  expect `meta_title` / `meta_description` keys). PageBuilder wants raw
  HTML. Restored `AiService` to the original 1.0.1 inline-cURL flow so
  the toolbar keeps working. The `AdapterFactory` + adapters remain in
  place for the admin AI grids (bulk generation / prompts / knowledge)
  which DO expect structured JSON output.

## [1.1.0]

### Added — AI content generation (merged from Panth_AdvancedSEO)

- **Generator adapter layer** — `Model/Generator/{AbstractHttpAdapter,
  AdapterFactory, ClaudeAdapter, OpenAiAdapter, NullAdapter}.php`. Replaces
  the previous inline HTTP calls in `Model/AiService.php` with a proper
  factory pattern so additional providers can be plugged in without
  touching the service class. SSRF allowlist (`api.anthropic.com`,
  `api.openai.com`) preserved.
- **5 AI tables** provisioned via `etc/db_schema.xml` +
  `etc/db_schema_whitelist.json`:
  `panth_seo_ai_prompt`, `panth_seo_ai_knowledge`, `panth_seo_ai_usage`,
  `panth_seo_ai_cache`, `panth_seo_generation_job`. Table names kept as
  `panth_seo_ai_*` to preserve existing data on stores migrating from
  Panth_AdvancedSEO.
- **Admin grids & forms** at *Panth Infotech → AI Prompts / AI Knowledge
  Base / AI Generation Jobs* — 5 UI components
  (`panth_pagebuilderai_ai_prompt_listing/form`,
  `panth_pagebuilderai_ai_knowledge_listing/form`,
  `panth_pagebuilderai_generation_job_listing`), 6 admin layouts, 13
  admin controllers under `Controller/Adminhtml/{AiPrompt,AiKnowledge,
  AiSettings,AiGenerate}/`, plus `Block/Adminhtml/AiSettings.php`,
  `Block/Adminhtml/AiPrompt/Edit/PlaceholdersReference.php`, and
  `Model/Admin/AiButtonRenderer.php`.
- **Config** under `panth_pagebuilderai/ai/*` extended with 3 new fields
  migrated from AdvancedSEO: `monthly_budget` (default 1,000,000 tokens),
  `cache_ttl` (default 2,592,000 seconds / 30 days), `tone` (default
  `professional`). Existing `provider` / `openai_api_key` /
  `claude_api_key` / `openai_model` / `claude_model` / `max_tokens` /
  `temperature` unchanged.
- **Queue topology** — `etc/queue_publisher.xml`,
  `etc/queue_consumer.xml`, `etc/communication.xml` for async bulk
  generation. Consumer at `Model/Queue/BulkGenerateConsumer.php`. Topic
  renamed from `panth_seo.generate_meta` to
  `panth_pagebuilderai.generate_meta` to reflect new ownership.
- **3rd-party AI plugins** — `Plugin/ThirdParty/{BannerSlideAiPlugin,
  DynamicFormAiPlugin, FaqItemAiPlugin, TestimonialAiPlugin}.php` inject
  AI-generated content into Panth_BannerSlider, Panth_DynamicForms,
  Panth_Faq, and Panth_Testimonials admin forms when those modules are
  installed.
- **Data patches** — 1 schema patch + 2 data patches + 10 data files
  under `Setup/Data/` covering accessibility HTML, conversion copywriting,
  e-commerce, Panth modules (4 batches), PageBuilder, response format,
  and SEO technical knowledge.
- **Helper/Config.php** extended with the new constants + getters; kept
  backward-compatible with the previously-present `isEnabled()` /
  `getProvider()` / `getOpenAiApiKey()` etc.
- **`ViewModel/AiInit.php`** refactored: the previous raw-SQL read of
  `panth_seo_ai_prompt` (which coupled this module to AdvancedSEO's
  internals) now goes through `Model/ResourceModel/AiPrompt/Collection`
  — clean dependency contract, no cross-module table knowledge.
- **ACL, menu, routes** — 3 new ACL resources (`Panth_PageBuilderAi::
  ai_prompts`, `::ai_knowledge`, `::ai_jobs`), 3 new menu items under
  *Panth Infotech*, admin frontName `panth_pagebuilderai` extended with
  the new controller paths.

### Notes

- Total module size grew from 21 → ~95 files.
- The AI code is no longer coupled to `Panth_AdvancedSEO`. Stores running
  both modules should remove `Panth_AdvancedSEO::ai*` resources and the
  `panth_seo/ai/*` config group in a follow-up AdvancedSEO release.

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
