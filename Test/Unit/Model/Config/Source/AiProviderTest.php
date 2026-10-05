<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model\Config\Source;

use Panth\PageBuilderAi\Model\Config\Source\AiProvider;
use PHPUnit\Framework\TestCase;

class AiProviderTest extends TestCase
{
    public function testOptionValuesMatchAdapterCodes(): void
    {
        $options = (new AiProvider())->toOptionArray();

        $this->assertSame(['openai', 'claude', 'null'], array_column($options, 'value'));
        foreach ($options as $option) {
            $this->assertNotSame('', (string)$option['label']);
        }
    }
}
