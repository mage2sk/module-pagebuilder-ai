<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Plugin\ThirdParty;

use Panth\PageBuilderAi\Model\Admin\AiButtonRenderer;
use Panth\PageBuilderAi\Plugin\ThirdParty\DynamicFormAiPlugin;
use PHPUnit\Framework\TestCase;

class DynamicFormAiPluginTest extends TestCase
{
    public function testUnavailableRendererLeavesMetaUntouched(): void
    {
        $renderer = $this->createMock(AiButtonRenderer::class);
        $renderer->method('isAvailable')->willReturn(false);
        $renderer->expects($this->never())->method('buildContainerMeta');

        $meta = ['general' => ['children' => ['x' => []]]];

        $this->assertSame($meta, (new DynamicFormAiPlugin($renderer))->afterGetMeta(new \stdClass(), $meta));
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

        $result = (new DynamicFormAiPlugin($renderer))->afterGetMeta(new \stdClass(), ['general' => ['children' => ['x' => []]]]);

        $this->assertSame(['x' => [], 'ai_generate_container' => ['container' => true]], $result['general']['children']);
        [$entityType, $idField, $storeField, $fieldMap, $perField, $suffix, $help, $sortOrder] = $captured;
        $this->assertSame('dynamic_form', $entityType);
        $this->assertSame('form_id', $idField);
        $this->assertSame('store_id', $storeField);
        $this->assertSame('dynform', $suffix);
        $this->assertSame(5, $sortOrder);
        $this->assertNotSame('', $help);
        $this->assertSame(['description', 'content_above', 'content_below', 'success_message', 'meta_title', 'meta_description', 'meta_keywords'], array_keys($fieldMap));
        $this->assertSame(array_keys($fieldMap), array_keys($perField));
        foreach ($perField as $field => $config) {
            $this->assertSame($field, $config['field']);
            $this->assertNotSame('', $config['label']);
            $this->assertNotSame('', $config['prompt']);
        }
    }
}
