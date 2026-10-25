<?php

declare(strict_types=1);

namespace ZxArt\Tests\Import;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Import\Dbal\ImportOriginsDbal;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Shared\EntityType;

/**
 * The portal ids of an element become exactly the given list.
 */
final class ImportOriginsWriterTest extends TestCase
{
    private const int ELEMENT_ID = 100;
    private const int OTHER_ELEMENT_ID = 200;

    private SQLiteConnection $db;
    private ImportOriginsWriter $writer;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $this->db->getPdo()->exec('CREATE TABLE import_origin (id INTEGER PRIMARY KEY, elementId INTEGER, importOrigin TEXT, importId TEXT, type TEXT)');
        $this->writer = new ImportOriginsWriter(new ImportOriginsDbal($this->db));
    }

    public function testNewIdsAreAddedAndThoseLeftOutRemoved(): void
    {
        $this->insert(self::ELEMENT_ID, 'zxdb', '1');
        $this->insert(self::ELEMENT_ID, 'pouet', '2');

        $this->writer->replace(self::ELEMENT_ID, EntityType::Prod, [
            new ImportOriginDto(ImportOrigin::Zxdb, '1'),
            new ImportOriginDto(ImportOrigin::Vtrdos, '3'),
        ]);

        self::assertSame([['vt', '3', 'prod'], ['zxdb', '1', 'prod']], $this->origins(self::ELEMENT_ID));
    }

    public function testAnIdGivenTwiceIsStoredOnce(): void
    {
        $this->writer->replace(self::ELEMENT_ID, EntityType::Author, [
            new ImportOriginDto(ImportOrigin::Zxdb, '1'),
            new ImportOriginDto(ImportOrigin::Zxdb, '1'),
        ]);

        self::assertSame([['zxdb', '1', 'author']], $this->origins(self::ELEMENT_ID));
    }

    public function testAnIdOfAnotherElementOfTheTypeMovesHere(): void
    {
        $this->insert(self::OTHER_ELEMENT_ID, 'zxdb', '1', 'author');

        $this->writer->replace(self::ELEMENT_ID, EntityType::Author, [new ImportOriginDto(ImportOrigin::Zxdb, '1')]);

        self::assertSame([['zxdb', '1', 'author']], $this->origins(self::ELEMENT_ID));
        self::assertSame([], $this->origins(self::OTHER_ELEMENT_ID));
    }

    public function testAnEmptyListRemovesEveryId(): void
    {
        $this->insert(self::ELEMENT_ID, 'zxdb', '1');

        $this->writer->replace(self::ELEMENT_ID, EntityType::Prod, []);

        self::assertSame([], $this->origins(self::ELEMENT_ID));
    }

    private function insert(int $elementId, string $origin, string $importId, string $type = 'prod'): void
    {
        $this->db->table('import_origin')->insert([
            'elementId' => $elementId,
            'importOrigin' => $origin,
            'importId' => $importId,
            'type' => $type,
        ]);
    }

    /**
     * @return list<array{string, string, string}> portal, id, type
     */
    private function origins(int $elementId): array
    {
        return array_map(
            static fn(array $row): array => [(string)$row['importOrigin'], (string)$row['importId'], (string)$row['type']],
            $this->db->table('import_origin')
                ->where('elementId', '=', $elementId)
                ->orderBy('importOrigin')
                ->get(['importOrigin', 'importId', 'type']),
        );
    }
}
