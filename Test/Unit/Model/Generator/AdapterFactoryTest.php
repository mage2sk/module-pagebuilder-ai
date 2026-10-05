<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Model\Generator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Panth\PageBuilderAi\Model\Generator\AdapterFactory;
use Panth\PageBuilderAi\Model\Generator\ClaudeAdapter;
use Panth\PageBuilderAi\Model\Generator\NullAdapter;
use Panth\PageBuilderAi\Model\Generator\OpenAiAdapter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AdapterFactoryTest extends TestCase
{
    #[DataProvider('providerMap')]
    public function testConfiguredProviderIsResolvedOnce(string $provider, string $expectedClass): void
    {
        $scope = $this->createMock(ScopeConfigInterface::class);
        $scope->expects($this->once())
            ->method('getValue')
            ->with('panth_pagebuilderai/ai/provider', 'store')
            ->willReturn($provider);

        $factory = new AdapterFactory($scope, $this->objectManager($this->adapters()));

        $this->assertSame($expectedClass, $factory->getProvider());
        $this->assertSame(['title' => $expectedClass], $factory->generate(['x' => 1], ['f'], ['o' => 1]));
        $this->assertSame(strlen($expectedClass), $factory->getLastUsageTokens());
    }

    public static function providerMap(): array
    {
        return [
            ['claude', ClaudeAdapter::class],
            ['openai', OpenAiAdapter::class],
            ['', NullAdapter::class],
            ['unknown', NullAdapter::class],
        ];
    }

    public function testExplicitLookupBypassesConfiguration(): void
    {
        $scope = $this->createMock(ScopeConfigInterface::class);
        $scope->expects($this->never())->method('getValue');
        $adapters = $this->adapters();
        $factory = new AdapterFactory($scope, $this->objectManager($adapters));

        $this->assertSame($adapters[OpenAiAdapter::class], $factory->get('openai'));
        $this->assertSame($adapters[ClaudeAdapter::class], $factory->get('claude'));
        $this->assertSame($adapters[NullAdapter::class], $factory->get('other'));
    }

    private function adapters(): array
    {
        $result = [];
        foreach ([ClaudeAdapter::class, OpenAiAdapter::class, NullAdapter::class] as $class) {
            $adapter = $this->createStub($class);
            $adapter->method('getProvider')->willReturn($class);
            $adapter->method('generate')->willReturn(['title' => $class]);
            $adapter->method('getLastUsageTokens')->willReturn(strlen($class));
            $result[$class] = $adapter;
        }
        return $result;
    }

    private function objectManager(array $adapters): ObjectManagerInterface
    {
        $objectManager = $this->createStub(ObjectManagerInterface::class);
        $objectManager->method('get')->willReturnCallback(static fn ($class) => $adapters[$class]);
        return $objectManager;
    }
}
