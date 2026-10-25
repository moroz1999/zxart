<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Groups\Dbal\GroupSearchDbal;

/**
 * Groups by a part of the title, of the abbreviation or of an alias, by
 * location and by type; each group once, never an alias.
 */
class GroupSearchRepositoryTest extends TestCase
{
    private const int TRIUMPH = 1;
    private const int SAGE = 2;
    private const int CODE_BUSTERS = 3;
    private const int ESTONIA = 101;
    private const int TALLINN = 100;

    private SQLiteConnection $db;
    private GroupSearchDbal $repository;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $pdo = $this->db->getPdo();
        $pdo->exec('CREATE TABLE module_group (id INTEGER, title TEXT, abbreviation TEXT, country INTEGER, city INTEGER, type TEXT)');
        $pdo->exec('CREATE TABLE module_groupalias (id INTEGER, title TEXT, groupId INTEGER)');
        $this->db->table('module_group')->insert([
            ['id' => self::TRIUMPH, 'title' => 'Triumph', 'abbreviation' => 'TRM', 'country' => self::ESTONIA, 'city' => self::TALLINN, 'type' => 'demogroup'],
            ['id' => self::SAGE, 'title' => 'Sage Group', 'abbreviation' => '', 'country' => self::ESTONIA, 'city' => 0, 'type' => 'company'],
            ['id' => self::CODE_BUSTERS, 'title' => 'Code Busters', 'abbreviation' => 'CBS', 'country' => 0, 'city' => 0, 'type' => 'demogroup'],
        ]);
        $this->db->table('module_groupalias')->insert(['id' => 11, 'title' => 'Triumph Brothers', 'groupId' => self::TRIUMPH]);
        $this->repository = new GroupSearchDbal($this->db);
    }

    public function testANameMatchesTheTitleTheAbbreviationOrAnAlias(): void
    {
        self::assertSame([self::CODE_BUSTERS], $this->repository->findIds('cbs', null, null, null, 0, 10));
        self::assertSame([self::TRIUMPH], $this->repository->findIds('Brothers', null, null, null, 0, 10));
        self::assertSame([self::TRIUMPH], $this->repository->findIds('triumph', null, null, null, 0, 10));
    }

    public function testLocationAndTypeNarrowTheResult(): void
    {
        self::assertSame([self::SAGE, self::TRIUMPH], $this->repository->findIds(null, self::ESTONIA, null, null, 0, 10));
        self::assertSame([self::TRIUMPH], $this->repository->findIds(null, null, self::TALLINN, null, 0, 10));
        self::assertSame([self::CODE_BUSTERS, self::TRIUMPH], $this->repository->findIds(null, null, null, 'demogroup', 0, 10));
        self::assertSame(2, $this->repository->count(null, null, null, 'demogroup'));
    }

    public function testThePageIsOrderedByTitle(): void
    {
        self::assertSame([self::SAGE], $this->repository->findIds(null, null, null, null, 1, 1));
        self::assertSame(3, $this->repository->count(null, null, null, null));
    }
}
