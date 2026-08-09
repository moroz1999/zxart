<?php

declare(strict_types=1);

namespace ZxArt\Tests\Hardware;

use DBValueSetDataChunk;
use DI\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

/**
 * The `lookupTable` mode of the shared DBValueSet chunk: the link table stores
 * numeric ids while the element property keeps working in codes.
 *
 * The write path is what these tests really guard. `persistExtraData()` diffs
 * the wanted values against the stored rows, so the code→id translation has to
 * happen before that diff; if it did not, every save would delete and reinsert
 * every row of the element.
 */
#[AllowMockObjectsWithoutExpectations]
class DBValueSetLookupTest extends TestCase
{
    private const array LOOKUP_ROWS = [
        ['id' => 1, 'code' => 'zx48'],
        ['id' => 2, 'code' => 'ay'],
        ['id' => 3, 'code' => 'kempston'],
    ];

    protected function setUp(): void
    {
        // both directions are cached per lookup table for the whole request
        foreach (['lookupCodesById', 'lookupIdsByCode'] as $property) {
            new ReflectionProperty(DBValueSetDataChunk::class, $property)->setValue(null, []);
        }
    }

    public function testStoredIdsAreReadBackAsCodes(): void
    {
        $chunk = $this->createChunk([
            ['id' => 10, 'hardwareId' => 3],
            ['id' => 11, 'hardwareId' => 1],
        ]);

        $this->assertSame(['kempston', 'zx48'], $chunk->getStorageValue());
    }

    public function testStoredIdWithNoCatalogRowIsDropped(): void
    {
        $chunk = $this->createChunk([
            ['id' => 10, 'hardwareId' => 1],
            ['id' => 11, 'hardwareId' => 999],
        ]);

        $this->assertSame(['zx48'], $chunk->getStorageValue());
    }

    public function testPersistTranslatesCodesToIdsAndOnlyTouchesTheDifference(): void
    {
        $inserted = [];
        $deleted = [];
        $chunk = $this->createChunk(
            [
                ['id' => 10, 'hardwareId' => 1],
                ['id' => 11, 'hardwareId' => 2],
            ],
            $inserted,
            $deleted,
        );

        // zx48 stays, ay goes, kempston is new
        $chunk->setExternalValue(['zx48', 'kempston']);
        $chunk->persistExtraData();

        $this->assertSame([['elementId' => 42, 'hardwareId' => 3]], $inserted);
        $this->assertSame([11], $deleted);
    }

    public function testPersistWithAnUnchangedSetWritesNothing(): void
    {
        $inserted = [];
        $deleted = [];
        $chunk = $this->createChunk(
            [
                ['id' => 10, 'hardwareId' => 1],
                ['id' => 11, 'hardwareId' => 2],
            ],
            $inserted,
            $deleted,
        );

        $chunk->setExternalValue(['zx48', 'ay']);
        $chunk->persistExtraData();

        $this->assertSame([], $inserted);
        $this->assertSame([], $deleted);
    }

    public function testPersistDropsCodesThatAreNotInTheCatalog(): void
    {
        $inserted = [];
        $deleted = [];
        $chunk = $this->createChunk([], $inserted, $deleted);

        $chunk->setExternalValue(['zx48', 'not_a_real_code']);
        $chunk->persistExtraData();

        $this->assertSame([['elementId' => 42, 'hardwareId' => 1]], $inserted);
    }

    /**
     * @param list<array{id: int, hardwareId: int}> $existingRows
     * @param list<array{elementId: int, hardwareId: int}> $inserted
     * @param list<int> $deleted
     */
    private function createChunk(array $existingRows, array &$inserted = [], array &$deleted = []): DBValueSetDataChunk
    {
        $chunk = new DBValueSetDataChunk('hardwareRequired');
        $chunk->setProperties([
            'tableName' => 'module_zxrelease_hw_required',
            'valueField' => 'hardwareId',
            'lookupTable' => 'hardware',
            'lookupIdField' => 'id',
            'lookupCodeField' => 'code',
        ]);

        $element = new stdClass();
        $element->id = 42;
        $chunk->setStructureElement($element);

        $connection = $this->createConnection($existingRows, $inserted, $deleted);
        $container = $this->createMock(Container::class);
        $container->method('get')->with('db')->willReturn($connection);
        $chunk->setContainer($container);

        return $chunk;
    }

    /**
     * @param list<array{id: int, hardwareId: int}> $existingRows
     * @param list<array{elementId: int, hardwareId: int}> $inserted
     * @param list<int> $deleted
     */
    private function createConnection(array $existingRows, array &$inserted, array &$deleted): Connection&MockObject
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('table')->willReturnCallback(
            function (string $table) use ($existingRows, &$inserted, &$deleted): Builder&MockObject {
                return $table === 'hardware'
                    ? $this->createLookupBuilder()
                    : $this->createLinkBuilder($existingRows, $inserted, $deleted);
            },
        );

        return $connection;
    }

    private function createLookupBuilder(): Builder&MockObject
    {
        $builder = $this->createMock(Builder::class);
        $builder->method('select')->willReturnSelf();
        $builder->method('get')->willReturn(self::LOOKUP_ROWS);

        return $builder;
    }

    /**
     * @param list<array{id: int, hardwareId: int}> $existingRows
     * @param list<array{elementId: int, hardwareId: int}> $inserted
     * @param list<int> $deleted
     */
    private function createLinkBuilder(array $existingRows, array &$inserted, array &$deleted): Builder&MockObject
    {
        $builder = $this->createMock(Builder::class);
        $builder->method('where')->willReturnSelf();
        $builder->method('select')->willReturnSelf();
        $builder->method('get')->willReturn($existingRows);
        $builder->method('insert')->willReturnCallback(
            static function (array $rows) use (&$inserted): bool {
                foreach ($rows as $row) {
                    $inserted[] = $row;
                }
                return true;
            },
        );
        $builder->method('whereIn')->willReturnCallback(
            static function (string $column, array $ids) use (&$deleted, $builder): Builder {
                foreach ($ids as $id) {
                    $deleted[] = $id;
                }
                return $builder;
            },
        );
        $builder->method('delete')->willReturn(0);

        return $builder;
    }
}
