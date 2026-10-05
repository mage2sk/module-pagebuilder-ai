<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml;

use Panth\PageBuilderAi\Controller\Adminhtml as C;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ListingPagesTest extends TestCase
{
    use BackendActionStubs;

    #[DataProvider('pageProvider')]
    public function testPageHighlightsMenuAndSetsTitle(string $class, string $menu, string $title): void
    {
        $pageFactory = $this->pageFactory();
        $controller = new $class($this->context($this->request([], false)), $pageFactory);

        $this->assertSame($pageFactory->create(), $controller->execute());
        $this->assertSame($menu, $this->activeMenu);
        $this->assertSame([$title], $this->titles);
    }

    public static function pageProvider(): array
    {
        $p = 'Panth_PageBuilderAi::';
        return [
            [C\AiKnowledge\Index::class, $p . 'ai_knowledge', 'AI Knowledge Base'],
            [C\AiPrompt\Index::class, $p . 'ai_prompts', 'AI Prompts'],
            [C\AiSettings\Index::class, $p . 'ai_jobs', 'AI Settings'],
            [C\AiSettings\Jobs::class, $p . 'ai_jobs', 'AI Generation Jobs'],
            [C\RequestLog\Index::class, $p . 'ai_request_logs', 'AI Request Logs'],
        ];
    }
}
