<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Groups;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Assistant\Groups\FoundGroupsDto;
use ZxArt\Assistant\Groups\SearchGroupsAction;
use ZxArt\Groups\Dto\FoundGroupDto;
use ZxArt\Groups\Dto\GroupSearchFilterDto;
use ZxArt\Groups\GroupTypes;
use ZxArt\Groups\Services\FoundGroupsReader;
use ZxArt\Groups\Services\GroupSearchService;
use ZxArt\Prods\ProdLink;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Shared\Dto\IdPageDto;

final class SearchGroupsActionTest extends TestCase
{
    public function testItIsTheReadActionSearchGroups(): void
    {
        $action = $this->action($this->createStub(GroupSearchService::class));

        self::assertInstanceOf(ReadAction::class, $action);
        self::assertSame('search_groups', $action->name());
    }

    public function testTheFilterIsOfferedFlatBesideThePage(): void
    {
        $schema = $this->schema();

        self::assertSame(['name', 'countryIds', 'cityIds', 'types', 'foundedFrom', 'foundedTo', 'endedFrom', 'endedTo', 'activeFrom', 'activeTo', 'memberIds', 'prodIds', 'prodLink', 'parentGroupId', 'subGroupId', 'prods', 'limit', 'offset'], array_keys($schema['properties']));
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

        self::assertSame(GroupTypes::LIST, $properties['types']['items']['enum']);
        self::assertSame([...array_column(ProdLink::cases(), 'value'), null], $properties['prodLink']['enum']);
    }

    public function testItAnswersThePageTheSearchFoundForTheFilterOfTheArguments(): void
    {
        $filter = new GroupSearchFilterDto(
            name: 'Triumph',
            types: ['scene'],
            foundedFrom: 631152000,
            memberIds: [5],
            prodLink: ProdLink::Published,
            prods: new ProdSearchFilterDto(yearFrom: 1995),
            parentGroupId: 9,
        );
        $summaries = [new FoundGroupDto(id: 7, title: 'Triumph', abbreviation: '', type: 'scene', country: null, city: null, aliases: [])];
        $search = $this->createMock(GroupSearchService::class);
        $search->expects(self::once())->method('search')->with($filter, 40, 20)->willReturn(new IdPageDto(total: 41, ids: [7]));
        $reader = $this->createMock(FoundGroupsReader::class);
        $reader->expects(self::once())->method('read')->with([7])->willReturn($summaries);

        $result = $this->read(new SearchGroupsAction($search, $reader), <<<'JSON'
            {"name": "Triumph", "types": ["scene"], "foundedFrom": 631152000, "memberIds": [5], "prodLink": "published",
             "prods": {"yearFrom": 1995}, "parentGroupId": 9, "limit": 20, "offset": 40}
            JSON);

        self::assertEquals(new FoundGroupsDto(total: 41, results: $summaries), $result);
    }

    public function testTheLimitIsKeptBetweenOneAndAHundredAndTwentyWhenNotGiven(): void
    {
        $limits = [];
        $search = $this->createStub(GroupSearchService::class);
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

        $this->read($this->action($this->createStub(GroupSearchService::class)), '{"prods": {"addedFrom": 0}}');
    }

    /**
     * @return array<string, mixed>
     */
    private function schema(): array
    {
        return Arguments::schema($this->action($this->createStub(GroupSearchService::class))->schema());
    }

    private function action(GroupSearchService $search): SearchGroupsAction
    {
        $reader = $this->createStub(FoundGroupsReader::class);
        $reader->method('read')->willReturn([]);

        return new SearchGroupsAction($search, $reader);
    }

    private function read(SearchGroupsAction $action, string $json): object
    {
        return $action->read(Arguments::parse($json, $action->schema()));
    }
}
