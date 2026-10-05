<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Plugin\ThirdParty;

use Panth\PageBuilderAi\Model\Admin\AiButtonRenderer;
use Panth\PageBuilderAi\Plugin\ThirdParty\TestimonialAiPlugin;
use PHPUnit\Framework\TestCase;

class TestimonialAiPluginTest extends TestCase
{
    public function testUnavailableRendererLeavesMetaUntouched(): void
    {
        $renderer = $this->createMock(AiButtonRenderer::class);
        $renderer->method('isAvailable')->willReturn(false);
        $renderer->expects($this->never())->method('buildContainerMeta');

        $meta = ['general' => ['children' => ['x' => []]]];

        $this->assertSame($meta, (new TestimonialAiPlugin($renderer))->afterGetMeta(new \stdClass(), $meta));
    }

    public function testContainerIsInjectedWithFieldConfiguration(): void
    {
        $captured = [];
        $renderer = $this->createMock(AiButtonRenderer::class);
        $renderer->method('isAvailable')->willReturn(true);
        $renderer->expects($this->once())->method('buildContainerMeta')->willReturnCallback(
            static function (...$args) use (&$captured) {
                $captured = $args;
                return ['container' => true];
            }
        );

        $result = (new TestimonialAiPlugin($renderer))->afterGetMeta(new \stdClass(), ['general' => ['children' => ['x' => []]]]);

        $this->assertSame(['x' => [], 'ai_generate_container' => ['container' => true]], $result['general']['children']);
        [$entityType, $idField, $storeField, $fieldMap, $perField, $suffix, $help, $sortOrder] = $captured;
        $this->assertSame('testimonial', $entityType);
        $this->assertSame('testimonial_id', $idField);
        $this->assertSame('', $storeField);
        $this->assertSame('testimonial', $suffix);
        $this->assertSame(5, $sortOrder);
        $this->assertNotSame('', $help);
        $this->assertSame(['content', 'short_content', 'title'], array_keys($fieldMap));
        $this->assertSame(array_keys($fieldMap), array_keys($perField));
        foreach ($perField as $field => $config) {
            $this->assertSame($field, $config['field']);
            $this->assertNotSame('', $config['label']);
            $this->assertNotSame('', $config['prompt']);
        }
    }
}
