<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Search;

require_once __DIR__ . '/../Entities/EntityWriteTestCase.php';

use LanguagesManager;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\UseCases\CreateAuthorAliasCommand;
use ZxArt\Authors\UseCases\CreateAuthorAliasHandler;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Search\Dto\SearchResultPreviewDto;
use ZxArt\Search\Dto\SearchResultsDto;
use ZxArt\Search\Dto\SearchResultSetDto;
use ZxArt\Search\Services\SearchService;
use ZxArt\Tests\Integration\Entities\EntityWriteTestCase;

/**
 * What the public searches answer for entities the test wrote: aliases
 * merged into their author, sets in the configured order of the types.
 */
#[RunTestsInSeparateProcesses]
final class PublicSearchTest extends EntityWriteTestCase
{
    private int $authorId;
    private int $aliasId;
    private int $exactProdId;
    private int $laterProdId;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive']]);
        $this->laterProdId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldsearch Megademo')))->id;
        $this->exactProdId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldsearch')))->id;
        $this->authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldsearch Writer')))->id;
        $this->aliasId = $this->container->get(CreateAuthorAliasHandler::class)->handle(new CreateAuthorAliasCommand(
            new AuthorAliasFieldsDto(title: 'Goldsearch Wanderer', authorId: $this->authorId),
        ))->id;
    }

    public function testQuickSearchMergesAliasesAndPutsTheClosestTitlesFirst(): void
    {
        $result = $this->container->get(SearchService::class)->quickSearch('Goldsearch', ['author', 'zxProd'], 10, $this->languageId());

        self::assertSame(['author' => [$this->authorId, $this->aliasId], 'zxProd' => [$this->exactProdId, $this->laterProdId]], $this->idsBySet($result));
        self::assertSame(4, $result->total);
        $prodSet = $result->sets[1];
        self::assertEquals(
            [
                new SearchResultPreviewDto($this->exactProdId, 'Goldsearch', "/prod/{$this->exactProdId}", 'zxProd'),
                new SearchResultPreviewDto($this->laterProdId, 'Goldsearch Megademo', "/prod/{$this->laterProdId}", 'zxProd'),
            ],
            $prodSet->items,
        );
    }

    public function testQuickSearchOfOnlyAuthorsLeavesTheProdsOut(): void
    {
        $result = $this->container->get(SearchService::class)->quickSearch('Goldsearch', ['author'], 10, $this->languageId());

        self::assertSame(['author' => [$this->authorId, $this->aliasId]], $this->idsBySet($result));
    }

    public function testPagedSearchListsEachTypeAlphabetically(): void
    {
        $result = $this->container->get(SearchService::class)->search('Goldsearch', 1, ['author', 'zxProd']);

        self::assertSame(['author' => [$this->authorId, $this->aliasId], 'zxProd' => [$this->exactProdId, $this->laterProdId]], $this->idsBySet($result));
        self::assertSame(4, $result->total);
    }

    public function testBothSearchesFindATitleWithAnAmpersand(): void
    {
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Rock & Roll Goldsearch')))->id;

        $quick = $this->container->get(SearchService::class)->quickSearch('Rock & Roll', ['zxProd'], 10, $this->languageId());
        $paged = $this->container->get(SearchService::class)->search('Rock & Roll', 1, ['zxProd']);

        self::assertContains($prodId, $this->setIds($quick, 'zxProd'));
        self::assertContains($prodId, $this->setIds($paged, 'zxProd'));
    }

    public function testAnEmptyPhraseFindsNothing(): void
    {
        self::assertSame([], $this->container->get(SearchService::class)->quickSearch(' ', [], 10, $this->languageId())->sets);
        self::assertSame([], $this->container->get(SearchService::class)->search(' ', 1, [])->sets);
    }

    /**
     * Only the entities this test wrote: the database holds others of the phrase.
     *
     * @return array<string, int[]>
     */
    private function idsBySet(SearchResultsDto $result): array
    {
        $written = [$this->authorId, $this->aliasId, $this->exactProdId, $this->laterProdId];
        $idsBySet = [];
        foreach ($result->sets as $set) {
            self::assertInstanceOf(SearchResultSetDto::class, $set);
            $ids = array_values(array_filter(
                array_map(static fn(object $item): int => (int)($item->id ?? 0), $set->items),
                static fn(int $id): bool => in_array($id, $written, true),
            ));
            if ($ids !== []) {
                $idsBySet[$set->type] = $ids;
            }
        }

        return $idsBySet;
    }

    /**
     * @return int[]
     */
    private function setIds(SearchResultsDto $result, string $type): array
    {
        foreach ($result->sets as $set) {
            if ($set->type === $type) {
                return array_map(static fn(object $item): int => (int)($item->id ?? 0), $set->items);
            }
        }

        return [];
    }

    private function languageId(): int
    {
        return (int)$this->container->get(LanguagesManager::class)->getCurrentLanguageId();
    }
}
