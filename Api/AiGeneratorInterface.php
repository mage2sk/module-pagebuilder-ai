<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Api;

/**
 * Contract for AI content-generation adapters (OpenAI / Claude / Null).
 *
 * Replaces the legacy Panth\AdvancedSEO\Api\MetaGeneratorInterface. Unlike the
 * old contract, this interface is shaped around general content generation
 * (meta fields, full PageBuilder HTML, per-field copy) not strictly meta SEO.
 */
interface AiGeneratorInterface
{
    public const FIELD_TITLE       = 'title';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_KEYWORDS    = 'keywords';

    /**
     * Generate content fields for a single entity.
     *
     * @param array<string,mixed> $context   Entity context (name, description, attributes, custom_prompt, images ...)
     * @param string[]            $fields    Fields to generate
     * @param array<string,mixed> $options   Adapter options (locale, tone, maxTokens ...)
     * @return array<string,mixed>           Map of field => generated text (+ optional confidence, error, etc.)
     */
    public function generate(array $context, array $fields = [], array $options = []): array;

    /**
     * @return string Provider identifier ("openai", "claude", "null").
     */
    public function getProvider(): string;

    /**
     * @return int Approximate tokens consumed by the last generate() call.
     */
    public function getLastUsageTokens(): int;
}
