<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\Generator;

use Panth\PageBuilderAi\Api\AiGeneratorInterface;

class NullAdapter implements AiGeneratorInterface
{
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
