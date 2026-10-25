<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

require_once __DIR__ . '/../Doubles/Elements/ZxProdElementSpy.php';

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use partyElement;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Hardware\Dto\HardwareItemDto;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Prods\Services\ProdFieldsWriter;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Tests\Doubles\Elements\ZxProdElementSpy;
use zxProdCategoryElement;
use zxProdElement;
use zxReleaseElement;

final class ProdFieldsWriterTest extends TestCase
{
    public function testWritesOnlyTheGivenFieldsAndPersists(): void
    {
        $prod = new ZxProdElementSpy();

        $this->createWriter()->apply($prod, new ProdFieldsDto(altTitle: 'Dizzy I', year: 1987));

        self::assertSame(['altTitle' => 'Dizzy I', 'year' => 1987], $prod->values);
        self::assertSame(['updateYear', 'persistElementData'], $prod->calls);
    }

    public function testTitleAlsoRenamesTheStructureName(): void
    {
        $prod = new ZxProdElementSpy();

        $this->createWriter()->apply($prod, new ProdFieldsDto(title: 'Dizzy'));

        self::assertSame('Dizzy', $prod->values['title']);
        self::assertSame('Dizzy', $prod->values['structureName']);
    }

    public function testFlagsAndLegalStatusAreStoredInTheirElementForm(): void
    {
        $prod = new ZxProdElementSpy();

        $this->createWriter()->apply($prod, new ProdFieldsDto(
            htmlDescription: true,
            legalStatus: LegalStatus::allowedzxart,
            denyComments: false,
        ));

        self::assertSame(1, $prod->values['htmlDescription']);
        self::assertSame('allowedzxart', $prod->values['legalStatus']);
        self::assertSame(0, $prod->values['denyComments']);
    }

    public function testGroupsAcceptGroupsAndGroupAliasesAsLoadedElements(): void
    {
        $group = $this->createStub(groupElement::class);
        $alias = $this->createStub(groupAliasElement::class);
        $prod = new ZxProdElementSpy();

        $this->createWriter([5 => $group, 6 => $alias])->apply($prod, new ProdFieldsDto(groupIds: [5, 6]));

        self::assertSame([$group, $alias], $prod->values['groups']);
    }

    public function testEmptyGroupListClearsTheGroups(): void
    {
        $prod = new ZxProdElementSpy();

        $this->createWriter()->apply($prod, new ProdFieldsDto(groupIds: []));

        self::assertSame([], $prod->values['groups']);
    }

    public function testGroupIdOfAnotherEntityIsBadRequestAndWritesNothing(): void
    {
        $prod = new ZxProdElementSpy();
        $writer = $this->createWriter([5 => $this->createStub(authorElement::class)]);

        $this->assertStatus(400, fn() => $writer->apply($prod, new ProdFieldsDto(title: 'Dizzy', groupIds: [5])));
        self::assertSame([], $prod->values);
        self::assertSame([], $prod->calls);
    }

    public function testMissingGroupIsBadRequest(): void
    {
        $writer = $this->createWriter();

        $this->assertStatus(400, fn() => $writer->apply(new ZxProdElementSpy(), new ProdFieldsDto(groupIds: [5])));
    }

    public function testPublishersAcceptGroupsAuthorsAndTheirAliases(): void
    {
        $elements = [
            1 => $this->createStub(groupElement::class),
            2 => $this->createStub(groupAliasElement::class),
            3 => $this->createStub(authorElement::class),
            4 => $this->createStub(authorAliasElement::class),
        ];
        $prod = new ZxProdElementSpy();

        $this->createWriter($elements)->apply($prod, new ProdFieldsDto(publisherIds: [1, 2, 3, 4]));

        self::assertSame(array_values($elements), $prod->values['publishers']);
    }

    public function testCompilationItemsAcceptProdsAndReleases(): void
    {
        $item = $this->createStub(zxProdElement::class);
        $release = $this->createStub(zxReleaseElement::class);
        $prod = new ZxProdElementSpy();

        $this->createWriter([7 => $item, 8 => $release])->apply($prod, new ProdFieldsDto(compilationItemIds: [7, 8]));

        self::assertSame([$item, $release], $prod->values['compilationItems']);
    }

    public function testSeriesAcceptsOnlyProds(): void
    {
        $writer = $this->createWriter([8 => $this->createStub(zxReleaseElement::class)]);

        $this->assertStatus(400, fn() => $writer->apply(new ZxProdElementSpy(), new ProdFieldsDto(seriesProdIds: [8])));
    }

    public function testKnownHardwareAndLanguagesAreStoredAsCodes(): void
    {
        $prod = new ZxProdElementSpy();

        $this->createWriter()->apply($prod, new ProdFieldsDto(languages: ['en', 'ru'], hardware: ['zx48']));

        self::assertSame(['en', 'ru'], $prod->values['language']);
        self::assertSame(['zx48'], $prod->values['hardwareRequired']);
    }

    public function testUnknownHardwareCodeIsBadRequest(): void
    {
        $writer = $this->createWriter();

        $this->assertStatus(400, fn() => $writer->apply(new ZxProdElementSpy(), new ProdFieldsDto(hardware: ['zx48', 'amiga'])));
    }

    public function testUnknownLanguageIsBadRequest(): void
    {
        $writer = $this->createWriter();

        $this->assertStatus(400, fn() => $writer->apply(new ZxProdElementSpy(), new ProdFieldsDto(languages: ['xx'])));
    }

    public function testPartyIsLinkedAfterItIsSet(): void
    {
        $prod = new ZxProdElementSpy();

        $this->createWriter([9 => $this->createStub(partyElement::class)])
            ->apply($prod, new ProdFieldsDto(partyId: 9, partyPlace: 2, compo: 'demo'));

        self::assertSame(9, $prod->values['party']);
        self::assertSame(2, $prod->values['partyplace']);
        self::assertSame('demo', $prod->values['compo']);
        self::assertSame(['renewPartyLink', 'updateYear', 'persistElementData'], $prod->calls);
    }

    public function testZeroPartyUnlinksTheParty(): void
    {
        $prod = new ZxProdElementSpy();

        $this->createWriter()->apply($prod, new ProdFieldsDto(partyId: 0));

        self::assertSame(0, $prod->values['party']);
        self::assertContains('renewPartyLink', $prod->calls);
    }

    public function testPartyIdOfAnotherEntityIsBadRequest(): void
    {
        $writer = $this->createWriter([9 => $this->createStub(groupElement::class)]);

        $this->assertStatus(400, fn() => $writer->apply(new ZxProdElementSpy(), new ProdFieldsDto(partyId: 9)));
    }

    public function testTagsReplaceTheTagList(): void
    {
        $prod = new ZxProdElementSpy();

        $this->createWriter()->apply($prod, new ProdFieldsDto(tags: ['arcade', 'puzzle']));

        self::assertSame(['arcade', 'puzzle'], $prod->tagTitles);
        self::assertSame(['updateTagsFromList', 'updateYear', 'persistElementData'], $prod->calls);
    }

    public function testCategoriesAreCheckedAndPersistedAfterTheElement(): void
    {
        $prod = new ZxProdElementSpy(connectedCategoryIds: [3]);

        $this->createWriter([3 => $this->createStub(zxProdCategoryElement::class)])
            ->apply($prod, new ProdFieldsDto(categoryIds: [3]));

        self::assertSame([3], $prod->values['categories']);
        self::assertSame(['updateYear', 'persistElementData', 'checkAndPersistCategories'], $prod->calls);
    }

    public function testCategoryIdOfAnotherEntityIsBadRequest(): void
    {
        $writer = $this->createWriter([3 => $this->createStub(groupElement::class)]);

        $this->assertStatus(400, fn() => $writer->apply(new ZxProdElementSpy(), new ProdFieldsDto(categoryIds: [3])));
    }

    public function testChangedCategoriesQueueTheAiTextsAgain(): void
    {
        $queue = $this->createStub(QueueService::class);
        $updates = [];
        $queue->method('updateStatus')->willReturnCallback(
            function (int $elementId, QueueType $type, QueueStatus $status) use (&$updates): void {
                $updates[] = [$elementId, $type, $status];
            },
        );
        $category = $this->createStub(zxProdCategoryElement::class);

        $this->createWriter([3 => $category, 4 => $category], $queue)
            ->apply(new ZxProdElementSpy(connectedCategoryIds: [3]), new ProdFieldsDto(categoryIds: [3, 4]));

        self::assertSame([
            [42, QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SKIP],
            [42, QueueType::AI_SEO, QueueStatus::STATUS_TODO],
            [42, QueueType::AI_INTRO, QueueStatus::STATUS_TODO],
        ], $updates);
    }

    public function testUnchangedCategoriesOnlySkipTheAiCategoriesPass(): void
    {
        // categories or tags chosen by a person need no AI pass over them
        $queue = $this->createMock(QueueService::class);
        $queue->expects($this->once())
            ->method('updateStatus')
            ->with(42, QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SKIP);

        $this->createWriter([3 => $this->createStub(zxProdCategoryElement::class)], $queue)
            ->apply(new ZxProdElementSpy(connectedCategoryIds: [3]), new ProdFieldsDto(categoryIds: [3]));
    }

    public function testTagsAloneSkipTheAiCategoriesPass(): void
    {
        $queue = $this->createMock(QueueService::class);
        $queue->expects($this->once())
            ->method('updateStatus')
            ->with(42, QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SKIP);

        $this->createWriter(queue: $queue)->apply(new ZxProdElementSpy(), new ProdFieldsDto(tags: ['arcade']));
    }

    public function testFieldsWithoutCategoriesOrTagsLeaveTheAiQueueAlone(): void
    {
        $queue = $this->createMock(QueueService::class);
        $queue->expects($this->never())->method('updateStatus');

        $this->createWriter(queue: $queue)->apply(new ZxProdElementSpy(), new ProdFieldsDto(title: 'Dizzy'));
    }

    /**
     * @param array<int, structureElement> $elements
     */
    private function createWriter(array $elements = [], ?QueueService $queue = null): ProdFieldsWriter
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturnCallback(
            static fn(int $id): ?structureElement => $elements[$id] ?? null,
        );
        $hardware = $this->createStub(HardwareCatalogService::class);
        $hardware->method('getItemByCode')->willReturnCallback(
            fn(string $code): ?HardwareItemDto => $code === 'zx48' ? $this->createStub(HardwareItemDto::class) : null,
        );

        return new ProdFieldsWriter(
            new EntityReferenceResolver($structureManager, $hardware),
            $queue ?? $this->createStub(QueueService::class),
            new ProdRepository($structureManager),
        );
    }

    private function assertStatus(int $expectedStatus, callable $call): void
    {
        try {
            $call();
            self::fail('ProdDataException was not thrown');
        } catch (ProdDataException $exception) {
            self::assertSame($expectedStatus, $exception->getStatusCode());
        }
    }
}
