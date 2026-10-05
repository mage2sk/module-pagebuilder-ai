<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Support;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

trait DbStubs
{
    protected function selectStub(): Select
    {
        $select = $this->createStub(Select::class);
        foreach (['from', 'where', 'order', 'limit', 'group', 'join', 'joinLeft', 'orWhere', 'columns'] as $method) {
            $select->method($method)->willReturnSelf();
        }
        return $select;
    }

    /**
     * @param string[] $existingTables
     */
    protected function connectionStub(array $existingTables = [], string $class = AdapterInterface::class): AdapterInterface
    {
        $connection = $this->createStub($class);
        $connection->method('select')->willReturnCallback(fn () => $this->selectStub());
        $connection->method('isTableExists')->willReturnCallback(
            static fn ($table) => in_array($table, $existingTables, true)
        );
        return $connection;
    }

    protected function resourceStub(AdapterInterface $connection): ResourceConnection
    {
        $resource = $this->createStub(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);
        $resource->method('getTableName')->willReturnArgument(0);
        return $resource;
    }
}
