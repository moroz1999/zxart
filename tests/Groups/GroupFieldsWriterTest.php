<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

require_once __DIR__ . '/../Doubles/Elements/GroupElementSpy.php';

use authorElement;
use cityElement;
use countryElement;
use groupElement;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupFieldsWriter;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Tests\Doubles\Elements\GroupElementSpy;

final class GroupFieldsWriterTest extends TestCase
{
    public function testWritesOnlyTheGivenFieldsPersistsAndRecalculates(): void
    {
        $group = new GroupElementSpy();

        $this->createWriter()->apply($group, new GroupFieldsDto(abbreviation: 'FX', startDate: 600000000));

        self::assertSame(['abbreviation' => 'FX', 'startDate' => 600000000], $group->values);
        self::assertSame(['persistElementData', 'recalculate'], $group->calls);
    }

    public function testTitleRenamesTheStructureNameAndMovesTheGroupToItsLetter(): void
    {
        $group = new GroupElementSpy();

        $this->createWriter()->apply($group, new GroupFieldsDto(title: 'Fenomen'));

        self::assertSame('Fenomen', $group->values['structureName']);
        self::assertSame(['persistElementData', 'checkParentLetter', 'recalculate'], $group->calls);
    }

    public function testLocationIsStoredAsIds(): void
    {
        $group = new GroupElementSpy();

        $this->createWriter([3 => $this->createStub(countryElement::class), 4 => $this->createStub(cityElement::class)])
            ->apply($group, new GroupFieldsDto(countryId: 3, cityId: 4));

        self::assertSame(3, $group->values['country']);
        self::assertSame(4, $group->values['city']);
    }

    public function testSubgroupsAreConnectedAfterTheGroupIsPersisted(): void
    {
        $group = new GroupElementSpy();

        $this->createWriter([8 => $this->createStub(groupElement::class)])
            ->apply($group, new GroupFieldsDto(subGroupIds: [8]));

        self::assertSame([8], $group->values['subGroupsSelector']);
        self::assertSame(['persistElementData', 'persistSubGroupConnections', 'recalculate'], $group->calls);
    }

    public function testSubgroupOfAnotherKindIsBadRequestAndWritesNothing(): void
    {
        $group = new GroupElementSpy();
        $writer = $this->createWriter([8 => $this->createStub(authorElement::class)]);

        try {
            $writer->apply($group, new GroupFieldsDto(title: 'Fenomen', subGroupIds: [8]));
            self::fail('GroupDataException was not thrown');
        } catch (GroupDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame([], $group->values);
    }

    /**
     * @param array<int, structureElement> $elements
     */
    private function createWriter(array $elements = []): GroupFieldsWriter
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturnCallback(
            static fn(int $id): ?structureElement => $elements[$id] ?? null,
        );

        return new GroupFieldsWriter(
            new EntityReferenceResolver($structureManager, $this->createStub(HardwareCatalogService::class)),
            new GroupRepository($structureManager, $this->createStub(GroupsService::class)),
        );
    }
}
