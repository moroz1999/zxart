<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Authors;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Assistant\Authors\FoundAuthorsDto;
use ZxArt\Assistant\Authors\SearchAuthorsAction;
use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Authors\Dto\AuthorSearchFilterDto;
use ZxArt\Authors\Dto\FoundAuthorDto;
use ZxArt\Authors\Services\AuthorSearchService;
use ZxArt\Authors\Services\FoundAuthorsReader;
use ZxArt\Prods\ProdLink;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Shared\Dto\IdPageDto;

final class SearchAuthorsActionTest extends TestCase
{
    public function testItIsTheReadActionSearchAuthors(): void
    {
        $action = $this->action($this->createStub(AuthorSearchService::class));

        self::assertInstanceOf(ReadAction::class, $action);
        self::assertSame('search_authors', $action->name());
    }

    public function testTheFilterIsOfferedFlatBesideThePage(): void
    {
        $schema = $this->schema();

        self::assertSame(['name', 'countryIds', 'cityIds', 'groupIds', 'activeFrom', 'activeTo', 'prodIds', 'prodRoles', 'prodLink', 'coAuthorId', 'prods', 'limit', 'offset'], array_keys($schema['properties']));
        self::assertArrayNotHasKey('required', $schema);
        self::assertFalse($schema['additionalProperties']);
    }

    public function testTheNestedProductionFilterIsTheOneOfTheProductionSearchWithoutThePage(): void
    {
        $prods = $this->schema()['properties']['prods'];

        self::assertSame(['object', 'null'], $prods['type']);
        self::assertFalse($prods['additionalProperties']);
        self::assertContains('legalStatuses', array_keys($prods['properties']));
        self::assertNotContains('limit', array_keys($prods['properties']));
        self::assertNotContains('addedFrom', array_keys($prods['properties']));
    }

    public function testTheClosedListsOfTheFilterAreListedByTheirValues(): void
    {
        $properties = $this->schema()['properties'];

        self::assertSame(AuthorshipRoles::LIST, $properties['prodRoles']['items']['enum']);
        self::assertSame([...array_column(ProdLink::cases(), 'value'), null], $properties['prodLink']['enum']);
    }

    public function testItAnswersThePageTheSearchFoundForTheFilterOfTheArguments(): void
    {
        $filter = new AuthorSearchFilterDto(
            name: 'Wanderer',
            countryIds: [3],
            activeFrom: 1990,
            prodRoles: ['code'],
            prodLink: ProdLink::Made,
            prods: new ProdSearchFilterDto(legalStatuses: [LegalStatus::allowed]),
            coAuthorId: 9,
        );
        $summaries = [new FoundAuthorDto(id: 7, title: 'Wanderer', realName: '', country: null, city: null, aliases: [], groups: [])];
        $search = $this->createMock(AuthorSearchService::class);
        $search->expects(self::once())->method('search')->with($filter, 40, 20)->willReturn(new IdPageDto(total: 41, ids: [7]));
        $reader = $this->createMock(FoundAuthorsReader::class);
        $reader->expects(self::once())->method('read')->with([7])->willReturn($summaries);

        $result = $this->read(new SearchAuthorsAction($search, $reader), <<<'JSON'
            {"name": "Wanderer", "countryIds": [3], "activeFrom": 1990, "prodRoles": ["code"], "prodLink": "made",
             "prods": {"legalStatuses": ["allowed"]}, "coAuthorId": 9, "limit": 20, "offset": 40}
            JSON);

        self::assertEquals(new FoundAuthorsDto(total: 41, results: $summaries), $result);
    }

    public function testTheLimitIsKeptBetweenOneAndAHundredAndTwentyWhenNotGiven(): void
    {
        $limits = [];
        $search = $this->createStub(AuthorSearchService::class);
        $search->method('search')->willReturnCallback(static function (object $filter, int $offset, int $limit) use (&$limits): IdPageDto {
            $limits[] = [$offset, $limit];

            return new IdPageDto(total: 0, ids: []);
        });
        $action = $this->action($search);

        $this->read($action, '{}');
        $this->read($action, '{"limit": 0, "offset": -5}');
        $this->read($action, '{"limit": 500}');

        self::assertSame([[0, 20], [0, 1], [0, 100]], $limits);
    }

    public function testAFieldTheNestedFilterDoesNotHaveIsRefusedNamingIt(): void
    {
        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage('prods.addedFrom');

        $this->read($this->action($this->createStub(AuthorSearchService::class)), '{"prods": {"addedFrom": 0}}');
    }

    /**
     * @return array<string, mixed>
     */
    private function schema(): array
    {
        return Arguments::schema($this->action($this->createStub(AuthorSearchService::class))->schema());
    }

    private function action(AuthorSearchService $search): SearchAuthorsAction
    {
        $reader = $this->createStub(FoundAuthorsReader::class);
        $reader->method('read')->willReturn([]);

        return new SearchAuthorsAction($search, $reader);
    }

    private function read(SearchAuthorsAction $action, string $json): object
    {
        return $action->read(Arguments::parse($json, $action->schema()));
    }
}
