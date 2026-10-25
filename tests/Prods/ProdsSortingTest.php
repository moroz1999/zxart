<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Processors\MySqlProcessor;
use LanguagesManager;
use linksManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Prods\Services\ProdsService;
use ZxArt\Prods\UseCases\ImportProdHandler;

/**
 * The catalogue sorting selector offers every column in both directions,
 * so each of them must reach the SQL with the direction the visitor picked.
 */
#[AllowMockObjectsWithoutExpectations]
class ProdsSortingTest extends TestCase
{
    private ?string $executedSql = null;

    public function testProdsSortedByDateAscendingAreOrderedOldestFirst(): void
    {
        $this->createService()->getElementsByQuery($this->createQuery('module_zxprod'), ['date' => 'asc']);

        $this->assertStringContainsString('order by `structure_elements`.`dateCreated` asc, id asc', $this->executedSql);
    }

    public function testProdsSortedByDateDescendingAreOrderedNewestFirst(): void
    {
        $this->createService()->getElementsByQuery($this->createQuery('module_zxprod'), ['date' => 'desc']);

        $this->assertStringContainsString('order by `structure_elements`.`dateCreated` desc, id desc', $this->executedSql);
    }

    public function testReleasesSortedByDateAscendingAreOrderedOldestFirst(): void
    {
        $this->createService()->getReleasesByIdList($this->createQuery('module_zxrelease'), ['date' => 'asc']);

        $this->assertStringContainsString('order by `structure_elements`.`dateCreated` asc, module_zxrelease.id asc', $this->executedSql);
    }

    public function testReleasesSortedByYearDescendingAreOrderedLatestFirst(): void
    {
        $this->createService()->getReleasesByIdList($this->createQuery('module_zxrelease'), ['year' => 'desc']);

        $this->assertStringContainsString('order by module_zxrelease.year desc', $this->executedSql);
    }

    public function testReleasesSortedByYearAscendingAreOrderedEarliestFirst(): void
    {
        $this->createService()->getReleasesByIdList($this->createQuery('module_zxrelease'), ['year' => 'asc']);

        $this->assertStringContainsString('order by module_zxrelease.year asc', $this->executedSql);
    }

    private function createQuery(string $table): Builder
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection->method('select')->willReturnCallback(function (string $sql): array {
            $this->executedSql = $sql;
            return [];
        });
        $query = new Builder($connection, new MySqlGrammar(), new MySqlProcessor());
        return $query->from($table);
    }

    private function createService(): ProdsService
    {
        $db = $this->createStub(Connection::class);
        $db->method('getTablePrefix')->willReturn('');

        return new ProdsService(
            $this->createStub(structureManager::class),
            $this->createStub(linksManager::class),
            $this->createStub(privilegesManager::class),
            $this->createStub(AuthorshipDbal::class),
            $db,
            $this->createStub(LanguagesManager::class),
            $this->createStub(ImportIdOperator::class),
            $this->createStub(ImportProdHandler::class),
        );
    }
}
