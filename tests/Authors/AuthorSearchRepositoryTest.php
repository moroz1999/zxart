<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Authors\Dbal\AuthorSearchRepository;

/**
 * Authors by a part of the nickname, of the real name in any language or of an
 * alias, by location and by group; each author once, never an alias.
 */
class AuthorSearchRepositoryTest extends TestCase
{
    private const int ALEX = 1;
    private const int BORIS = 2;
    private const int CLAUDE = 3;
    private const int ALEX_ALIAS = 11;
    private const int TALLINN = 100;
    private const int ESTONIA = 101;
    private const int GROUP = 200;
    private const int GROUP_ALIAS = 201;

    private SQLiteConnection $db;
    private AuthorSearchRepository $repository;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $pdo = $this->db->getPdo();
        $pdo->exec('CREATE TABLE module_author (id INTEGER, languageId INTEGER, title TEXT, realName TEXT, country INTEGER, city INTEGER)');
        $pdo->exec('CREATE TABLE module_authoralias (id INTEGER, title TEXT, authorId INTEGER)');
        $pdo->exec('CREATE TABLE module_groupalias (id INTEGER, title TEXT, groupId INTEGER)');
        $pdo->exec('CREATE TABLE authorship (authorId INTEGER, elementId INTEGER, type TEXT)');
        $this->addAuthor(self::ALEX, 'Alex', ['Alexander Ivanov', 'Александр Иванов'], self::ESTONIA, self::TALLINN);
        $this->addAuthor(self::BORIS, 'Boris', ['Boris Petrov', 'Борис Петров'], self::ESTONIA, 0);
        $this->addAuthor(self::CLAUDE, 'Claude', ['', ''], 0, 0);
        $this->db->table('module_authoralias')->insert(['id' => self::ALEX_ALIAS, 'title' => 'Zorro', 'authorId' => self::ALEX]);
        $this->db->table('module_groupalias')->insert(['id' => self::GROUP_ALIAS, 'title' => 'Crew', 'groupId' => self::GROUP]);
        $this->repository = new AuthorSearchRepository($this->db);
    }

    public function testANameMatchesTheNicknameTheRealNameInAnyLanguageOrAnAlias(): void
    {
        self::assertSame([self::ALEX], $this->repository->findIds('Иванов', null, null, null, 0, 10));
        self::assertSame([self::ALEX], $this->repository->findIds('orr', null, null, null, 0, 10));
        self::assertSame([self::ALEX, self::BORIS], $this->repository->findIds('o', null, null, null, 0, 10));
        self::assertSame(2, $this->repository->count('o', null, null, null));
    }

    public function testTheLocationNarrowsTheResult(): void
    {
        self::assertSame([self::ALEX, self::BORIS], $this->repository->findIds(null, self::ESTONIA, null, null, 0, 10));
        self::assertSame([self::ALEX], $this->repository->findIds(null, null, self::TALLINN, null, 0, 10));
    }

    public function testAGroupFindsItsMembersThroughEitherSidesAliases(): void
    {
        $this->addMembership(self::ALEX_ALIAS, self::GROUP);
        $this->addMembership(self::BORIS, self::GROUP_ALIAS);
        $this->addMembership(self::CLAUDE, 999);

        self::assertSame([self::ALEX, self::BORIS], $this->repository->findIds(null, null, null, self::GROUP, 0, 10));
        self::assertSame(2, $this->repository->count(null, null, null, self::GROUP));
    }

    public function testThePageIsOrderedByNickname(): void
    {
        self::assertSame([self::BORIS], $this->repository->findIds(null, null, null, null, 1, 1));
        self::assertSame(3, $this->repository->count(null, null, null, null));
    }

    /**
     * @param array{string, string} $realNames one per language
     */
    private function addAuthor(int $id, string $title, array $realNames, int $countryId, int $cityId): void
    {
        foreach ($realNames as $languageId => $realName) {
            $this->db->table('module_author')->insert([
                'id' => $id,
                'languageId' => $languageId + 1,
                'title' => $title,
                'realName' => $realName,
                'country' => $countryId,
                'city' => $cityId,
            ]);
        }
    }

    private function addMembership(int $authorId, int $groupId): void
    {
        $this->db->table('authorship')->insert(['authorId' => $authorId, 'elementId' => $groupId, 'type' => 'group']);
    }
}
