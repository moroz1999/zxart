<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

require_once __DIR__ . '/../Doubles/Elements/AuthorAliasElementSpy.php';

use authorElement;
use groupElement;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Repositories\AuthorAliasRepository;
use ZxArt\Authors\Services\AuthorAliasFieldsWriter;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Tests\Doubles\Elements\AuthorAliasElementSpy;

final class AuthorAliasFieldsWriterTest extends TestCase
{
    public function testWritesOnlyTheGivenFieldsAndPersists(): void
    {
        $alias = new AuthorAliasElementSpy();

        $this->createWriter()->apply($alias, new AuthorAliasFieldsDto(startDate: 600000000, displayInMusic: false));

        self::assertSame(['startDate' => 600000000, 'displayInMusic' => 0], $alias->values);
        self::assertSame(['persistElementData'], $alias->calls);
    }

    public function testTitleRenamesTheStructureNameAndMovesTheAliasToItsLetter(): void
    {
        $alias = new AuthorAliasElementSpy();

        $this->createWriter()->apply($alias, new AuthorAliasFieldsDto(title: 'Fx'));

        self::assertSame('Fx', $alias->values['structureName']);
        self::assertSame(['persistElementData', 'checkParentLetter'], $alias->calls);
    }

    public function testAuthorIsStoredAsItsId(): void
    {
        $alias = new AuthorAliasElementSpy();

        $this->createWriter([7 => $this->createStub(authorElement::class)])
            ->apply($alias, new AuthorAliasFieldsDto(authorId: 7));

        self::assertSame(7, $alias->values['authorId']);
    }

    public function testAuthorIdOfAnotherKindIsBadRequest(): void
    {
        $writer = $this->createWriter([7 => $this->createStub(groupElement::class)]);

        try {
            $writer->apply(new AuthorAliasElementSpy(), new AuthorAliasFieldsDto(authorId: 7));
            self::fail('AuthorAliasDataException was not thrown');
        } catch (AuthorAliasDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
    }

    public function testMovingTheAliasMakesBothOwnersReadTheirAliasesAgain(): void
    {
        $alias = new AuthorAliasElementSpy();
        $alias->values = ['authorId' => '5'];
        $previousOwner = $this->createMock(authorElement::class);
        $previousOwner->expects(self::once())->method('resetAliasElements');
        $newOwner = $this->createMock(authorElement::class);
        $newOwner->expects(self::once())->method('resetAliasElements');

        $this->createWriter([5 => $previousOwner, 7 => $newOwner])
            ->apply($alias, new AuthorAliasFieldsDto(authorId: 7));
    }

    public function testAKeptOwnerReadsItsAliasesAgainOnce(): void
    {
        $alias = new AuthorAliasElementSpy();
        $alias->values = ['authorId' => '7'];
        $owner = $this->createMock(authorElement::class);
        $owner->expects(self::once())->method('resetAliasElements');

        $this->createWriter([7 => $owner])->apply($alias, new AuthorAliasFieldsDto(title: 'Fx', authorId: 7));
    }

    /**
     * @param array<int, structureElement> $elements
     */
    private function createWriter(array $elements = []): AuthorAliasFieldsWriter
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturnCallback(
            static fn(int $id): ?structureElement => $elements[$id] ?? null,
        );

        return new AuthorAliasFieldsWriter(
            new EntityReferenceResolver($structureManager, $this->createStub(HardwareCatalogService::class)),
            new AuthorAliasRepository($structureManager, $this->createStub(AuthorsService::class)),
            $structureManager,
        );
    }
}
