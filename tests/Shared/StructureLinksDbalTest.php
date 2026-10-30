<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\LinkTypes;
use ZxArt\Shared\Dbal\StructureLinksDbal;

final class StructureLinksDbalTest extends TestCase
{
    public function testTheParentsOfOneTypeComeInTheOrderTheyWereLinked(): void
    {
        $db = new SQLiteConnection(new PDO('sqlite::memory:'));
        $db->setFetchMode(PDO::FETCH_ASSOC);
        $db->getPdo()->exec('CREATE TABLE structure_links (id INTEGER PRIMARY KEY, parentStructureId INTEGER, childStructureId INTEGER, type TEXT, position INTEGER)');
        $db->table('structure_links')->insert([
            ['parentStructureId' => 30, 'childStructureId' => 1, 'type' => 'series', 'position' => 0],
            ['parentStructureId' => 20, 'childStructureId' => 1, 'type' => 'series', 'position' => 0],
            ['parentStructureId' => 40, 'childStructureId' => 1, 'type' => 'compilation', 'position' => 0],
            ['parentStructureId' => 50, 'childStructureId' => 2, 'type' => 'series', 'position' => 0],
        ]);

        self::assertSame([30, 20], (new StructureLinksDbal($db))->parentIds(1, LinkTypes::SERIES));
    }
}
