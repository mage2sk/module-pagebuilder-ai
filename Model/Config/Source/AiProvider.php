<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AiProvider implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'openai', 'label' => __('OpenAI (GPT-4o)')],
            ['value' => 'claude', 'label' => __('Anthropic Claude')],
        ];
    }
}
