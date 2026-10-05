<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model\Config\Source;

use Panth\PageBuilderAi\Model\Config\Source\MetaRobots;
use PHPUnit\Framework\TestCase;

class MetaRobotsTest extends TestCase
{
    public function testOptionsCoverAllDirectiveCombinations(): void
    {
        $source = new MetaRobots();

        $options = $source->getAllOptions();

        $this->assertSame(
            ['', 'INDEX,FOLLOW', 'NOINDEX,FOLLOW', 'INDEX,NOFOLLOW', 'NOINDEX,NOFOLLOW'],
            array_column($options, 'value')
        );
        $this->assertSame($options, $source->toOptionArray());
        $this->assertSame('NOINDEX,NOFOLLOW', (string)$options[4]['label']);
    }

    public function testOptionTextLookup(): void
    {
        $source = new MetaRobots();

        $this->assertSame('INDEX,NOFOLLOW', (string)$source->getOptionText('INDEX,NOFOLLOW'));
        $this->assertFalse($source->getOptionText('BOGUS'));
    }
}
