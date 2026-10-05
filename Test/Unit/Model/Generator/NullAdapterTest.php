<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model\Generator;

use Panth\PageBuilderAi\Model\Generator\NullAdapter;
use PHPUnit\Framework\TestCase;

class NullAdapterTest extends TestCase
{
    public function testAlwaysReturnsEmptyResult(): void
    {
        $adapter = new NullAdapter();

        $this->assertSame(
            ['title' => '', 'description' => '', 'confidence' => 0.0],
            $adapter->generate(['entity_type' => 'product'], ['meta_title'], ['x' => 1])
        );
        $this->assertSame('null', $adapter->getProvider());
        $this->assertSame(0, $adapter->getLastUsageTokens());
    }
}
