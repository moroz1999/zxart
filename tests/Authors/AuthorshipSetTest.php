<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Shared\EntityType;

/**
 * Setting an authorship makes the record hold exactly the given roles; dates
 * left out keep what is stored.
 */
class AuthorshipSetTest extends TestCase
{
    private SQLiteConnection $db;
    private AuthorshipDbal $repository;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $this->db->getPdo()->exec('CREATE TABLE authorship (id INTEGER PRIMARY KEY, authorId INTEGER, elementId INTEGER, roles TEXT, startDate INTEGER DEFAULT 0, endDate INTEGER DEFAULT 0, type TEXT)');
        $this->repository = new AuthorshipDbal($this->db, $this->createStub(structureManager::class));
    }

    public function testSetAddsTheRecord(): void
    {
        $this->repository->setAuthorship(42, 7, EntityType::Group, ['coder'], 100, null);

        self::assertSame([['roles' => '["coder"]', 'startDate' => 100, 'endDate' => 0]], $this->records(42, 7, 'group'));
    }

    public function testSetReplacesTheRolesAndKeepsTheDatesLeftOut(): void
    {
        $this->repository->setAuthorship(42, 7, EntityType::Group, ['coder', 'musician'], 100, 200);

        $this->repository->setAuthorship(42, 7, EntityType::Group, ['musician'], null, 0);

        self::assertSame([['roles' => '["musician"]', 'startDate' => 100, 'endDate' => 0]], $this->records(42, 7, 'group'));
    }

    public function testSetTouchesOnlyTheRecordOfThatType(): void
    {
        $this->repository->setAuthorship(42, 7, EntityType::Prod, ['code'], null, null);

        $this->repository->setAuthorship(42, 7, EntityType::Release, ['release'], null, null);

        self::assertSame([['roles' => '["code"]', 'startDate' => 0, 'endDate' => 0]], $this->records(42, 7, 'prod'));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(int $elementId, int $authorId, string $type): array
    {
        return array_map(
            static fn(array $row): array => ['roles' => $row['roles'], 'startDate' => (int)$row['startDate'], 'endDate' => (int)$row['endDate']],
            $this->db->table('authorship')
                ->where('elementId', '=', $elementId)
                ->where('authorId', '=', $authorId)
                ->where('type', '=', $type)
                ->get(['roles', 'startDate', 'endDate']),
        );
    }
}
