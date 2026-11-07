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
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\UseCases\CreateReleaseCommand;
use ZxArt\Releases\UseCases\CreateReleaseHandler;
use ZxArt\Search\Dto\PhraseSearchHitDto;
use ZxArt\Search\Dto\PhraseSearchQueryDto;
use ZxArt\Search\Dto\PhraseSearchResultDto;
use ZxArt\Search\Services\AliasHandling;
use ZxArt\Search\Services\PhraseSearchEngine;
use ZxArt\Search\Services\PhraseSearchMode;
use ZxArt\Search\Services\PhraseSearchType;
use ZxArt\Tests\Integration\Entities\EntityWriteTestCase;

/**
 * The one phrase search the public searches, the pickers and the assistant
 * run, with each setting changing what it answers.
 */
#[RunTestsInSeparateProcesses]
final class PhraseSearchEngineTest extends EntityWriteTestCase
{
    private const int COUNTRY_POLAND = 47450;
    private const int CITY_WARSAW = 47452;
    private const int CRACKTRO_CATEGORY = 92171;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive'], ['zxRelease', 'publicReceive']]);
    }

    public function testMergedAliasesComeUnderTheirOwnersType(): void
    {
        [$authorId, $aliasId] = $this->authorWithAlias();

        $result = $this->find(new PhraseSearchQueryDto('Goldsearch', [PhraseSearchType::Author], 10, PhraseSearchMode::SinglePage, aliases: AliasHandling::MergeIntoOwner));

        self::assertSame([['author', $authorId], ['author', $aliasId]], $this->hits($result));
        self::assertSame(2, $result->total);
    }

    public function testSeparateAliasesAreATypeOfTheirOwn(): void
    {
        [$authorId, $aliasId] = $this->authorWithAlias();

        $authors = $this->find(new PhraseSearchQueryDto('Goldsearch', [PhraseSearchType::Author], 10, PhraseSearchMode::SinglePage));
        $aliases = $this->find(new PhraseSearchQueryDto('Goldsearch', [PhraseSearchType::AuthorAlias], 10, PhraseSearchMode::SinglePage));

        self::assertSame([['author', $authorId]], $this->hits($authors));
        self::assertSame([['authorAlias', $aliasId]], $this->hits($aliases));
    }

    public function testClosestFirstOrdersAcrossTheTypes(): void
    {
        $containingId = $this->prod('The Goldsearch Demo');
        $startingId = $this->prod('Goldsearch Megademo');
        $exactId = $this->author('Goldsearch');

        $byType = $this->find(new PhraseSearchQueryDto('goldsearch', [PhraseSearchType::ZxProd, PhraseSearchType::Author], 10, PhraseSearchMode::SinglePage));
        $closestFirst = $this->find(new PhraseSearchQueryDto('goldsearch', [PhraseSearchType::ZxProd, PhraseSearchType::Author], 10, PhraseSearchMode::SinglePage, closestFirst: true));

        self::assertSame([['zxProd', $startingId], ['zxProd', $containingId], ['author', $exactId]], $this->hits($byType));
        self::assertSame([['author', $exactId], ['zxProd', $startingId], ['zxProd', $containingId]], $this->hits($closestFirst));
    }

    public function testAPageIsCutFromTheAlphabeticalList(): void
    {
        $this->prod('Goldsearch Gamma');
        $betaId = $this->prod('Goldsearch Beta');
        $this->prod('Goldsearch Alpha');

        $result = $this->find(new PhraseSearchQueryDto('Goldsearch', [PhraseSearchType::ZxProd], 1, PhraseSearchMode::Paged, offset: 1));

        self::assertSame([['zxProd', $betaId]], $this->hits($result));
        self::assertSame(3, $result->total);
    }

    public function testAnEmptyPhraseFindsNothing(): void
    {
        self::assertEquals(
            new PhraseSearchResultDto(0, []),
            $this->find(new PhraseSearchQueryDto('  ', [PhraseSearchType::ZxProd], 10, PhraseSearchMode::SinglePage)),
        );
    }

    public function testAPhraseOfPunctuationAloneFindsNothing(): void
    {
        $this->prod('Goldsearch - Demo');

        self::assertEquals(
            new PhraseSearchResultDto(0, []),
            $this->find(new PhraseSearchQueryDto(' - !', [PhraseSearchType::ZxProd], 10, PhraseSearchMode::SinglePage)),
        );
    }

    public function testSpacesAndPunctuationInTheTitleOrThePhraseDoNotMatter(): void
    {
        $joinedId = $this->prod('Goldsearchstar');
        $hyphenatedId = $this->prod('Gold-Search Star 2');

        $quick = $this->find(new PhraseSearchQueryDto('gold search-star', [PhraseSearchType::ZxProd], 10, PhraseSearchMode::SinglePage));
        $paged = $this->find(new PhraseSearchQueryDto('gold search-star', [PhraseSearchType::ZxProd], 10, PhraseSearchMode::Paged, contentMatching: true));

        // the single page puts the equal key first, the paged list is alphabetical
        self::assertSame([['zxProd', $joinedId], ['zxProd', $hyphenatedId]], $this->hits($quick));
        self::assertSame([['zxProd', $hyphenatedId], ['zxProd', $joinedId]], $this->hits($paged));
    }

    public function testLettersBeyondLatinAndCyrillicAreMatched(): void
    {
        $prodId = $this->prod('Złoty Goldsearch');

        $result = $this->find(new PhraseSearchQueryDto('złoty-goldsearch', [PhraseSearchType::ZxProd], 10, PhraseSearchMode::SinglePage));

        self::assertSame([['zxProd', $prodId]], $this->hits($result));
    }

    public function testATitleStoredHtmlEncodedIsMatchedByWhatItReads(): void
    {
        $prodId = $this->prod('Goldsearch & "Friends"');

        $result = $this->find(new PhraseSearchQueryDto('goldsearch friends', [PhraseSearchType::ZxProd], 10, PhraseSearchMode::SinglePage));

        self::assertSame([['zxProd', $prodId]], $this->hits($result));
    }

    public function testTheClosestKeyComesFirstOnASinglePage(): void
    {
        $containingId = $this->prod('A Goldsearch Star');
        $startingId = $this->prod('Goldsearchstar 2');
        $exactId = $this->prod('Goldsearch-Star');

        $result = $this->find(new PhraseSearchQueryDto('goldsearch star', [PhraseSearchType::ZxProd], 10, PhraseSearchMode::SinglePage));

        self::assertSame([['zxProd', $exactId], ['zxProd', $startingId], ['zxProd', $containingId]], $this->hits($result));
    }

    public function testASinglePageSharesTheLimitBetweenTheTypes(): void
    {
        $authorIds = [$this->author('Qzxquota A'), $this->author('Qzxquota B'), $this->author('Qzxquota C')];
        $prodIds = [$this->prod('Qzxquota A'), $this->prod('Qzxquota B'), $this->prod('Qzxquota C')];

        $result = $this->find(new PhraseSearchQueryDto('qzxquota', [PhraseSearchType::Author, PhraseSearchType::ZxProd], 4, PhraseSearchMode::SinglePage));

        self::assertSame(
            [['author', $authorIds[0]], ['author', $authorIds[1]], ['zxProd', $prodIds[0]], ['zxProd', $prodIds[1]]],
            $this->hits($result),
        );
        self::assertSame(6, $result->total);
    }

    public function testAPageGoesOnFromTheLastResultsOfOneTypeToTheFirstOfTheNext(): void
    {
        $this->author('Qzxwindow A');
        $lastAuthorId = $this->author('Qzxwindow B');
        $firstProdId = $this->prod('Qzxwindow A');
        $this->prod('Qzxwindow B');

        $result = $this->find(new PhraseSearchQueryDto('qzxwindow', [PhraseSearchType::Author, PhraseSearchType::ZxProd], 2, PhraseSearchMode::Paged, offset: 1));

        self::assertSame([['author', $lastAuthorId], ['zxProd', $firstProdId]], $this->hits($result));
        self::assertSame(4, $result->total);
    }

    public function testAnEntityStoredInARowPerLanguageIsFoundOnce(): void
    {
        $authorId = $this->author('Qzxonce Writer');

        $result = $this->find(new PhraseSearchQueryDto('qzxonce', [PhraseSearchType::Author], 10, PhraseSearchMode::Paged));

        self::assertSame([['author', $authorId]], $this->hits($result));
        self::assertSame(1, $result->total);
    }

    public function testACracktroIsLeftOut(): void
    {
        $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(
            new ProdFieldsDto(title: 'Qzxcrack Intro', categoryIds: [self::CRACKTRO_CATEGORY]),
        ));

        self::assertSame([], $this->find(new PhraseSearchQueryDto('qzxcrack', [PhraseSearchType::ZxProd], 10, PhraseSearchMode::SinglePage))->hits);
    }

    public function testReleasesCountriesAndCitiesAreSearchedByTheirTitles(): void
    {
        $prodId = $this->prod('Qzxplace Demo');
        $releaseId = $this->container->get(CreateReleaseHandler::class)->handle(new CreateReleaseCommand(
            new ReleaseFieldsDto(title: 'Qzxplace Tape', prodId: $prodId),
        ))->id;

        $releases = $this->find(new PhraseSearchQueryDto('qzx-place', [PhraseSearchType::ZxRelease], 10, PhraseSearchMode::SinglePage));
        $countries = $this->find(new PhraseSearchQueryDto('pol-and', [PhraseSearchType::Country], 10, PhraseSearchMode::SinglePage));
        $cities = $this->find(new PhraseSearchQueryDto('war saw', [PhraseSearchType::City], 10, PhraseSearchMode::SinglePage));

        self::assertSame([['zxRelease', $releaseId]], $this->hits($releases));
        self::assertContains(['country', self::COUNTRY_POLAND], $this->hits($countries));
        self::assertContains(['city', self::CITY_WARSAW], $this->hits($cities));
    }

    /**
     * @return array{int, int}
     */
    private function authorWithAlias(): array
    {
        $authorId = $this->author('Goldsearch Writer');
        $aliasId = $this->container->get(CreateAuthorAliasHandler::class)->handle(new CreateAuthorAliasCommand(
            new AuthorAliasFieldsDto(title: 'Goldsearch Wanderer', authorId: $authorId),
        ))->id;

        return [$authorId, $aliasId];
    }

    private function prod(string $title): int
    {
        return $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: $title)))->id;
    }

    private function author(string $title): int
    {
        return $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: $title)))->id;
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
        return array_map(static fn(PhraseSearchHitDto $hit): array => [$hit->type->value, $hit->element->getId()], $result->hits);
    }
}
