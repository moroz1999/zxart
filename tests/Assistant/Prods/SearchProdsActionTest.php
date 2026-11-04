<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Prods;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Assistant\Prods\FoundProdsDto;
use ZxArt\Assistant\Prods\SearchProdsAction;
use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Prods\Dto\FoundProdDto;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\ProdLink;
use ZxArt\Prods\Services\FoundProdsReader;
use ZxArt\Prods\Services\ProdSearchService;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Shared\Dto\IdPageDto;

final class SearchProdsActionTest extends TestCase
{
    public function testItIsTheReadActionSearchProds(): void
    {
        $action = new SearchProdsAction($this->createStub(ProdSearchService::class), $this->createStub(FoundProdsReader::class));

        self::assertInstanceOf(ReadAction::class, $action);
        self::assertSame('search_prods', $action->name());
    }

    public function testTheFilterIsOfferedFlatBesideThePageWithoutTheDatesOfAdding(): void
    {
        $schema = $this->schema();

        self::assertSame([
            'title', 'categoryIds', 'yearFrom', 'yearTo', 'releaseYearFrom', 'releaseYearTo', 'hardware', 'languages',
            'legalStatuses', 'releaseTypes', 'formats', 'authorIds', 'anyAuthor', 'authorRoles', 'groupIds', 'groupLink',
            'publisherIds', 'countryIds', 'cityIds', 'partyIds', 'compo', 'maxPartyPlace', 'seriesId', 'compilationId',
            'includedProdId', 'tagIds', 'limit', 'offset',
        ], array_keys($schema['properties']));
        self::assertArrayNotHasKey('required', $schema);
        self::assertFalse($schema['additionalProperties']);
    }

    public function testTheClosedListsOfTheFilterAreListedByTheirValues(): void
    {
        $properties = $this->schema()['properties'];

        self::assertSame(array_column(LegalStatus::cases(), 'value'), $properties['legalStatuses']['items']['enum']);
        self::assertSame(ReleaseTypes::getAllValues(), $properties['releaseTypes']['items']['enum']);
        self::assertSame(AuthorshipRoles::LIST, $properties['authorRoles']['items']['enum']);
        self::assertSame([...array_column(ProdLink::cases(), 'value'), null], $properties['groupLink']['enum']);
    }

    public function testItAnswersThePageTheSearchFoundForTheFilterOfTheArguments(): void
    {
        $filter = new ProdSearchFilterDto(
            title: 'Elite',
            categoryIds: [92159],
            yearFrom: 1985,
            legalStatuses: [LegalStatus::allowed],
            releaseTypes: [ReleaseTypes::original],
            authorIds: [7],
            anyAuthor: true,
            authorRoles: ['code'],
            groupIds: [9],
            groupLink: ProdLink::Made,
            maxPartyPlace: 3,
            tagIds: [11],
        );
        $summaries = [new FoundProdDto(id: 7, title: 'Elite', year: 1985, authors: [], groups: [], publishers: [], categories: [])];
        $search = $this->createMock(ProdSearchService::class);
        $search->expects(self::once())->method('search')->with($filter, 40, 20)->willReturn(new IdPageDto(total: 41, ids: [7]));
        $reader = $this->createMock(FoundProdsReader::class);
        $reader->expects(self::once())->method('read')->with([7])->willReturn($summaries);

        $result = $this->read(new SearchProdsAction($search, $reader), <<<'JSON'
            {"title": "Elite", "categoryIds": [92159], "yearFrom": 1985, "legalStatuses": ["allowed"],
             "releaseTypes": ["original"], "authorIds": [7], "anyAuthor": true, "authorRoles": ["code"],
             "groupIds": [9], "groupLink": "made", "maxPartyPlace": 3, "tagIds": [11], "limit": 20, "offset": 40}
            JSON);

        self::assertEquals(new FoundProdsDto(total: 41, results: $summaries), $result);
    }

    public function testTheLimitIsKeptBetweenOneAndAHundredAndTwentyWhenNotGiven(): void
    {
        $limits = [];
        $search = $this->createStub(ProdSearchService::class);
        $search->method('search')->willReturnCallback(static function (ProdSearchFilterDto $filter, int $offset, int $limit) use (&$limits): IdPageDto {
            $limits[] = [$offset, $limit];

            return new IdPageDto(total: 0, ids: []);
        });
        $reader = $this->createStub(FoundProdsReader::class);
        $reader->method('read')->willReturn([]);
        $action = new SearchProdsAction($search, $reader);

        $this->read($action, '{}');
        $this->read($action, '{"limit": 0, "offset": -5}');
        $this->read($action, '{"limit": 500}');

        self::assertSame([[0, 20], [0, 1], [0, 100]], $limits);
    }

    public function testAValueOutsideAClosedListIsRefused(): void
    {
        $action = new SearchProdsAction($this->createStub(ProdSearchService::class), $this->createStub(FoundProdsReader::class));

        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage('legalStatuses');

        $this->read($action, '{"legalStatuses": ["sold"]}');
    }

    /**
     * @return array<string, mixed>
     */
    private function schema(): array
    {
        $action = new SearchProdsAction($this->createStub(ProdSearchService::class), $this->createStub(FoundProdsReader::class));

        return Arguments::schema($action->schema());
    }

    private function read(SearchProdsAction $action, string $json): object
    {
        return $action->read(Arguments::parse($json, $action->schema()));
    }
}
