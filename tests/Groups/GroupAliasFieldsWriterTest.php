<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

require_once __DIR__ . '/../Doubles/Elements/GroupAliasElementSpy.php';

use authorElement;
use groupElement;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Groups\Services\GroupAliasFieldsWriter;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Tests\Doubles\Elements\GroupAliasElementSpy;

final class GroupAliasFieldsWriterTest extends TestCase
{
    public function testWritesTheGivenFieldsAndPersists(): void
    {
        $alias = new GroupAliasElementSpy();

        $this->createWriter([5 => $this->createStub(groupElement::class)])
            ->apply($alias, new GroupAliasFieldsDto(title: 'FNM', groupId: 5, endDate: 0));

        self::assertSame(
            ['title' => 'FNM', 'structureName' => 'FNM', 'groupId' => 5, 'endDate' => 0],
            $alias->values,
        );
        self::assertSame(['persistElementData'], $alias->calls);
    }

    public function testGroupIdOfAnotherKindIsBadRequest(): void
    {
        $writer = $this->createWriter([5 => $this->createStub(authorElement::class)]);

        try {
            $writer->apply(new GroupAliasElementSpy(), new GroupAliasFieldsDto(groupId: 5));
            self::fail('GroupAliasDataException was not thrown');
        } catch (GroupAliasDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
    }

    public function testMovingTheAliasMakesBothOwnersReadTheirAliasesAgain(): void
    {
        $alias = new GroupAliasElementSpy();
        $alias->values = ['groupId' => '5'];
        $previousOwner = $this->createMock(groupElement::class);
        $previousOwner->expects(self::once())->method('resetAliasElements');
        $newOwner = $this->createMock(groupElement::class);
        $newOwner->expects(self::once())->method('resetAliasElements');

        $this->createWriter([5 => $previousOwner, 7 => $newOwner])
            ->apply($alias, new GroupAliasFieldsDto(groupId: 7));
    }

    public function testAKeptOwnerReadsItsAliasesAgainOnce(): void
    {
        $alias = new GroupAliasElementSpy();
        $alias->values = ['groupId' => '7'];
        $owner = $this->createMock(groupElement::class);
        $owner->expects(self::once())->method('resetAliasElements');

        $this->createWriter([7 => $owner])->apply($alias, new GroupAliasFieldsDto(title: 'Fx', groupId: 7));
    }

    /**
     * @param array<int, structureElement> $elements
     */
    private function createWriter(array $elements = []): GroupAliasFieldsWriter
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturnCallback(
            static fn(int $id): ?structureElement => $elements[$id] ?? null,
        );

        return new GroupAliasFieldsWriter(
            new EntityReferenceResolver($structureManager, $this->createStub(HardwareCatalogService::class)),
            new GroupAliasRepository($structureManager, $this->createStub(GroupsService::class)),
            $structureManager,
        );
    }
}
