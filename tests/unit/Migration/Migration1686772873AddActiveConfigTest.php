<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Tests\Unit\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Frosh\ThumbnailProcessor\Migration\Migration1686772873AddActiveConfig;
use PHPUnit\Framework\TestCase;

class Migration1686772873AddActiveConfigTest extends TestCase
{
    public function testGetCreationTimestamp(): void
    {
        $migration = new Migration1686772873AddActiveConfig();
        static::assertSame(1686772873, $migration->getCreationTimestamp());
    }

    public function testUpdate(): void
    {
        $migration = new Migration1686772873AddActiveConfig();
        $connection = $this->createMock(Connection::class);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::once())->method('select')->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('from')->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('where')->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('andWhere')->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setParameter')->willReturn($queryBuilder);

        $statement = $this->createMock(Result::class);
        $statement->expects(static::once())->method('fetchOne')->willReturn('3.0.2');

        $queryBuilder->expects(static::once())->method('executeQuery')->willReturn($statement);

        $connection->expects(static::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $connection->expects(static::once())
            ->method('update');

        $migration->update($connection);
    }

    /**
     * @dataProvider provideVersions
     */
    public function testUpdateWithVersions(bool $runUpdate, ?string $version): void
    {
        $migration = new Migration1686772873AddActiveConfig();
        $connection = $this->createMock(Connection::class);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::once())->method('select')->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('from')->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('where')->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('andWhere')->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setParameter')->willReturn($queryBuilder);

        $statement = $this->createMock(Result::class);
        $statement->expects(static::once())->method('fetchOne')->willReturn($version);

        $queryBuilder->expects(static::once())->method('executeQuery')->willReturn($statement);

        $connection->expects(static::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $expects = $runUpdate ? static::once() : static::never();
        $connection->expects($expects)
            ->method('update');

        $migration->update($connection);
    }

    public function testUpdateDestructive(): void
    {
        $migration = new Migration1686772873AddActiveConfig();
        $connection = $this->createMock(Connection::class);
        $connection->expects(static::never())
            ->method('createQueryBuilder');
        $migration->updateDestructive($connection);
    }

    /**
     * @return iterable<array{bool, string|null}>
     */
    public static function provideVersions(): iterable
    {
        yield [false, null];
        yield [true, '2.0.0'];
        yield [true, '2.0.1'];
        yield [false, '2.1.0'];
        yield [false, '2.1.1'];
        yield [true, '3.0.0'];
        yield [true, '3.0.1'];
        yield [true, '3.0.2'];
        yield [true, '3.0.3'];
        yield [false, '3.0.4'];
        yield [false, '3.1.0'];
        yield [false, '3.1.1'];
        yield [false, '3.1.2'];
        yield [false, '3.1.3'];
        yield [false, '3.1.4'];
        yield [false, '3.1.5'];
        yield [false, '3.1.6'];
        yield [false, '3.1.7'];
    }
}
