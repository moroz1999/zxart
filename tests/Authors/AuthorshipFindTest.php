<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Authors\Dto\AuthorshipDto;
use ZxArt\Shared\EntityType;

/**
 * Authorship records read as they are stored: roles decoded, dates as
 * timestamps, records of other types left out.
 */
class AuthorshipFindTest extends TestCase
{
    private AuthorshipDbal $repository;

    protected function setUp(): void
    {
        $db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $db->setFetchMode(PDO::FETCH_ASSOC);
        $db->getPdo()->exec('CREATE TABLE authorship (id INTEGER PRIMARY KEY, authorId INTEGER, elementId INTEGER, roles TEXT, startDate INTEGER DEFAULT 0, endDate INTEGER DEFAULT 0, type TEXT)');
        $db->table('authorship')->insert([
            ['authorId' => 7, 'elementId' => 42, 'roles' => '["coder","musician"]', 'startDate' => 100, 'endDate' => 200, 'type' => 'group'],
            ['authorId' => 8, 'elementId' => 42, 'roles' => '', 'startDate' => 0, 'endDate' => 0, 'type' => 'group'],
            ['authorId' => 7, 'elementId' => 42, 'roles' => '["code"]', 'startDate' => 0, 'endDate' => 0, 'type' => 'prod'],
            ['authorId' => 7, 'elementId' => 43, 'roles' => '["graphics"]', 'startDate' => 0, 'endDate' => 0, 'type' => 'group'],
        ]);
        $this->repository = new AuthorshipDbal($db, $this->createStub(structureManager::class));
    }

    public function testFindByElementAnswersTheRecordsOfThatType(): void
    {
        self::assertEquals([
            new AuthorshipDto(elementId: 42, authorId: 7, roles: ['coder', 'musician'], startDate: 100, endDate: 200),
            new AuthorshipDto(elementId: 42, authorId: 8, roles: [], startDate: 0, endDate: 0),
        ], $this->repository->findByElement(42, EntityType::Group));
    }

    public function testFindByAuthorAnswersTheRecordsOfThatType(): void
    {
        self::assertEquals([
            new AuthorshipDto(elementId: 42, authorId: 7, roles: ['coder', 'musician'], startDate: 100, endDate: 200),
            new AuthorshipDto(elementId: 43, authorId: 7, roles: ['graphics'], startDate: 0, endDate: 0),
        ], $this->repository->findByAuthor(7, EntityType::Group));
    }

    public function testNothingStoredAnswersNoRecords(): void
    {
        self::assertSame([], $this->repository->findByAuthor(9, EntityType::Release));
    }
}
