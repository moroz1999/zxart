<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

require_once __DIR__ . '/../Doubles/Elements/ZxReleaseElementSpy.php';

use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Authors\Dto\AuthorshipDto;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ReleaseCloner;
use ZxArt\Releases\Services\ReleaseCreator;
use ZxArt\Releases\Services\ReleaseFieldsReader;
use ZxArt\Shared\EntityType;
use ZxArt\Tests\Doubles\Elements\ZxReleaseElementSpy;
use zxProdElement;

final class ReleaseClonerTest extends TestCase
{
    public function testTheCloneIsCreatedWithTheFieldsTheAuthorsAndTheFileOfTheSource(): void
    {
        $source = $this->source(file: '42', fileName: 'tape.tap');
        $clone = new ZxReleaseElementSpy(elementId: 43);
        $creator = $this->createMock(ReleaseCreator::class);
        $creator->expects($this->once())
            ->method('create')
            ->with(new ReleaseFieldsDto(
                title: 'Tape',
                year: 1999,
                version: '1.1',
                description: '',
                releaseType: ReleaseTypes::crack,
                releaseFormats: ['tap'],
                languages: ['en'],
                hardware: [],
                publisherIds: [],
                prodId: 9,
                denyVoting: false,
                denyComments: false,
            ))
            ->willReturn($clone);
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->method('findByElement')->with(42, EntityType::Release)->willReturn([
            new AuthorshipDto(elementId: 42, authorId: 7, roles: ['release'], startDate: 0, endDate: 0),
        ]);
        $authorship->expects($this->once())
            ->method('setAuthorship')
            ->with(43, 7, EntityType::Release, ['release'], 0, 0);

        $cloned = (new ReleaseCloner($creator, new ReleaseFieldsReader(), $authorship, new ReleaseRepository($this->createStub(structureManager::class))))->clone($source);

        self::assertSame($clone, $cloned);
        self::assertSame(['file' => '42', 'fileName' => 'tape.tap', 'parsed' => 0], $clone->values);
        self::assertSame(['persistElementData', 'updateFileStructure'], $clone->calls);
    }

    public function testASourceWithoutAFileGivesACloneWithoutOne(): void
    {
        $clone = new ZxReleaseElementSpy(elementId: 43);
        $creator = $this->createStub(ReleaseCreator::class);
        $creator->method('create')->willReturn($clone);

        (new ReleaseCloner($creator, new ReleaseFieldsReader(), $this->createStub(AuthorshipRepository::class), new ReleaseRepository($this->createStub(structureManager::class))))
            ->clone($this->source(file: '', fileName: ''));

        self::assertSame([], $clone->values);
        self::assertSame([], $clone->calls);
    }

    private function source(string $file, string $fileName): ZxReleaseElementSpy
    {
        $source = new ZxReleaseElementSpy(elementId: 42);
        $source->values = [
            'title' => 'Tape',
            'year' => '1999',
            'version' => '1.1',
            'releaseType' => 'crack',
            'releaseFormat' => ['tap'],
            'language' => ['en'],
            'zxProd' => [$this->element(9)],
            'file' => $file,
            'fileName' => $fileName,
        ];

        return $source;
    }

    private function element(int $id): structureElement
    {
        $element = $this->createStub(zxProdElement::class);
        $element->method('getId')->willReturn($id);

        return $element;
    }
}
