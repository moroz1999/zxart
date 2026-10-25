<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/../Entities/EntityWriteTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Tools\Search\FoundAuthorsDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\FoundAuthorDto;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Assistant\Tools\Search\FoundEntitiesDto;
use ZxArt\Assistant\Tools\ToolRegistry;
use ZxArt\Assistant\Tools\ToolRequestBuilder;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Search\Dto\FoundEntityDto;
use ZxArt\Shared\EntityType;
use ZxArt\Tests\Integration\Entities\EntityWriteTestCase;

/**
 * A tool call as the assistant makes it: the tool resolved by name from the
 * container's registry, its arguments built from JSON.
 */
#[RunTestsInSeparateProcesses]
final class ToolCallTest extends EntityWriteTestCase
{
    public function testASearchCallFindsWhatTheTestWrote(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive']]);
        $prodId = $this->container->get(ProdDataService::class)->create(new ProdFieldsDto(title: 'Goldsearch Demo', year: 1999))->id;

        $tool = $this->container->get(ToolRegistry::class)->get('search');
        $request = $this->container->get(ToolRequestBuilder::class)->build(
            $tool->requestClass(),
            '{"phrase": "Goldsearch", "types": ["prod"]}',
        );

        self::assertEquals(
            new FoundEntitiesDto([new FoundEntityDto(EntityType::Prod, $prodId, 'Goldsearch Demo', 1999, '')]),
            $tool->execute($request),
        );
    }

    public function testASearchAuthorsCallTakesANestedSoftwareFilterFromJson(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive']]);
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldsearch Coder'))->id;
        $prods = $this->container->get(ProdDataService::class);
        $prodId = $prods->create(new ProdFieldsDto(title: 'Goldsearch Demo', year: 1999, legalStatus: LegalStatus::allowed))->id;
        $prods->setMember($prodId, $authorId, ['code']);

        $tool = $this->container->get(ToolRegistry::class)->get('search_authors');
        $request = $this->container->get(ToolRequestBuilder::class)->build(
            $tool->requestClass(),
            '{"filter": {"name": "Goldsearch", "prodLink": "made", "prods": {"title": "goldsearch demo", "legalStatuses": ["allowed"]}}, "limit": 5}',
        );
        $result = $tool->execute($request);

        self::assertInstanceOf(FoundAuthorsDto::class, $result);
        self::assertSame(1, $result->total);
        self::assertSame([$authorId], array_map(static fn(FoundAuthorDto $author): int => $author->id, $result->results));
    }
}
