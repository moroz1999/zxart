<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/../Entities/EntityWriteTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Tools\Search\FoundEntitiesDto;
use ZxArt\Assistant\Tools\ToolRegistry;
use ZxArt\Assistant\Tools\ToolRequestBuilder;
use ZxArt\Prods\Dto\ProdFieldsDto;
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
}
