<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Search;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\JsonSchemaGenerator;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Assistant\Tools\Search\FoundEntitiesDto;
use ZxArt\Assistant\Tools\Search\SearchableType;
use ZxArt\Assistant\Tools\Search\SearchRequestDto;
use ZxArt\Assistant\Tools\Search\SearchTool;
use ZxArt\Search\Dto\FoundEntityDto;
use ZxArt\Search\Services\EntitySearchService;
use ZxArt\Shared\EntityType;

final class SearchToolTest extends TestCase
{
    public function testItIsTheReadToolNamedSearch(): void
    {
        $tool = new SearchTool($this->createStub(EntitySearchService::class));

        self::assertInstanceOf(ReadTool::class, $tool);
        self::assertSame('search', $tool->name());
        self::assertSame(SearchRequestDto::class, $tool->requestClass());
    }

    public function testTheModelCanAskForExactlyTheSixSearchedTypes(): void
    {
        $schema = (new JsonSchemaGenerator())->forClass(SearchRequestDto::class);

        self::assertSame(
            ['prod', 'release', 'author', 'authorAlias', 'group', 'groupAlias'],
            $schema['properties']['types']['items']['enum'] ?? null,
        );
        self::assertSame(['phrase'], $schema['required']);
    }

    public function testTheSearchRunsOverTheRequestedTypes(): void
    {
        $found = [new FoundEntityDto(EntityType::AuthorAlias, 7, 'Wanderer', null, 'Writer')];
        $service = $this->createMock(EntitySearchService::class);
        $service->expects(self::once())->method('search')
            ->with('Wanderer', [EntityType::AuthorAlias, EntityType::Prod], 5)
            ->willReturn($found);

        $result = (new SearchTool($service))->execute(
            new SearchRequestDto(phrase: 'Wanderer', types: [SearchableType::AuthorAlias, SearchableType::Prod], limit: 5),
        );

        self::assertEquals(new FoundEntitiesDto($found), $result);
    }

    public function testNoTypesSearchesThemAll(): void
    {
        $service = $this->createMock(EntitySearchService::class);
        $service->expects(self::once())->method('search')->with('Wanderer', [], 20)->willReturn([]);

        (new SearchTool($service))->execute(new SearchRequestDto(phrase: 'Wanderer'));
    }
}
