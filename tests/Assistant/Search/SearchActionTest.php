<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Search;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Assistant\Search\FoundEntitiesDto;
use ZxArt\Assistant\Search\SearchAction;
use ZxArt\Search\Dto\FoundEntityDto;
use ZxArt\Search\Services\EntitySearchService;
use ZxArt\Shared\EntityType;

final class SearchActionTest extends TestCase
{
    public function testItIsTheReadActionSearch(): void
    {
        $action = new SearchAction($this->createStub(EntitySearchService::class));

        self::assertInstanceOf(ReadAction::class, $action);
        self::assertSame('search', $action->name());
    }

    public function testTheModelCanAskForExactlyTheSixSearchedTypes(): void
    {
        $schema = Arguments::schema((new SearchAction($this->createStub(EntitySearchService::class)))->schema());

        self::assertSame(['phrase', 'types', 'limit'], array_keys($schema['properties']));
        self::assertSame(['prod', 'release', 'author', 'authorAlias', 'group', 'groupAlias'], $schema['properties']['types']['items']['enum']);
        self::assertSame(['phrase'], $schema['required']);
    }

    public function testTheSearchRunsOverTheRequestedTypes(): void
    {
        $found = [new FoundEntityDto(EntityType::AuthorAlias, 7, 'Wanderer', null, 'Writer')];
        $service = $this->createMock(EntitySearchService::class);
        $service->expects(self::once())->method('search')
            ->with('Wanderer', [EntityType::AuthorAlias, EntityType::Prod], 5)
            ->willReturn($found);

        $result = $this->read(new SearchAction($service), '{"phrase": "Wanderer", "types": ["authorAlias", "prod"], "limit": 5}');

        self::assertEquals(new FoundEntitiesDto($found), $result);
    }

    public function testNoTypesSearchesThemAllAndTheLimitIsKeptBetweenOneAndAHundred(): void
    {
        $service = $this->createMock(EntitySearchService::class);
        $service->expects(self::exactly(3))->method('search')
            ->with('Wanderer', [], self::callback(static fn(int $limit): bool => in_array($limit, [1, 20, 100], true)))
            ->willReturn([]);
        $action = new SearchAction($service);

        $this->read($action, '{"phrase": "Wanderer"}');
        $this->read($action, '{"phrase": "Wanderer", "limit": 0}');
        $this->read($action, '{"phrase": "Wanderer", "limit": 500}');
    }

    public function testATypeThatIsNotSearchedIsRefused(): void
    {
        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage('types');

        $this->read(new SearchAction($this->createStub(EntitySearchService::class)), '{"phrase": "Wanderer", "types": ["tune"]}');
    }

    private function read(SearchAction $action, string $json): object
    {
        return $action->read(Arguments::parse($json, $action->schema()));
    }
}
