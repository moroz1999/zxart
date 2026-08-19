<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use authorElement;
use fileElement;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use structureElement;
use ZxArt\Prods\Dto\ProdSplitGroupDto;
use ZxArt\Prods\ProdElementService;
use ZxArt\Prods\ProdSplitGroup;
use ZxArt\Prods\Services\ProdSplitService;
use ZxArt\Urls\EntityUrlResolver;
use zxProdElement;

/**
 * What the split form offers, and under which key each offer is sent back:
 * those keys are what the `split` action moves the item by, so they are the
 * part of this service that must not drift.
 */
#[AllowMockObjectsWithoutExpectations]
class ProdSplitServiceTest extends TestCase
{
    public function testEmptyGroupsAreNotOffered(): void
    {
        $data = $this->getSplitData($this->makeProd());

        $this->assertSame([], $data->groups);
        $this->assertSame(42, $data->id);
    }

    public function testOnlyFilledPropertiesAreOffered(): void
    {
        $prod = $this->makeProd(properties: ['title' => 'Elite', 'year' => 0, 'youtubeId' => '']);

        $items = $this->group($this->getSplitData($prod)->groups, ProdSplitGroup::Properties);

        $this->assertCount(1, $items);
        $this->assertSame('title', $items[0]->key);
        $this->assertSame('Elite', $items[0]->title);
    }

    public function testAuthorIsOfferedByItsAuthorshipRecord(): void
    {
        $author = $this->createMock(authorElement::class);
        $author->method('getSearchTitle')->willReturn('Bill &amp; Ben');
        $prod = $this->makeProd(authors: [['id' => 17, 'authorElement' => $author]]);

        $items = $this->group($this->getSplitData($prod)->groups, ProdSplitGroup::Authors);

        $this->assertCount(1, $items);
        // the authorship record, so the author's other credits stay where they are
        $this->assertSame('17', $items[0]->key);
        $this->assertSame('Bill & Ben', $items[0]->title);
        $this->assertSame('/author/1', $items[0]->url);
    }

    public function testScreenshotCarriesItsThumbnail(): void
    {
        $file = $this->createMock(fileElement::class);
        $file->method('getId')->willReturn(555);
        $file->method('getImageUrl')->willReturn('/image/555');
        $file->method('__get')->willReturnMap([['title', 'Loading screen'], ['fileName', 'load.png']]);
        $prod = $this->makeProd(screenshots: [$file]);

        $items = $this->group($this->getSplitData($prod)->groups, ProdSplitGroup::Screenshots);

        $this->assertCount(1, $items);
        $this->assertSame('555', $items[0]->key);
        $this->assertSame('Loading screen', $items[0]->title);
        $this->assertSame('/image/555', $items[0]->imageUrl);
    }

    public function testLinkIsOfferedByOriginAndIdentifierAndDerivedLinksAreNot(): void
    {
        $prod = $this->makeProd(links: [
            ['type' => 'pouet', 'name' => 'Pouet', 'url' => 'https://pouet.net/prod.php?which=7', 'id' => '7'],
            // derived from the zxdb link instead of being stored, so it cannot be moved
            ['type' => 'wos', 'name' => 'WoS', 'url' => 'https://worldofspectrum.org/software?id=9', 'id' => '9'],
        ]);

        $items = $this->group($this->getSplitData($prod)->groups, ProdSplitGroup::Links);

        $this->assertCount(1, $items);
        $this->assertSame('pouet;7', $items[0]->key);
        $this->assertSame('Pouet 7', $items[0]->title);
        $this->assertSame('https://pouet.net/prod.php?which=7', $items[0]->url);
    }

    private function getSplitData(zxProdElement $prod): \ZxArt\Prods\Dto\ProdSplitDataDto
    {
        $elementService = $this->createMock(ProdElementService::class);
        $elementService->method('get')->willReturn($prod);

        $urlResolver = $this->createMock(EntityUrlResolver::class);
        $urlResolver->method('urlFor')->willReturnCallback(
            static fn(structureElement $element): string => '/author/1',
        );

        return (new ProdSplitService($elementService, $urlResolver))->getSplitData(42);
    }

    /**
     * @param ProdSplitGroupDto[] $groups
     * @return \ZxArt\Prods\Dto\ProdSplitItemDto[]
     */
    private function group(array $groups, ProdSplitGroup $group): array
    {
        foreach ($groups as $candidate) {
            if ($candidate->group === $group->value) {
                return $candidate->items;
            }
        }

        $this->fail('Group ' . $group->value . ' was not offered');
    }

    /**
     * @param array<string, scalar> $properties
     * @param list<array{id: int, authorElement: object}> $authors
     * @param list<object> $screenshots
     * @param list<array{type: string, name: string, url: string, id: string}> $links
     */
    private function makeProd(
        array $properties = [],
        array $authors = [],
        array $screenshots = [],
        array $links = [],
    ): zxProdElement {
        /** @var zxProdElement&MockObject $prod */
        $prod = $this->getMockBuilder(zxProdElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getTitle', 'getAuthorsInfo', 'getFilesList', 'getLinksInfo', 'getReleasesList', '__get'])
            ->getMock();

        $prod->method('getId')->willReturn(42);
        $prod->method('getTitle')->willReturn('Elite');
        $prod->method('getAuthorsInfo')->willReturn($authors);
        $prod->method('getFilesList')->willReturn($screenshots);
        $prod->method('getLinksInfo')->willReturn($links);
        $prod->method('getReleasesList')->willReturn([]);
        $prod->method('__get')->willReturnCallback(
            static fn(string $property): mixed => match ($property) {
                'publishers', 'groups' => [],
                default => $properties[$property] ?? null,
            },
        );

        return $prod;
    }
}
