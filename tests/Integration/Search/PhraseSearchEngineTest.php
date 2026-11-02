<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Search;

require_once __DIR__ . '/../Entities/EntityWriteTestCase.php';

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
use ZxArt\Search\Dto\PhraseSearchHitDto;
use ZxArt\Search\Dto\PhraseSearchQueryDto;
use ZxArt\Search\Dto\PhraseSearchResultDto;
use ZxArt\Search\Services\AliasHandling;
use ZxArt\Search\Services\PhraseSearchEngine;
use ZxArt\Search\Services\PhraseSearchMode;
use ZxArt\Tests\Integration\Entities\EntityWriteTestCase;

/**
 * The one phrase search the public searches and the assistant run, with each
 * setting changing what it answers.
 */
#[RunTestsInSeparateProcesses]
final class PhraseSearchEngineTest extends EntityWriteTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive']]);
    }

    public function testMergedAliasesComeUnderTheirOwnersType(): void
    {
        [$authorId, $aliasId] = $this->authorWithAlias();

        $result = $this->find(new PhraseSearchQueryDto('Goldsearch', ['author'], 10, PhraseSearchMode::SinglePage, aliases: AliasHandling::MergeIntoOwner));

        self::assertSame([['author', $authorId], ['author', $aliasId]], $this->hits($result));
        self::assertSame(2, $result->total);
    }

    public function testSeparateAliasesAreATypeOfTheirOwn(): void
    {
        [$authorId, $aliasId] = $this->authorWithAlias();

        $authors = $this->find(new PhraseSearchQueryDto('Goldsearch', ['author'], 10, PhraseSearchMode::SinglePage));
        $aliases = $this->find(new PhraseSearchQueryDto('Goldsearch', ['authorAlias'], 10, PhraseSearchMode::SinglePage));

        self::assertSame([['author', $authorId]], $this->hits($authors));
        self::assertSame([['authorAlias', $aliasId]], $this->hits($aliases));
    }

    public function testClosestFirstOrdersAcrossTheTypes(): void
    {
        $containingId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'The Goldsearch Demo')))->id;
        $startingId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldsearch Megademo')))->id;
        $exactId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldsearch')))->id;

        $byType = $this->find(new PhraseSearchQueryDto('goldsearch', ['zxProd', 'author'], 10, PhraseSearchMode::SinglePage));
        $closestFirst = $this->find(new PhraseSearchQueryDto('goldsearch', ['zxProd', 'author'], 10, PhraseSearchMode::SinglePage, closestFirst: true));

        self::assertSame([['zxProd', $startingId], ['zxProd', $containingId], ['author', $exactId]], $this->hits($byType));
        self::assertSame([['author', $exactId], ['zxProd', $startingId], ['zxProd', $containingId]], $this->hits($closestFirst));
    }

    public function testAPageIsCutFromTheAlphabeticalList(): void
    {
        $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldsearch Gamma')));
        $betaId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldsearch Beta')))->id;
        $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldsearch Alpha')));

        $result = $this->find(new PhraseSearchQueryDto('Goldsearch', ['zxProd'], 1, PhraseSearchMode::Paged, offset: 1));

        self::assertSame([['zxProd', $betaId]], $this->hits($result));
        self::assertSame(3, $result->total);
    }

    public function testAnEmptyPhraseFindsNothing(): void
    {
        self::assertEquals(
            new PhraseSearchResultDto(0, []),
            $this->find(new PhraseSearchQueryDto('  ', ['zxProd'], 10, PhraseSearchMode::SinglePage)),
        );
    }

    /**
     * @return array{int, int}
     */
    private function authorWithAlias(): array
    {
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldsearch Writer')))->id;
        $aliasId = $this->container->get(CreateAuthorAliasHandler::class)->handle(new CreateAuthorAliasCommand(
            new AuthorAliasFieldsDto(title: 'Goldsearch Wanderer', authorId: $authorId),
        ))->id;

        return [$authorId, $aliasId];
    }

    private function find(PhraseSearchQueryDto $query): PhraseSearchResultDto
    {
        return $this->container->get(PhraseSearchEngine::class)->find($query);
    }

    /**
     * @return list<array{string, int}>
     */
    private function hits(PhraseSearchResultDto $result): array
    {
        return array_map(static fn(PhraseSearchHitDto $hit): array => [$hit->type, $hit->element->getId()], $result->hits);
    }
}
