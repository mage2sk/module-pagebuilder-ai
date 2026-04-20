<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Upgrade the seeded AI prompt templates to "master-grade" 2026 Magento prompts.
 *
 * The first installer (`InstallDefaultAiPrompts`) seeds baseline prompts; this
 * patch rewrites the prompt_template of each default row (matched by `name`)
 * with a longer, much more specific prompt that:
 *
 *   1. Explains exactly what output shape the LLM must return and nothing else.
 *   2. Encodes 2026 SEO best practices (E-E-A-T, helpful content, search intent).
 *   3. Gives explicit Magento PageBuilder data-content-type guidance for
 *      PageBuilder-entity prompts so the response becomes editable blocks.
 *   4. Lists negative examples ("DO NOT output HTML in meta_title") so the LLM
 *      self-polices instead of relying on client-side parsing.
 *
 * Idempotent: we UPDATE existing rows by name. Any row an admin has customised
 * (i.e. where prompt_template ≠ the previous baseline and ≠ our new value) is
 * left alone — we only upgrade rows that still hold the v1 text.
 */
class UpgradeDefaultAiPromptsV2 implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();
        $connection = $this->moduleDataSetup->getConnection();
        $table      = $this->moduleDataSetup->getTable('panth_seo_ai_prompt');

        if (!$connection->isTableExists($table)) {
            $this->moduleDataSetup->endSetup();
            return $this;
        }

        foreach ($this->getMasterPrompts() as $name => $template) {
            try {
                $connection->update(
                    $table,
                    [
                        'prompt_template' => $template,
                        'updated_at'      => date('Y-m-d H:i:s'),
                    ],
                    ['name = ?' => $name]
                );
            } catch (\Throwable) {
                // Row may not exist yet (fresh install executes v1 first, then us).
                // Nothing to do.
            }
        }

        $this->moduleDataSetup->endSetup();
        return $this;
    }

    /**
     * @return array<string, string>
     */
    private function getMasterPrompts(): array
    {
        return [
            'Default Product Meta' => <<<'P'
You are a senior e-commerce SEO copywriter producing Magento 2 product meta
fields. Your output is consumed by a parser that expects STRICT JSON and
nothing else.

=== PRODUCT CONTEXT ===
Name: {{name}}
SKU: {{sku}}
Price: {{price}}
Brand: {{brand}}
Category: {{category}}
Short description: {{short_description}}
Full description: {{description}}

=== OUTPUT CONTRACT ===
Return ONLY this JSON shape — no prose, no code fences, no leading/trailing
characters:
{"meta_title":"...","meta_description":"..."}

=== FIELD RULES ===
meta_title  (50–60 characters, hard cap 60):
  • Put the primary product keyword in the first 30 characters.
  • Include the brand once when available.
  • No ALL CAPS, no emoji, no clickbait ("The Best…"), no brackets/pipes
    unless semantically meaningful.
  • Must read naturally if spoken aloud.

meta_description (140–156 characters, hard cap 160):
  • Lead with the core buyer benefit (what the product does for the user).
  • Include one concrete differentiator (material, size, warranty, speed).
  • End with a soft CTA ("Shop now", "Order today", "See the collection").
  • Do NOT stuff keywords or repeat the title verbatim.

=== FORBIDDEN ===
HTML tags, markdown, emoji, escaped newlines inside values, double quotes
inside values (use single quotes or dashes instead), trailing explanations.

Good example:
{"meta_title":"Acme Pro Chef's Knife 8\" | Sharp German Steel","meta_description":"Razor-sharp 8-inch chef's knife in full-tang German steel. Balanced grip for daily prep. Lifetime warranty. Shop the Acme Pro line today."}
P,

            'Default Category Meta' => <<<'P'
You are an SEO strategist writing Magento 2 category meta fields. Output is
STRICT JSON — a downstream parser will reject anything else.

=== CATEGORY CONTEXT ===
Name: {{name}}
Parent: {{parent}}
Store: {{store_name}}
Existing description: {{description}}

=== OUTPUT CONTRACT ===
Return ONLY this JSON, no code fences or prose:
{"meta_title":"...","meta_description":"..."}

=== RULES ===
meta_title (50–60 chars):
  • Pattern: "<Category> — <benefit/range> | <Store>" or similar.
  • Lead with the category keyword, not the store.
  • Prefer commercial-intent verbs (Shop, Explore, Discover).

meta_description (140–156 chars):
  • Signal range ("100+", "all sizes", "top brands") when honest.
  • One trust element (free shipping, easy returns, expert support, warranty).
  • Close with a CTA tuned to browse intent, not click-bait.
  • Avoid superlatives that feel unverifiable.

FORBIDDEN: HTML, markdown, emoji, superlatives like "Best ever", vague
filler ("Click here"). Do not invent facts not in the context.
P,

            'Default CMS Meta' => <<<'P'
You are writing SEO meta for a Magento 2 CMS page. The parser expects STRICT
JSON — never any HTML, markdown, or extra prose.

=== PAGE CONTEXT ===
Title: {{title}}
URL key: {{identifier}}
Content excerpt: {{content}}
Store: {{store_name}}

=== OUTPUT CONTRACT ===
Return ONLY this JSON, no code fences:
{"meta_title":"...","meta_description":"..."}

=== RULES ===
meta_title (50–60 chars):
  • Match search intent of the page (informational / navigational / transactional).
  • Primary keyword in the first 30 chars.
  • No clickbait. No emoji. No ALL CAPS.
  • If the page is support/policy (e.g. Shipping, Returns, Privacy), phrase the
    title as the user's actual query (e.g. "Shipping Policy & Delivery Times").

meta_description (140–156 chars):
  • Lead with what the visitor will learn or get from the page.
  • One concrete data point when available (timeframes, prices, eligibility).
  • Use second-person voice when natural ("Learn how you can…").
  • Close with a soft next step ("Read the full policy.", "See all options.").

=== FORBIDDEN ===
HTML tags, Markdown, emoji, invented facts not in the page context, any form
of <div> / <p> / <br> / <span> — downstream input fields will render literal
tags as text. Keep each value on a single line.
P,

            'Generic All-Entity Meta' => <<<'P'
You produce SEO meta for a generic Magento 2 entity (unknown type). Output
is STRICT JSON only.

Entity name: {{name}}
Entity type: {{entity_type}}
Description: {{description}}
URL: {{url}}

Return EXACTLY: {"meta_title":"...","meta_description":"..."}
  • meta_title: 50–60 chars, primary keyword early, no emoji, no HTML.
  • meta_description: 140–156 chars, 1 concrete benefit + 1 call-to-action.
  • No code fences. No leading sentence. No trailing notes.
P,

            'Default PageBuilder Content' => <<<'P'
You are generating content for Magento 2 PageBuilder. The response MUST be
HTML that PageBuilder can parse into editable content types (rows, columns,
headings, text, buttons, dividers, images). Failing this contract makes the
content render as one inert "HTML Code" block that cannot be edited.

=== BRIEF ===
Purpose: {{purpose}}
Store: {{store_name}}
Audience: {{audience}}
Key message: {{description}}

=== CONTRACT ===
Return ONLY HTML — no markdown, no code fences, no explanations. Every top-
level element must be a PageBuilder row:

  <div data-content-type="row" data-appearance="contained" data-element="main">
    <div data-content-type="column-group" data-grid-size="12" data-element="main">
      <div data-content-type="column" data-appearance="full-height"
           data-background-images="{}" data-element="main"
           style="justify-content: flex-start; display: flex; flex-direction: column;">
        <!-- content types here -->
      </div>
    </div>
  </div>

Allowed inner content types (one per block):
  <h2 data-content-type="heading" data-element="main">…</h2>
  <div data-content-type="text" data-appearance="default" data-element="main"><p>…</p></div>
  <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main">
    <div data-content-type="button-item" data-appearance="default" data-element="main">
      <a href="/…" data-element="link" class="pagebuilder-button-primary">Label</a>
    </div>
  </div>
  <div data-content-type="divider" data-appearance="default" data-element="main"><hr data-element="line"/></div>
  <figure data-content-type="image" data-appearance="full-width" data-element="main">
    <img src="{{media-url}}" alt="…" data-element="desktop_image"/>
  </figure>

=== SEO / STRUCTURE RULES (2026) ===
  • Open with exactly one h2 headline (the page already has one h1).
  • Use semantic headings: only one h2 per row; h3 for sub-sections.
  • Write to search intent first, keywords second.
  • Include one concrete stat / proof point per 300 words when credible.
  • Every link must have descriptive text (no "click here", no "read more"
    without the what).

=== FORBIDDEN ===
<script>, <iframe>, <form>, inline onclick/onerror handlers, javascript:
or data: URLs in href, external stylesheets, emojis, placeholder text
(lorem ipsum). If the brief cannot be fulfilled safely, return a single row
with a text block that politely explains why.
P,

            'FAQ Section Generator' => <<<'P'
You are producing a PageBuilder FAQ section that also satisfies FAQPage
structured data. Output is HTML only (no JSON), matching Magento PageBuilder
content-type contracts so each Q and each A remains editable.

=== BRIEF ===
Topic: {{name}}
Audience: {{audience}}
Supporting notes: {{description}}

=== CONTRACT ===
Return ONLY HTML. Wrap the whole block in a single PageBuilder row:

  <div data-content-type="row" data-appearance="contained" data-element="main">
    <div data-content-type="column-group" data-grid-size="12" data-element="main">
      <div data-content-type="column" data-appearance="full-height"
           data-background-images="{}" data-element="main"
           style="justify-content: flex-start; display: flex; flex-direction: column;">
        <h2 data-content-type="heading" data-element="main">Frequently Asked Questions</h2>
        <!-- Five Q/A pairs. For EACH pair: -->
        <h3 data-content-type="heading" data-element="main">Question text?</h3>
        <div data-content-type="text" data-appearance="default" data-element="main">
          <p>Answer in 2–4 sentences, factual, no marketing fluff.</p>
        </div>
        <!-- repeat h3 + text for the other 4 pairs -->
      </div>
    </div>
  </div>

=== RULES ===
  • Exactly 5 question/answer pairs.
  • Questions must read like real user queries (no marketing rhetoric).
  • Answers: 2–4 short sentences; concrete facts only; no emoji.
  • No nested content types inside the text blocks other than <p>, <strong>,
    <em>, <a>.
  • No <script>, no inline handlers, no external stylesheets.
P,
        ];
    }

    public static function getDependencies(): array
    {
        return [
            InstallDefaultAiPrompts::class,
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
