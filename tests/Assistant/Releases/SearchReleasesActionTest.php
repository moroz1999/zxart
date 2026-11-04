<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Releases;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Assistant\Releases\FoundReleasesDto;
use ZxArt\Assistant\Releases\SearchReleasesAction;
use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Releases\Dto\FoundReleaseDto;
use ZxArt\Releases\Dto\ReleaseSearchFilterDto;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Services\FoundReleasesReader;
use ZxArt\Releases\Services\ReleaseSearchService;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Shared\Dto\IdPageDto;

final class SearchReleasesActionTest extends TestCase
{
    public function testItIsTheReadActionSearchReleases(): void
    {
        $action = $this->action($this->createStub(ReleaseSearchService::class));

        self::assertInstanceOf(ReadAction::class, $action);
        self::assertSame('search_releases', $action->name());
    }

    public function testTheFilterIsOfferedFlatBesideThePage(): void
    {
        $schema = $this->schema();

        self::assertSame(['title', 'fileName', 'md5', 'prodIds', 'yearFrom', 'yearTo', 'releaseTypes', 'formats', 'hardware', 'languages', 'publisherIds', 'authorIds', 'anyAuthor', 'authorRoles', 'prods', 'limit', 'offset'], array_keys($schema['properties']));
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

        self::assertSame(ReleaseTypes::getAllValues(), $properties['releaseTypes']['items']['enum']);
        self::assertSame(AuthorshipRoles::LIST, $properties['authorRoles']['items']['enum']);
    }

    public function testItAnswersThePageTheSearchFoundForTheFilterOfTheArguments(): void
    {
        $filter = new ReleaseSearchFilterDto(
            title: 'Elite',
            md5: 'abc',
            prods: new ProdSearchFilterDto(title: 'Elite', legalStatuses: [LegalStatus::allowed]),
            releaseTypes: [ReleaseTypes::crack],
            formats: ['tap'],
            authorRoles: ['code'],
        );
        $summaries = [new FoundReleaseDto(id: 7, title: 'Elite', year: 1985, prod: null, releaseType: 'crack', formats: ['tap'], fileName: 'elite.tap', authors: [], publishers: [])];
        $search = $this->createMock(ReleaseSearchService::class);
        $search->expects(self::once())->method('search')->with($filter, 40, 20)->willReturn(new IdPageDto(total: 41, ids: [7]));
        $reader = $this->createMock(FoundReleasesReader::class);
        $reader->expects(self::once())->method('read')->with([7])->willReturn($summaries);

        $result = $this->read(new SearchReleasesAction($search, $reader), <<<'JSON'
            {"title": "Elite", "md5": "abc", "prods": {"title": "Elite", "legalStatuses": ["allowed"]}, "releaseTypes": ["crack"],
             "formats": ["tap"], "authorRoles": ["code"], "limit": 20, "offset": 40}
            JSON);

        self::assertEquals(new FoundReleasesDto(total: 41, results: $summaries), $result);
    }

    public function testTheLimitIsKeptBetweenOneAndAHundredAndTwentyWhenNotGiven(): void
    {
        $limits = [];
        $search = $this->createStub(ReleaseSearchService::class);
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

        $this->read($this->action($this->createStub(ReleaseSearchService::class)), '{"prods": {"addedFrom": 0}}');
    }

    /**
     * @return array<string, mixed>
     */
    private function schema(): array
    {
        return Arguments::schema($this->action($this->createStub(ReleaseSearchService::class))->schema());
    }

    private function action(ReleaseSearchService $search): SearchReleasesAction
    {
        $reader = $this->createStub(FoundReleasesReader::class);
        $reader->method('read')->willReturn([]);

        return new SearchReleasesAction($search, $reader);
    }

    private function read(SearchReleasesAction $action, string $json): object
    {
        return $action->read(Arguments::parse($json, $action->schema()));
    }
}
