<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Block\Adminhtml;

use Panth\PageBuilderAi\Block\Adminhtml\AiKnowledge\Edit as KnowledgeEdit;
use Panth\PageBuilderAi\Block\Adminhtml\AiPrompt\Edit as PromptEdit;
use Panth\PageBuilderAi\Block\Adminhtml\GenericSaveAndContinueButton;
use Panth\PageBuilderAi\Block\Adminhtml\GenericSaveButton;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GenericSaveButtonTest extends TestCase
{
    #[DataProvider('buttonProvider')]
    public function testButtonTriggersFormEvent(string $class, string $label, string $event, int $sortOrder): void
    {
        $data = (new $class())->getButtonData();

        $this->assertSame($label, (string)$data['label']);
        $this->assertSame(['event' => $event], $data['data_attribute']['mage-init']['button']);
        $this->assertSame($sortOrder, $data['sort_order']);
        $this->assertStringContainsString('save', $data['class']);
    }

    public static function buttonProvider(): array
    {
        return [
            [GenericSaveButton::class, 'Save', 'save', 90],
            [KnowledgeEdit\SaveButton::class, 'Save', 'save', 90],
            [PromptEdit\SaveButton::class, 'Save', 'save', 90],
            [GenericSaveAndContinueButton::class, 'Save and Continue Edit', 'saveAndContinueEdit', 80],
            [KnowledgeEdit\SaveAndContinueButton::class, 'Save and Continue Edit', 'saveAndContinueEdit', 80],
            [PromptEdit\SaveAndContinueButton::class, 'Save and Continue Edit', 'saveAndContinueEdit', 80],
        ];
    }

    public function testPrimarySaveButtonIsTheFormSubmitRole(): void
    {
        $data = (new GenericSaveButton())->getButtonData();

        $this->assertSame('save primary', $data['class']);
        $this->assertSame('save', $data['data_attribute']['form-role']);
    }
}
