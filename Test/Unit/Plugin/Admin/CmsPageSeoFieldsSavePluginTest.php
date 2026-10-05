<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Plugin\Admin;

use Magento\Cms\Controller\Adminhtml\Page\Save as CmsPageSaveController;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Plugin\Admin\CmsPageSeoFieldsSavePlugin;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CmsPageSeoFieldsSavePluginTest extends TestCase
{
    use DbStubs;

    private array $writes = [];

    public function testDisabledModuleSkipsPersistence(): void
    {
        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($this->never())->method('getConnection');
        $result = new \stdClass();

        $plugin = new CmsPageSeoFieldsSavePlugin($resource, $this->request(['page_id' => 1]), new NullLogger(), $this->config(false));

        $this->assertSame($result, $plugin->afterExecute($this->subject(), $result));
    }

    public function testNewOverrideIsInserted(): void
    {
        $connection = $this->connection(false);

        $this->persist([
            'page_id' => 4,
            'store_id' => ['2', '3'],
            'meta_robots' => ' NOINDEX,FOLLOW ',
            'hreflang_identifier' => '',
        ], $connection);

        $this->assertSame([[
            'insert',
            [
                'robots' => 'NOINDEX,FOLLOW',
                'hreflang_identifier' => null,
                'entity_type' => 'cms_page',
                'entity_id' => 4,
                'store_id' => 2,
            ],
            null,
        ]], $this->writes);
    }

    public function testExistingOverrideIsUpdated(): void
    {
        $this->persist(['page_id' => 4, 'store_id' => '1', 'hreflang_identifier' => 'about'], $this->connection('9'));

        $this->assertSame(
            [['update', ['robots' => null, 'hreflang_identifier' => 'about'], ['override_id = ?' => 9]]],
            $this->writes
        );
    }

    public function testClearingFieldsDeletesRowWithoutOtherData(): void
    {
        $connection = $this->connection('9', [
            'override_id' => 9,
            'entity_type' => 'cms_page',
            'robots' => 'NOINDEX,NOFOLLOW',
            'meta_title' => null,
            'canonical_url' => '',
            'ai_generated' => '1',
            'flag' => '0',
        ]);

        $this->persist(['page_id' => 4], $connection);

        $this->assertSame([['delete', ['override_id = ?' => 9], null]], $this->writes);
    }

    public function testClearingFieldsKeepsRowWithOtherData(): void
    {
        $this->persist(['page_id' => 4], $this->connection('9', ['override_id' => 9, 'meta_title' => 'Kept']));

        $this->assertSame(
            [['update', ['robots' => null, 'hreflang_identifier' => null], ['override_id = ?' => 9]]],
            $this->writes
        );
    }

    public function testClearingWithoutExistingRowDoesNothing(): void
    {
        $this->persist(['page_id' => 4, 'meta_robots' => '  '], $this->connection(false));

        $this->assertSame([], $this->writes);
    }

    public function testNewPageIsFoundByIdentifierAndStoreParam(): void
    {
        $connection = $this->connectionStub(['panth_seo_override']);
        $connection->method('fetchOne')->willReturnOnConsecutiveCalls('12', false);
        $this->record($connection);

        $this->persist(['identifier' => 'new-page', 'meta_robots' => 'INDEX,FOLLOW'], $connection, ['store' => '1']);

        $this->assertSame(12, $this->writes[0][1]['entity_id']);
        $this->assertSame(1, $this->writes[0][1]['store_id']);
    }

    public function testMissingPageIdentityOrTableSkipsWrites(): void
    {
        $this->persist(['meta_robots' => 'INDEX,FOLLOW'], $this->connection(false));
        $connection = $this->connectionStub();
        $this->record($connection);
        $this->persist(['page_id' => 4, 'meta_robots' => 'INDEX,FOLLOW'], $connection);

        $this->assertSame([], $this->writes);
    }

    public function testFailureIsLoggedAndResultReturned(): void
    {
        $connection = $this->connectionStub(['panth_seo_override']);
        $connection->method('fetchOne')->willThrowException(new \RuntimeException('db'));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('save failed'), ['error' => 'db']);
        $plugin = new CmsPageSeoFieldsSavePlugin(
            $this->resourceStub($connection),
            $this->request(['identifier' => 'x']),
            $logger,
            $this->config(true)
        );

        $this->assertSame('redirect', $plugin->afterExecute($this->subject(), 'redirect'));
    }

    private function persist(array $post, AdapterInterface $connection, array $params = []): void
    {
        $plugin = new CmsPageSeoFieldsSavePlugin(
            $this->resourceStub($connection),
            $this->request($post, $params),
            new NullLogger(),
            $this->config(true)
        );
        $this->assertSame('result', $plugin->afterExecute($this->subject(), 'result'));
    }

    private function connection(string|false $existingId, array|false $row = false): AdapterInterface
    {
        $connection = $this->connectionStub(['panth_seo_override']);
        $connection->method('fetchOne')->willReturn($existingId);
        $connection->method('fetchRow')->willReturn($row);
        $this->record($connection);
        return $connection;
    }

    private function record(AdapterInterface $connection): void
    {
        $connection->method('insert')->willReturnCallback(function ($table, array $data) {
            $this->writes[] = ['insert', $data, null];
            return 1;
        });
        $connection->method('update')->willReturnCallback(function ($table, array $data, $where) {
            $this->writes[] = ['update', $data, $where];
            return 1;
        });
        $connection->method('delete')->willReturnCallback(function ($table, $where) {
            $this->writes[] = ['delete', $where, null];
            return 1;
        });
    }

    private function request(array $post, array $params = []): Http
    {
        $request = $this->createStub(Http::class);
        $request->method('getPostValue')->willReturn($post);
        $request->method('getParam')->willReturnCallback(static fn ($key, $default = null) => $params[$key] ?? $default);
        return $request;
    }

    private function config(bool $enabled): Config
    {
        $config = $this->createStub(Config::class);
        $config->method('isEnabled')->willReturn($enabled);
        return $config;
    }

    private function subject(): CmsPageSaveController
    {
        return $this->createStub(CmsPageSaveController::class);
    }
}
