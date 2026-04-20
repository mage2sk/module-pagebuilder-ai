<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\Generator;

use Panth\PageBuilderAi\Api\AiGeneratorInterface;

/**
 * Safe default generator — returns empty output. Used when no AI provider is
 * configured so the pipeline never crashes.
 */
class NullAdapter implements AiGeneratorInterface
{
    /**
     * @param array<string,mixed> $context
     * @return array{title:string, description:string, confidence:float}
     */
    public function generate(array $context, array $fields = [], array $options = []): array
    {
        return ['title' => '', 'description' => '', 'confidence' => 0.0];
    }

    public function getProvider(): string
    {
        return 'null';
    }

    public function getLastUsageTokens(): int
    {
        return 0;
    }
}
