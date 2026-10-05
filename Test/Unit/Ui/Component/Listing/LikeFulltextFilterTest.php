<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Test\Unit\Ui\Component\Listing;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Panth\PageBuilderAi\Ui\Component\Listing\LikeFulltextFilter;
use PHPUnit\Framework\TestCase;

class LikeFulltextFilterTest extends TestCase
{
    public function testLikeConditionIsBuiltForEveryColumn(): void
    {
        $select = $this->createMock(Select::class);
        $select->expects($this->once())
            ->method('where')
            ->with("`title` LIKE '%50\\%\\_off%' OR `content` LIKE '%50\\%\\_off%'");

        (new LikeFulltextFilter(['title', 5, 'content']))
            ->apply($this->collection($select), new Filter(['value' => '  50%_off ']));
    }

    public function testLongSearchTermIsTruncated(): void
    {
        $select = $this->createMock(Select::class);
        $select->expects($this->once())
            ->method('where')
            ->with("`title` LIKE '%" . str_repeat('a', 200) . "%'");

        (new LikeFulltextFilter(['title']))
            ->apply($this->collection($select), new Filter(['value' => str_repeat('a', 300)]));
    }

    public function testBlankOrNonScalarValueIsIgnored(): void
    {
        $select = $this->createMock(Select::class);
        $select->expects($this->never())->method('where');
        $filter = new LikeFulltextFilter(['title']);

        $filter->apply($this->collection($select), new Filter(['value' => '   ']));
        $filter->apply($this->collection($select), new Filter(['value' => ['x']]));
    }

    public function testNonDatabaseCollectionOrNoColumnsIsIgnored(): void
    {
        $filterValue = $this->createMock(Filter::class);
        $filterValue->expects($this->never())->method('getValue');

        (new LikeFulltextFilter(['title']))->apply($this->createStub(Collection::class), $filterValue);
        (new LikeFulltextFilter([]))->apply($this->createStub(AbstractDb::class), $filterValue);
    }

    private function collection(Select $select): AbstractDb
    {
        $connection = $this->createStub(AdapterInterface::class);
        $connection->method('quoteIdentifier')->willReturnCallback(static fn ($c) => '`' . $c . '`');
        $connection->method('quoteInto')->willReturnCallback(
            static fn ($text, $value) => str_replace('?', "'" . $value . "'", $text)
        );
        $collection = $this->createStub(AbstractDb::class);
        $collection->method('getConnection')->willReturn($connection);
        $collection->method('getSelect')->willReturn($select);
        return $collection;
    }
}
