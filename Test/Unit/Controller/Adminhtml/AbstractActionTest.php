<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml;

use Magento\Backend\App\AbstractAction as BackendAbstractAction;
use Magento\Framework\AuthorizationInterface;
use Panth\PageBuilderAi\Controller\Adminhtml as C;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AbstractActionTest extends TestCase
{
    #[DataProvider('controllerProvider')]
    public function testAdminResourceIsEnforced(string $class, string $resource): void
    {
        $this->assertSame($resource, constant($class . '::ADMIN_RESOURCE'));

        $controller = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        $authorization = $this->createMock(AuthorizationInterface::class);
        $authorization->expects($this->exactly(2))
            ->method('isAllowed')
            ->with($resource)
            ->willReturnOnConsecutiveCalls(true, false);
        (new \ReflectionProperty(BackendAbstractAction::class, '_authorization'))->setValue($controller, $authorization);
        $isAllowed = new \ReflectionMethod($class, '_isAllowed');

        $this->assertTrue($isAllowed->invoke($controller));
        $this->assertFalse($isAllowed->invoke($controller));
    }

    public static function controllerProvider(): array
    {
        $p = 'Panth_PageBuilderAi::';
        return [
            [C\AiGenerate\Generate::class, $p . 'ai_generate'],
            [C\Generate\Index::class, $p . 'generate'],
            [C\AiKnowledge\Delete::class, $p . 'ai_knowledge'],
            [C\AiKnowledge\Edit::class, $p . 'ai_knowledge'],
            [C\AiKnowledge\Import::class, $p . 'ai_knowledge'],
            [C\AiKnowledge\Index::class, $p . 'ai_knowledge'],
            [C\AiKnowledge\NewAction::class, $p . 'ai_knowledge'],
            [C\AiKnowledge\Save::class, $p . 'ai_knowledge'],
            [C\AiPrompt\Delete::class, $p . 'ai_prompts'],
            [C\AiPrompt\Edit::class, $p . 'ai_prompts'],
            [C\AiPrompt\Index::class, $p . 'ai_prompts'],
            [C\AiPrompt\NewAction::class, $p . 'ai_prompts'],
            [C\AiPrompt\Save::class, $p . 'ai_prompts'],
            [C\AiSettings\ApproveBatch::class, $p . 'ai_jobs'],
            [C\AiSettings\GenerateBatch::class, $p . 'ai_jobs'],
            [C\AiSettings\Jobs::class, $p . 'ai_jobs'],
            [C\AiSettings\ViewJob::class, $p . 'ai_jobs'],
            [C\AiSettings\Index::class, $p . 'ai_settings'],
            [C\RequestLog\Delete::class, $p . 'ai_request_logs'],
            [C\RequestLog\Index::class, $p . 'ai_request_logs'],
            [C\RequestLog\MassDelete::class, $p . 'ai_request_logs'],
            [C\RequestLog\View::class, $p . 'ai_request_logs'],
        ];
    }

    public function testEveryControllerResourceIsDeclaredInAcl(): void
    {
        $acl = simplexml_load_file(dirname(__DIR__, 4) . '/etc/acl.xml');
        $declared = array_map('strval', $acl->xpath('//resource/@id'));

        $resources = array_unique(array_column(self::controllerProvider(), 1));
        $resources[] = C\AbstractAction::ADMIN_RESOURCE;
        foreach ($resources as $resource) {
            $this->assertContains($resource, $declared);
        }
    }
}
