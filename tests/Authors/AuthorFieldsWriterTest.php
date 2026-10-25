<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

require_once __DIR__ . '/../Doubles/Elements/AuthorElementSpy.php';

use cityElement;
use countryElement;
use groupElement;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Authors\Services\AuthorFieldsWriter;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Tests\Doubles\Elements\AuthorElementSpy;

final class AuthorFieldsWriterTest extends TestCase
{
    public function testWritesOnlyTheGivenFieldsPersistsAndRecalculates(): void
    {
        $author = new AuthorElementSpy();

        $this->createWriter()->apply($author, new AuthorFieldsDto(realName: 'Ivan', denyVoting: true));

        self::assertSame(['realName' => 'Ivan', 'denyVoting' => 1], $author->values);
        self::assertSame(['persistElementData', 'recalculate'], $author->calls);
    }

    public function testTitleRenamesTheStructureNameAndMovesTheAuthorToItsLetter(): void
    {
        $author = new AuthorElementSpy();

        $this->createWriter()->apply($author, new AuthorFieldsDto(title: 'Fikus'));

        self::assertSame('Fikus', $author->values['title']);
        self::assertSame('Fikus', $author->values['structureName']);
        self::assertSame(['persistElementData', 'checkParentLetter', 'recalculate'], $author->calls);
    }

    public function testBlankTitleIsTheRealName(): void
    {
        $author = new AuthorElementSpy();

        $this->createWriter()->apply($author, new AuthorFieldsDto(title: '', realName: 'Ivan Petrov'));

        self::assertSame('Ivan Petrov', $author->values['title']);
        self::assertSame('Ivan Petrov', $author->values['structureName']);
    }

    public function testBlankTitleFallsBackToTheStoredRealName(): void
    {
        $author = new AuthorElementSpy();
        $author->values['realName'] = 'Ivan Petrov';

        $this->createWriter()->apply($author, new AuthorFieldsDto(title: ''));

        self::assertSame('Ivan Petrov', $author->values['title']);
    }

    public function testLocationIsStoredAsIds(): void
    {
        $author = new AuthorElementSpy();
        $writer = $this->createWriter([
            3 => $this->createStub(countryElement::class),
            4 => $this->createStub(cityElement::class),
        ]);

        $writer->apply($author, new AuthorFieldsDto(countryId: 3, cityId: 4));

        self::assertSame(3, $author->values['country']);
        self::assertSame(4, $author->values['city']);
    }

    public function testZeroLocationClearsIt(): void
    {
        $author = new AuthorElementSpy();

        $this->createWriter()->apply($author, new AuthorFieldsDto(countryId: 0, cityId: 0));

        self::assertSame(0, $author->values['country']);
        self::assertSame(0, $author->values['city']);
    }

    public function testCountryOfAnotherKindIsBadRequestAndWritesNothing(): void
    {
        $author = new AuthorElementSpy();
        $writer = $this->createWriter([3 => $this->createStub(groupElement::class)]);

        try {
            $writer->apply($author, new AuthorFieldsDto(title: 'Fikus', countryId: 3));
            self::fail('AuthorDataException was not thrown');
        } catch (AuthorDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame([], $author->values);
        self::assertSame([], $author->calls);
    }

    public function testChangedMusicSettingsReconvertTheTunes(): void
    {
        $author = new AuthorElementSpy();

        $this->createWriter()->apply($author, new AuthorFieldsDto(chipType: 'ym'));

        self::assertSame(['persistElementData', 'recalculate', 'reconvertMusic'], $author->calls);
    }

    /**
     * @param array<int, structureElement> $elements
     */
    private function createWriter(array $elements = []): AuthorFieldsWriter
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturnCallback(
            static fn(int $id): ?structureElement => $elements[$id] ?? null,
        );

        return new AuthorFieldsWriter(
            new EntityReferenceResolver($structureManager, $this->createStub(HardwareCatalogService::class)),
            new AuthorRepository($structureManager, $this->createStub(AuthorsService::class)),
        );
    }
}
