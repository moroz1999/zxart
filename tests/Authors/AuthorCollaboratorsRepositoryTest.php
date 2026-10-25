<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Authors\Dbal\AuthorCollaboratorsRepository;

class AuthorCollaboratorsRepositoryTest extends TestCase
{
    private const int AUTHOR_ID = 100;
    private const int ALIAS_ID = 101;
    private const int OTHER_AUTHOR_ID = 102;

    private SQLiteConnection $db;
    private AuthorCollaboratorsRepository $repository;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $pdo = $this->db->getPdo();
        $pdo->exec('CREATE TABLE module_authoralias (id INTEGER PRIMARY KEY, authorId INTEGER)');
        $pdo->exec('CREATE TABLE authorship (id INTEGER PRIMARY KEY, authorId INTEGER, elementId INTEGER, type TEXT)');
        $this->db->table('module_authoralias')->insert(['id' => self::ALIAS_ID, 'authorId' => self::AUTHOR_ID]);
        $this->repository = new AuthorCollaboratorsRepository($this->db);
    }

    public function testCountGroupMembershipsCountsEachGroupOfTheAuthorAndItsAliasesOnce(): void
    {
        $this->addAuthorship(self::AUTHOR_ID, 1, 'group');
        $this->addAuthorship(self::ALIAS_ID, 1, 'group');
        $this->addAuthorship(self::ALIAS_ID, 2, 'group');
        $this->addAuthorship(self::AUTHOR_ID, 3, 'prod');
        $this->addAuthorship(self::OTHER_AUTHOR_ID, 4, 'group');

        $this->assertSame(2, $this->repository->countGroupMemberships(self::AUTHOR_ID));
        $this->assertSame(2, $this->repository->countGroupMemberships(self::ALIAS_ID));
    }

    private function addAuthorship(int $authorId, int $elementId, string $type): void
    {
        $this->db->table('authorship')->insert(['authorId' => $authorId, 'elementId' => $elementId, 'type' => $type]);
    }
}
