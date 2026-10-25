<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

require_once __DIR__ . '/../Doubles/Elements/ZxReleaseElementSpy.php';

use authorElement;
use groupAliasElement;
use groupElement;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Hardware\Dto\HardwareItemDto;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ReleaseFieldsWriter;
use ZxArt\Releases\Services\ReleaseFormatsProvider;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Tests\Doubles\Elements\ZxReleaseElementSpy;
use zxProdElement;

final class ReleaseFieldsWriterTest extends TestCase
{
    public function testWritesOnlyTheGivenFieldsAndPersists(): void
    {
        $release = new ZxReleaseElementSpy();

        $this->createWriter()->apply($release, new ReleaseFieldsDto(year: 1988, version: '1.1'));

        self::assertSame(['year' => 1988, 'version' => '1.1'], $release->values);
        self::assertSame(['persistElementData'], $release->calls);
    }

    public function testTitleAlsoRenamesTheStructureName(): void
    {
        $release = new ZxReleaseElementSpy();

        $this->createWriter()->apply($release, new ReleaseFieldsDto(title: 'Dizzy (crack)'));

        self::assertSame('Dizzy (crack)', $release->values['title']);
        self::assertSame('Dizzy (crack)', $release->values['structureName']);
    }

    public function testBlankTitleLeavesTheStructureNameToTheProduction(): void
    {
        // a release without a title of its own is named after its production when persisted
        $release = new ZxReleaseElementSpy();

        $this->createWriter()->apply($release, new ReleaseFieldsDto(title: ''));

        self::assertSame('', $release->values['title']);
        self::assertArrayNotHasKey('structureName', $release->values);
    }

    public function testTypeFlagsAndCodesAreStoredInTheirElementForm(): void
    {
        $release = new ZxReleaseElementSpy();

        $this->createWriter()->apply($release, new ReleaseFieldsDto(
            releaseType: ReleaseTypes::crack,
            releaseFormats: ['tap'],
            languages: ['en'],
            hardware: ['zx48'],
            denyVoting: true,
        ));

        self::assertSame('crack', $release->values['releaseType']);
        self::assertSame(['tap'], $release->values['releaseFormat']);
        self::assertSame(['en'], $release->values['language']);
        self::assertSame(['zx48'], $release->values['hardwareRequired']);
        self::assertSame(1, $release->values['denyVoting']);
    }

    public function testUnknownReleaseFormatIsBadRequestAndWritesNothing(): void
    {
        $release = new ZxReleaseElementSpy();

        $this->assertStatus(400, fn() => $this->createWriter()->apply($release, new ReleaseFieldsDto(title: 'X', releaseFormats: ['docx'])));
        self::assertSame([], $release->values);
        self::assertSame([], $release->calls);
    }

    public function testUnknownHardwareIsBadRequest(): void
    {
        $this->assertStatus(400, fn() => $this->createWriter()->apply(new ZxReleaseElementSpy(), new ReleaseFieldsDto(hardware: ['amiga'])));
    }

    public function testPublishersAcceptGroupsAndAuthors(): void
    {
        $group = $this->createStub(groupElement::class);
        $author = $this->createStub(authorElement::class);
        $release = new ZxReleaseElementSpy();

        $this->createWriter([5 => $group, 6 => $author])->apply($release, new ReleaseFieldsDto(publisherIds: [5, 6]));

        self::assertSame([$group, $author], $release->values['publishers']);
    }

    public function testPublisherOfAnotherKindIsBadRequest(): void
    {
        $writer = $this->createWriter([5 => $this->createStub(zxProdElement::class)]);

        $this->assertStatus(400, fn() => $writer->apply(new ZxReleaseElementSpy(), new ReleaseFieldsDto(publisherIds: [5])));
    }

    public function testProductionIsSetAsTheReleaseParent(): void
    {
        $prod = $this->createStub(zxProdElement::class);
        $release = new ZxReleaseElementSpy();

        $this->createWriter([9 => $prod])->apply($release, new ReleaseFieldsDto(prodId: 9));

        self::assertSame([$prod], $release->values['zxProd']);
    }

    public function testProductionIdOfAnotherKindIsBadRequest(): void
    {
        $writer = $this->createWriter([9 => $this->createStub(groupAliasElement::class)]);

        $this->assertStatus(400, fn() => $writer->apply(new ZxReleaseElementSpy(), new ReleaseFieldsDto(prodId: 9)));
    }

    /**
     * @param array<int, structureElement> $elements
     */
    private function createWriter(array $elements = []): ReleaseFieldsWriter
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturnCallback(
            static fn(int $id): ?structureElement => $elements[$id] ?? null,
        );
        $hardware = $this->createStub(HardwareCatalogService::class);
        $hardware->method('getItemByCode')->willReturnCallback(
            fn(string $code): ?HardwareItemDto => $code === 'zx48' ? $this->createStub(HardwareItemDto::class) : null,
        );

        return new ReleaseFieldsWriter(
            new EntityReferenceResolver($structureManager, $hardware),
            new ReleaseFormatsProvider(),
            new ReleaseRepository($structureManager),
        );
    }

    private function assertStatus(int $expectedStatus, callable $call): void
    {
        try {
            $call();
            self::fail('ReleaseDataException was not thrown');
        } catch (ReleaseDataException $exception) {
            self::assertSame($expectedStatus, $exception->getStatusCode());
        }
    }
}
