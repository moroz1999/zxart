<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

require_once __DIR__ . '/../Doubles/Elements/AuthorElementSpy.php';
require_once __DIR__ . '/../Doubles/Elements/AuthorAliasElementSpy.php';
require_once __DIR__ . '/../Doubles/Elements/GroupElementSpy.php';

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Authors\Services\ElementAuthorsWriter;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Tests\Doubles\Elements\AuthorAliasElementSpy;
use ZxArt\Tests\Doubles\Elements\AuthorElementSpy;
use ZxArt\Tests\Doubles\Elements\GroupElementSpy;

/**
 * The authors of an element become exactly the given list.
 */
final class ElementAuthorsWriterTest extends TestCase
{
    private const int ELEMENT_ID = 100;
    private const int AUTHOR_ID = 7;
    private const int OTHER_AUTHOR_ID = 8;
    private const int ALIAS_ID = 9;
    private const int SECOND_ALIAS_ID = 10;
    private const int GROUP_ID = 11;

    private SQLiteConnection $db;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $this->db->getPdo()->exec('CREATE TABLE authorship (id INTEGER PRIMARY KEY, authorId INTEGER, elementId INTEGER, roles TEXT, startDate INTEGER DEFAULT 0, endDate INTEGER DEFAULT 0, type TEXT)');
    }

    public function testTheListIsStoredWithItsRolesAndDates(): void
    {
        $this->writer()->replace(self::ELEMENT_ID, EntityType::Group, [
            new AuthorshipInputDto(self::AUTHOR_ID, ['coder', 'musician'], 100, 200),
        ]);

        self::assertSame([[self::AUTHOR_ID, '["coder","musician"]', 100, 200]], $this->records(EntityType::Group));
    }

    public function testAnAuthorLeftOutIsRemovedAndAListedOneUpdated(): void
    {
        $this->insert(self::AUTHOR_ID, EntityType::Prod, '["code"]');
        $this->insert(self::OTHER_AUTHOR_ID, EntityType::Prod, '["music"]');

        $this->writer()->replace(self::ELEMENT_ID, EntityType::Prod, [
            new AuthorshipInputDto(self::AUTHOR_ID, ['graphics']),
        ]);

        self::assertSame([[self::AUTHOR_ID, '["graphics"]', 0, 0]], $this->records(EntityType::Prod));
    }

    public function testNoRolesAreStoredAsUnknown(): void
    {
        $this->writer()->replace(self::ELEMENT_ID, EntityType::Prod, [new AuthorshipInputDto(self::AUTHOR_ID)]);

        self::assertSame([[self::AUTHOR_ID, '["unknown"]', 0, 0]], $this->records(EntityType::Prod));
    }

    public function testAnEmptyListRemovesEveryAuthorOfThatTypeOnly(): void
    {
        $this->insert(self::AUTHOR_ID, EntityType::Prod, '["code"]');
        $this->insert(self::AUTHOR_ID, EntityType::Release, '["release"]');

        $this->writer()->replace(self::ELEMENT_ID, EntityType::Prod, []);

        self::assertSame([], $this->records(EntityType::Prod));
        self::assertSame([[self::AUTHOR_ID, '["release"]', 0, 0]], $this->records(EntityType::Release));
    }

    public function testAnAuthorListedWithTheirAliasIsCreditedAsTheAliasWithTheAuthorsRoles(): void
    {
        $this->writer()->replace(self::ELEMENT_ID, EntityType::Prod, [
            new AuthorshipInputDto(self::AUTHOR_ID, ['code']),
            new AuthorshipInputDto(self::ALIAS_ID),
        ]);

        self::assertSame([[self::ALIAS_ID, '["code"]', 0, 0]], $this->records(EntityType::Prod));
    }

    public function testOnlyTheFirstOfTwoAliasesOfOneAuthorIsCredited(): void
    {
        $this->writer()->replace(self::ELEMENT_ID, EntityType::Prod, [
            new AuthorshipInputDto(self::ALIAS_ID, ['code']),
            new AuthorshipInputDto(self::SECOND_ALIAS_ID, ['music']),
        ]);

        self::assertSame([[self::ALIAS_ID, '["code"]', 0, 0]], $this->records(EntityType::Prod));
    }

    public function testWhatIsNoAuthorIsRefusedBeforeAnythingIsWritten(): void
    {
        $this->insert(self::OTHER_AUTHOR_ID, EntityType::Prod, '["music"]');

        try {
            $this->writer()->replace(self::ELEMENT_ID, EntityType::Prod, [
                new AuthorshipInputDto(self::AUTHOR_ID, ['code']),
                new AuthorshipInputDto(self::GROUP_ID, ['code']),
            ]);
            self::fail('A group cannot be credited as an author');
        } catch (InvalidReferenceException) {
        }

        self::assertSame([[self::OTHER_AUTHOR_ID, '["music"]', 0, 0]], $this->records(EntityType::Prod));
    }

    public function testARoleOfAnotherKindIsRefused(): void
    {
        $this->expectException(InvalidReferenceException::class);

        $this->writer()->replace(self::ELEMENT_ID, EntityType::Group, [new AuthorshipInputDto(self::AUTHOR_ID, ['code'])]);
    }

    private function writer(): ElementAuthorsWriter
    {
        $alias = new AuthorAliasElementSpy(self::ALIAS_ID);
        $alias->authorId = self::AUTHOR_ID;
        $secondAlias = new AuthorAliasElementSpy(self::SECOND_ALIAS_ID);
        $secondAlias->authorId = self::AUTHOR_ID;
        $elements = [
            self::AUTHOR_ID => new AuthorElementSpy(self::AUTHOR_ID),
            self::OTHER_AUTHOR_ID => new AuthorElementSpy(self::OTHER_AUTHOR_ID),
            self::ALIAS_ID => $alias,
            self::SECOND_ALIAS_ID => $secondAlias,
            self::GROUP_ID => new GroupElementSpy(self::GROUP_ID),
        ];
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturnCallback(
            static fn(int $id): ?structureElement => $elements[$id] ?? null,
        );

        return new ElementAuthorsWriter(
            new AuthorshipDbal($this->db, $structureManager),
            new EntityReferenceResolver($structureManager, $this->createStub(HardwareCatalogService::class)),
        );
    }

    private function insert(int $authorId, EntityType $type, string $roles): void
    {
        $this->db->table('authorship')->insert([
            'authorId' => $authorId,
            'elementId' => self::ELEMENT_ID,
            'roles' => $roles,
            'type' => $type->value,
        ]);
    }

    /**
     * @return list<array{int, string, int, int}> author, roles, start and end date
     */
    private function records(EntityType $type): array
    {
        return array_map(
            static fn(array $row): array => [(int)$row['authorId'], (string)$row['roles'], (int)$row['startDate'], (int)$row['endDate']],
            $this->db->table('authorship')
                ->where('elementId', '=', self::ELEMENT_ID)
                ->where('type', '=', $type->value)
                ->orderBy('authorId')
                ->get(['authorId', 'roles', 'startDate', 'endDate']),
        );
    }
}
