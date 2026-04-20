<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Seeds default AI prompt templates for product, category, CMS page and PageBuilder generation.
 *
 * Idempotent: each prompt is keyed by its unique `name` column, and a SELECT is performed
 * before INSERT so re-running setup:upgrade will never create duplicates. Rows that were
 * edited by an administrator are left untouched.
 */
class InstallDefaultAiPrompts implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('panth_seo_ai_prompt');

        if (!$connection->isTableExists($table)) {
            $this->moduleDataSetup->endSetup();
            return $this;
        }

        foreach ($this->getDefaultPrompts() as $row) {
            $existing = $connection->fetchOne(
                $connection->select()
                    ->from($table, 'prompt_id')
                    ->where('name = ?', $row['name'])
                    ->limit(1)
            );
            if (!$existing) {
                $connection->insert($table, $row);
            }
        }

        $this->moduleDataSetup->endSetup();
        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getDefaultPrompts(): array
    {
        return [
            [
                'name'            => 'Default Product Meta',
                'entity_type'     => 'product',
                'is_default'      => 1,
                'is_active'       => 1,
                'sort_order'      => 10,
                'prompt_template' => 'You are an expert SEO copywriter for an e-commerce store. Generate an optimized meta title and meta description for the following product.

PRODUCT DETAILS:
- Name: {{name}}
- SKU: {{sku}}
- Price: {{price}}
- Brand: {{brand}}
- Category: {{category}}
- Short Description: {{short_description}}
- Full Description: {{description}}

RULES:
1. Meta Title: 50-60 characters, include the product name as the primary keyword and the brand when available.
2. Meta Description: 140-156 characters, include a clear benefit and call-to-action, mention price when meaningful.
3. Use natural language; avoid keyword stuffing and marketing fluff.
4. Do not use emoji characters.

Return ONLY valid JSON in this exact shape:
{"meta_title":"...","meta_description":"..."}',
            ],
            [
                'name'            => 'Conversion-Focused Product',
                'entity_type'     => 'product',
                'is_default'      => 0,
                'is_active'       => 1,
                'sort_order'      => 20,
                'prompt_template' => 'You are a conversion-focused e-commerce copywriter. Write SEO meta tags that maximize click-through rate from Google search results.

PRODUCT:
- Name: {{name}}
- SKU: {{sku}}
- Price: {{price}}
- Brand: {{brand}}
- Short Description: {{short_description}}

REQUIREMENTS:
- Meta title (50-60 chars): product name + a concrete differentiator + brand. Allowed power words: Shop, Buy, Best, Premium, Official.
- Meta description (140-156 chars): include the price, a shipping or guarantee benefit, a mild urgency cue (e.g. Limited Stock), and a clear call-to-action.
- No emoji, no ALL CAPS, no clickbait.

Return ONLY valid JSON:
{"meta_title":"...","meta_description":"..."}',
            ],
            [
                'name'            => 'Minimal/Short Product',
                'entity_type'     => 'product',
                'is_default'      => 0,
                'is_active'       => 1,
                'sort_order'      => 30,
                'prompt_template' => 'Low-token SEO meta generator. Input: product "{{name}}" (SKU {{sku}}, {{price}}).

Output strictly this JSON (title 50-60 chars, description 140-156 chars, no emoji):
{"meta_title":"...","meta_description":"..."}',
            ],
            [
                'name'            => 'Product Description Generator',
                'entity_type'     => 'product',
                'is_default'      => 0,
                'is_active'       => 1,
                'sort_order'      => 40,
                'prompt_template' => 'You are a senior product copywriter. Write a structured product description for this item that can be rendered directly on a product detail page.

PRODUCT:
- Name: {{name}}
- SKU: {{sku}}
- Price: {{price}}
- Brand: {{brand}}
- Category: {{category}}
- Existing notes: {{short_description}}

Produce:
1. A one-sentence hook.
2. A short paragraph (2-3 sentences) explaining the key benefit.
3. Exactly 5 bullet features.
4. A closing call-to-action line.

Also produce SEO meta. No emoji. Return ONLY valid JSON:
{"meta_title":"...","meta_description":"...","description_html":"<p>...</p><ul><li>...</li></ul>"}',
            ],
            [
                'name'            => 'Default Category Meta',
                'entity_type'     => 'category',
                'is_default'      => 1,
                'is_active'       => 1,
                'sort_order'      => 10,
                'prompt_template' => 'You are an SEO expert for e-commerce category pages. Generate meta tags for this category listing.

CATEGORY:
- Name: {{name}}
- Parent: {{parent}}
- Store: {{store_name}}
- Description: {{description}}

RULES:
- Meta title (50-60 chars): "Shop {{name}} Online | {{store_name}}" style, include the category as primary keyword.
- Meta description (140-156 chars): highlight range, variety and a benefit such as fast shipping or deals.
- No emoji.

Return ONLY valid JSON:
{"meta_title":"...","meta_description":"..."}',
            ],
            [
                'name'            => 'Category Landing Page',
                'entity_type'     => 'category',
                'is_default'      => 0,
                'is_active'       => 1,
                'sort_order'      => 20,
                'prompt_template' => 'You are a senior SEO content strategist. Write a short landing page intro for the "{{name}}" category that will appear above the product grid.

CATEGORY:
- Name: {{name}}
- Parent: {{parent}}
- Store: {{store_name}}
- Existing description: {{description}}

Produce:
1. An H1-style headline (max 70 chars).
2. A 2-sentence intro paragraph with the primary keyword naturally included.
3. Three bullet points of buying guidance.
4. An SEO meta title (50-60 chars) and meta description (140-156 chars).

No emoji. Return ONLY valid JSON:
{"meta_title":"...","meta_description":"...","headline":"...","intro_html":"<p>...</p><ul><li>...</li></ul>"}',
            ],
            [
                'name'            => 'Default CMS Meta',
                'entity_type'     => 'cms_page',
                'is_default'      => 1,
                'is_active'       => 1,
                'sort_order'      => 10,
                'prompt_template' => 'Generate SEO-optimized meta title and meta description for this informational CMS page.

PAGE:
- Title: {{name}}
- URL key: {{url_key}}
- Content summary: {{description}}

RULES:
- Meta title: 50-60 characters, informative, keyword-focused.
- Meta description: 140-156 characters, summarize the page purpose and the visitor benefit.
- No emoji, no clickbait.

Return ONLY valid JSON:
{"meta_title":"...","meta_description":"..."}',
            ],
            [
                'name'            => 'About Us Generator',
                'entity_type'     => 'cms_page',
                'is_default'      => 0,
                'is_active'       => 1,
                'sort_order'      => 20,
                'prompt_template' => 'You are a senior brand copywriter. Write an "About Us" CMS page for the store "{{store_name}}".

INPUTS:
- Store name: {{store_name}}
- Existing intro: {{description}}
- Primary audience: {{audience}}

Produce:
1. An H1 headline.
2. A 2-paragraph brand story (mission, values, what makes the store different).
3. A 3-bullet list of commitments (quality, support, shipping, etc.).
4. SEO meta title (50-60 chars) and meta description (140-156 chars).

No emoji. Return ONLY valid JSON:
{"meta_title":"...","meta_description":"...","content_html":"<h1>...</h1><p>...</p><ul><li>...</li></ul>"}',
            ],
            [
                'name'            => 'Default PageBuilder Content',
                'entity_type'     => 'pagebuilder',
                'is_default'      => 1,
                'is_active'       => 1,
                'sort_order'      => 10,
                'prompt_template' => 'You are a Magento PageBuilder content designer. Produce a single CMS section of valid HTML that will be injected into a Magento PageBuilder row.

BRIEF:
- Purpose: {{purpose}}
- Store: {{store_name}}
- Target audience: {{audience}}
- Key message: {{description}}

REQUIREMENTS:
1. Use semantic HTML only: h2, h3, p, ul, li, a, strong, em. No inline styles. No scripts.
2. Exactly one h2 headline.
3. 1-2 short paragraphs plus a 3-5 item bullet list.
4. One clear text call-to-action link.
5. No emoji.

Return ONLY valid JSON:
{"meta_title":"...","meta_description":"...","content_html":"<h2>...</h2><p>...</p><ul><li>...</li></ul>"}',
            ],
            [
                'name'            => 'FAQ Section Generator',
                'entity_type'     => 'pagebuilder',
                'is_default'      => 0,
                'is_active'       => 1,
                'sort_order'      => 20,
                'prompt_template' => 'You are an SEO content writer producing a FAQ section for a PageBuilder row. Google should be able to pick up the Q&A pairs as FAQ rich results.

CONTEXT:
- Topic: {{name}}
- Audience: {{audience}}
- Supporting notes: {{description}}

REQUIREMENTS:
1. Exactly 5 question/answer pairs.
2. Each question must be natural spoken language.
3. Each answer: 2-4 sentences, factual, no emoji, no marketing fluff.
4. Output HTML uses h3 for the question and p for the answer.
5. Also produce SEO meta for the section.

Return ONLY valid JSON:
{"meta_title":"...","meta_description":"...","content_html":"<h3>Q1</h3><p>A1</p><h3>Q2</h3><p>A2</p>"}',
            ],
            [
                'name'            => 'Generic All-Entity Meta',
                'entity_type'     => 'all',
                'is_default'      => 0,
                'is_active'       => 1,
                'sort_order'      => 99,
                'prompt_template' => 'You are an SEO specialist. Generate a meta title (50-60 chars) and meta description (140-156 chars) for the following entity.

ENTITY:
- Name: {{name}}
- Type: {{entity_type}}
- Description: {{description}}
- URL: {{url}}

Rules: natural language, one primary keyword, no emoji, no clickbait.

Return ONLY valid JSON:
{"meta_title":"...","meta_description":"..."}',
            ],
        ];
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
