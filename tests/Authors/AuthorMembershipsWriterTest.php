<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

require_once __DIR__ . '/../Doubles/Elements/AuthorElementSpy.php';
require_once __DIR__ . '/../Doubles/Elements/GroupElementSpy.php';
require_once __DIR__ . '/../Doubles/Elements/GroupAliasElementSpy.php';
require_once __DIR__ . '/../Doubles/Elements/ZxProdElementSpy.php';

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Authors\Dto\MembershipInputDto;
use ZxArt\Authors\Services\AuthorMembershipsWriter;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Tests\Doubles\Elements\AuthorElementSpy;
use ZxArt\Tests\Doubles\Elements\GroupAliasElementSpy;
use ZxArt\Tests\Doubles\Elements\GroupElementSpy;
use ZxArt\Tests\Doubles\Elements\ZxProdElementSpy;

/**
 * What an author takes part in, of one type, becomes exactly the given list.
 */
final class AuthorMembershipsWriterTest extends TestCase
{
    private const int AUTHOR_ID = 7;
    private const int GROUP_ID = 20;
    private const int GROUP_ALIAS_ID = 21;
    private const int OTHER_GROUP_ID = 22;
    private const int PROD_ID = 30;

    private SQLiteConnection $db;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $this->db->getPdo()->exec('CREATE TABLE authorship (id INTEGER PRIMARY KEY, authorId INTEGER, elementId INTEGER, roles TEXT, startDate INTEGER DEFAULT 0, endDate INTEGER DEFAULT 0, type TEXT)');
    }

    public function testGroupsAndGroupAliasesAreStoredWithRolesAndDates(): void
    {
        $this->writer()->replace(self::AUTHOR_ID, EntityType::Group, [
            new MembershipInputDto(self::GROUP_ID, ['coder'], 100, 200),
            new MembershipInputDto(self::GROUP_ALIAS_ID),
        ]);

        self::assertSame([
            [self::GROUP_ID, '["coder"]', 100, 200],
            [self::GROUP_ALIAS_ID, '["unknown"]', 0, 0],
        ], $this->records(EntityType::Group));
    }

    public function testAGroupLeftOutIsLeftAndTheProductionsAreUntouched(): void
    {
        $this->insert(self::OTHER_GROUP_ID, EntityType::Group, '["musician"]');
        $this->insert(self::PROD_ID, EntityType::Prod, '["code"]');

        $this->writer()->replace(self::AUTHOR_ID, EntityType::Group, [new MembershipInputDto(self::GROUP_ID, ['coder'])]);

        self::assertSame([[self::GROUP_ID, '["coder"]', 0, 0]], $this->records(EntityType::Group));
        self::assertSame([[self::PROD_ID, '["code"]', 0, 0]], $this->records(EntityType::Prod));
    }

    public function testProductionsTakeTheProductionRoles(): void
    {
        $this->writer()->replace(self::AUTHOR_ID, EntityType::Prod, [new MembershipInputDto(self::PROD_ID, ['code', 'music'])]);

        self::assertSame([[self::PROD_ID, '["code","music"]', 0, 0]], $this->records(EntityType::Prod));
    }

    public function testAnElementOfAnotherTypeIsRefusedBeforeAnythingIsWritten(): void
    {
        $this->insert(self::OTHER_GROUP_ID, EntityType::Group, '["musician"]');

        try {
            $this->writer()->replace(self::AUTHOR_ID, EntityType::Group, [
                new MembershipInputDto(self::GROUP_ID),
                new MembershipInputDto(self::PROD_ID),
            ]);
            self::fail('A production is no group');
        } catch (InvalidReferenceException) {
        }

        self::assertSame([[self::OTHER_GROUP_ID, '["musician"]', 0, 0]], $this->records(EntityType::Group));
    }

    public function testARoleOfAnotherKindIsRefused(): void
    {
        $this->expectException(InvalidReferenceException::class);

        $this->writer()->replace(self::AUTHOR_ID, EntityType::Prod, [new MembershipInputDto(self::PROD_ID, ['coder'])]);
    }

    private function writer(): AuthorMembershipsWriter
    {
        $elements = [
            self::AUTHOR_ID => new AuthorElementSpy(self::AUTHOR_ID),
            self::GROUP_ID => new GroupElementSpy(self::GROUP_ID),
            self::GROUP_ALIAS_ID => new GroupAliasElementSpy(self::GROUP_ALIAS_ID),
            self::OTHER_GROUP_ID => new GroupElementSpy(self::OTHER_GROUP_ID),
            self::PROD_ID => new ZxProdElementSpy(self::PROD_ID),
        ];
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturnCallback(
            static fn(int $id): ?structureElement => $elements[$id] ?? null,
        );

        return new AuthorMembershipsWriter(
            new AuthorshipDbal($this->db, $structureManager),
            new EntityReferenceResolver($structureManager, $this->createStub(HardwareCatalogService::class)),
        );
    }

    private function insert(int $elementId, EntityType $type, string $roles): void
    {
        $this->db->table('authorship')->insert([
            'authorId' => self::AUTHOR_ID,
            'elementId' => $elementId,
            'roles' => $roles,
            'type' => $type->value,
        ]);
    }

    /**
     * @return list<array{int, string, int, int}> element, roles, start and end date
     */
    private function records(EntityType $type): array
    {
        return array_map(
            static fn(array $row): array => [(int)$row['elementId'], (string)$row['roles'], (int)$row['startDate'], (int)$row['endDate']],
            $this->db->table('authorship')
                ->where('authorId', '=', self::AUTHOR_ID)
                ->where('type', '=', $type->value)
                ->orderBy('elementId')
                ->get(['elementId', 'roles', 'startDate', 'endDate']),
        );
    }
}
