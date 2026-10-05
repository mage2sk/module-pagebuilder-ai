<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Controller\Adminhtml\RequestLog;

use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Panth\PageBuilderAi\Controller\Adminhtml\RequestLog\View;
use Panth\PageBuilderAi\Test\Unit\Support\BackendActionStubs;
use Panth\PageBuilderAi\Test\Unit\Support\DbStubs;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    use BackendActionStubs;
    use DbStubs;

    private const DIR = 'panth_pagebuilderai/request-log/b1/';

    public function testInvalidIdRedirects(): void
    {
        $this->controller(['log_id' => 0], $this->connectionStub())->execute();

        $this->assertSame(['Invalid log id.'], $this->messagesOfType('error'));
        $this->assertSame(['*/*/', []], $this->redirect);
    }

    public function testMissingEntryRedirects(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn(false);

        $this->controller(['log_id' => 4], $connection)->execute();

        $this->assertSame(['Log entry not found.'], $this->messagesOfType('error'));
    }

    public function testOnlySafeStoredImagesAreInlined(): void
    {
        $row = [
            'log_id' => 4,
            'images_json' => json_encode([
                self::DIR . '0.png',
                self::DIR . '1.webp',
                self::DIR . '../../../env.php',
                'elsewhere/2.png',
                self::DIR . '3.php',
                self::DIR . '4.gif',
                7,
            ]),
        ];
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn($row);
        $reader = $this->createStub(ReadInterface::class);
        $reader->method('isFile')->willReturnCallback(static fn ($path) => $path !== self::DIR . '4.gif');
        $reader->method('readFile')->willReturnCallback(static fn ($path) => 'bytes:' . basename($path));
        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getDirectoryRead')->willReturn($reader);
        $block = new DataObject();

        $this->controller(['log_id' => '4'], $connection, $block, $filesystem)->execute();

        $this->assertSame(['AI Request Log #4'], $this->titles);
        $this->assertSame($row, $block->getData('log_row'));
        $this->assertSame('https://media.test/', $block->getData('media_base_url'));
        $this->assertSame([
            self::DIR . '0.png' => 'data:image/png;base64,' . base64_encode('bytes:0.png'),
            self::DIR . '1.webp' => 'data:image/webp;base64,' . base64_encode('bytes:1.webp'),
        ], $block->getData('image_data'));
    }

    public function testRowWithoutImagesYieldsEmptyImageData(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn(['log_id' => 4, 'images_json' => null]);
        $block = new DataObject();

        $this->controller(['log_id' => 4], $connection, $block)->execute();

        $this->assertSame([], $block->getData('image_data'));
    }

    public function testUnreadableDirectoryYieldsEmptyImageData(): void
    {
        $connection = $this->connectionStub();
        $connection->method('fetchRow')->willReturn(['log_id' => 4, 'images_json' => json_encode([self::DIR . '0.png'])]);
        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getDirectoryRead')->willThrowException(new \RuntimeException('denied'));
        $block = new DataObject();

        $this->controller(['log_id' => 4], $connection, $block, $filesystem)->execute();

        $this->assertSame([], $block->getData('image_data'));
    }

    private function controller(
        array $params,
        AdapterInterface $connection,
        mixed $block = false,
        ?Filesystem $filesystem = null
    ): View {
        $store = $this->createStub(Store::class);
        $store->method('getBaseUrl')->willReturn('https://media.test/');
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        return new View(
            $this->context($this->request($params, false)),
            $this->pageFactory($block),
            $this->resourceStub($connection),
            $storeManager,
            $filesystem ?? $this->createStub(Filesystem::class)
        );
    }
}
