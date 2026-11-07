<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Search;

require_once __DIR__ . '/../Entities/EntityWriteTestCase.php';

use Illuminate\Database\Connection;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use QueryFiltersManager;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\AuthorSearchFilterDto;
use ZxArt\Authors\Services\AuthorSearchService;
use ZxArt\Authors\UseCases\CreateAuthorAliasCommand;
use ZxArt\Authors\UseCases\CreateAuthorAliasHandler;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Geo\Dbal\GeoDbal;
use ZxArt\Groups\Dbal\GroupsDbal;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Dto\GroupSearchFilterDto;
use ZxArt\Groups\Services\GroupSearchService;
use ZxArt\Groups\UseCases\CreateGroupAliasCommand;
use ZxArt\Groups\UseCases\CreateGroupAliasHandler;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\MusicSearch\Dbal\MusicSearchDbal;
use ZxArt\MusicSearch\Dto\MusicSearchQuery;
use ZxArt\MusicSearch\MusicSearchSort;
use ZxArt\PictureSearch\Dbal\PictureSearchDbal;
use ZxArt\PictureSearch\Dto\PictureSearchQuery;
use ZxArt\PictureSearch\PictureSearchOrder;
use ZxArt\PictureSearch\PictureSearchResultsType;
use ZxArt\PictureSearch\PictureSearchSort;
use ZxArt\Prods\Dbal\ProdsDbal;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\Services\ProdSearchService;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Dto\ReleaseSearchFilterDto;
use ZxArt\Releases\Services\ReleaseSearchService;
use ZxArt\Releases\UseCases\CreateReleaseCommand;
use ZxArt\Releases\UseCases\CreateReleaseHandler;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\SortDirection;
use ZxArt\Tests\Integration\Entities\EntityWriteTestCase;

/**
 * Every search that narrows by a title compares it the same way: spaces,
 * punctuation and case do not matter, letters of any script do. Run on what
 * the test wrote; the database holds nothing of the `qzx` phrases.
 */
#[RunTestsInSeparateProcesses]
final class TitleMatchingSearchesTest extends EntityWriteTestCase
{
    private const int PICTURE = 990_000_201;
    private const int TUNE = 990_000_202;
    private const int PARTY = 990_000_203;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [
            ['zxProd', 'publicReceive'],
            ['zxRelease', 'publicReceive'],
            ['group', 'publicReceive'],
            ['author', 'publicReceive'],
        ]);
    }

    public function testAProductionIsFoundByItsTitleOrItsAlternativeTitle(): void
    {
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(
            new ProdFieldsDto(title: 'Qzxrebelstar', altTitle: 'Звёздные qzx-войны'),
        ))->id;

        foreach (['qzx-rebel star', 'qzxвойны', 'ЗВЁЗДНЫЕ QZX'] as $phrase) {
            $page = $this->container->get(ProdSearchService::class)->search(new ProdSearchFilterDto(title: $phrase), 0, 10);
            self::assertSame([$prodId], $page->ids, $phrase);
            self::assertSame(1, $page->total, $phrase);
        }
    }

    public function testAnAuthorIsFoundByTheNicknameTheRealNameOrAnAlias(): void
    {
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(
            new AuthorFieldsDto(title: 'Qzxalex', realName: 'Qzx Иванов'),
        ))->id;
        $this->container->get(CreateAuthorAliasHandler::class)->handle(new CreateAuthorAliasCommand(
            new AuthorAliasFieldsDto(title: 'Qzxorr-alias', authorId: $authorId),
        ));

        foreach (['qzx-alex', 'qzxиванов', 'qzxorr alias'] as $phrase) {
            self::assertSame([$authorId], $this->container->get(AuthorSearchService::class)->search(new AuthorSearchFilterDto(name: $phrase), 0, 10)->ids, $phrase);
        }
    }

    public function testAGroupIsFoundByTheTitleTheAbbreviationOrAnAlias(): void
    {
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(
            new GroupFieldsDto(title: 'Qzx Triumph Brothers', abbreviation: 'QZX-TB'),
        ))->id;
        $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(
            new GroupAliasFieldsDto(title: 'Qzx Crew', groupId: $groupId),
        ));

        foreach (['qzxtriumph', 'qzx tb', 'qzx-crew'] as $phrase) {
            self::assertSame([$groupId], $this->container->get(GroupSearchService::class)->search(new GroupSearchFilterDto(name: $phrase), 0, 10)->ids, $phrase);
        }
    }

    public function testAReleaseIsFoundByItsTitleOrItsFileName(): void
    {
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Qzxelite')))->id;
        $releaseId = $this->container->get(CreateReleaseHandler::class)->handle(new CreateReleaseCommand(
            new ReleaseFieldsDto(title: 'Qzx Elite Tape', prodId: $prodId),
        ))->id;
        $this->db()->table(DatabaseTable::ZxRelease->value)->where('id', '=', $releaseId)->update(['fileName' => 'qzx_elite_disk.trd']);

        $byTitle = $this->container->get(ReleaseSearchService::class)->search(new ReleaseSearchFilterDto(title: 'qzx-elite tape'), 0, 10);
        $byFileName = $this->container->get(ReleaseSearchService::class)->search(new ReleaseSearchFilterDto(fileName: 'qzx elite disk'), 0, 10);

        self::assertSame([$releaseId], $byTitle->ids);
        self::assertSame([$releaseId], $byFileName->ids);
    }

    public function testThePictureSearchAndItsExportFilterMatchTheTitle(): void
    {
        $this->insertRelaxed(DatabaseTable::ZxPicture, ['id' => self::PICTURE, 'title' => 'Qzx Rebel-Star Screen', 'languageId' => 0]);

        $ids = $this->container->get(PictureSearchDbal::class)->buildPicturesQuery($this->pictureQuery('qzx rebelstar'))->pluck('id');
        $exported = $this->exportedIds('zxPictureTitleSearch', 'qzx rebelstar', 'zxPicture');

        self::assertSame([self::PICTURE], $ids);
        self::assertSame([self::PICTURE], $exported);
    }

    public function testTheMusicSearchAndItsExportFilterMatchTheTitleOrTheTrackTitle(): void
    {
        $this->insertRelaxed(DatabaseTable::ZxMusic, ['id' => self::TUNE, 'title' => 'Qzx Rebel-Star Theme', 'internalTitle' => 'qzx_main_loop', 'languageId' => 0]);

        foreach (['qzx rebelstar', 'QZX-MAIN loop'] as $phrase) {
            self::assertSame([self::TUNE], $this->container->get(MusicSearchDbal::class)->buildTunesQuery($this->musicQuery($phrase))->pluck('id'), $phrase);
            self::assertSame([self::TUNE], $this->exportedIds('zxMusicTitleSearch', $phrase, 'zxMusic'), $phrase);
        }
    }

    public function testTheMapsPartySearchMatchesTheTitle(): void
    {
        $this->insertRelaxed(DatabaseTable::Party, ['id' => self::PARTY, 'title' => 'Qzx Rebel-Party 2093', 'languageId' => 0]);

        $ids = $this->container->get(GeoDbal::class)->findPartyIds(0, 10, 'title', SortDirection::ASC, null, null, null, null, null, null, 'qzx rebelparty');

        self::assertSame([self::PARTY], $ids);
    }

    public function testTheImportFindsAProductionOrAGroupByTheTitleWithLettersBeyondLatin(): void
    {
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Qzx-Złoty-Star')))->id;
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'QZX Gęś Crew')))->id;

        self::assertContains($prodId, $this->container->get(ProdsDbal::class)->findProdsByTitles('qzx złoty star'));
        self::assertContains($groupId, $this->container->get(GroupsDbal::class)->findGroupIdsByName('qzx-gęś-crew') ?? []);
    }

    private function db(): Connection
    {
        return $this->container->get(Connection::class);
    }

    /**
     * A picture, tune or party row with only the columns the test needs; the
     * relaxed mode lets the others take their implicit defaults.
     *
     * @param array<string, int|string> $row
     */
    private function insertRelaxed(DatabaseTable $table, array $row): void
    {
        $this->db()->statement("SET SESSION sql_mode = ''");
        try {
            $this->db()->table($table->value)->insert($row);
        } finally {
            $this->db()->statement("SET SESSION sql_mode = DEFAULT");
        }
    }

    /**
     * @return int[]
     */
    private function exportedIds(string $filter, string $phrase, string $type): array
    {
        $queryFiltersManager = $this->container->get(QueryFiltersManager::class);
        $queryFiltersManager->setContainer($this->container);
        $queries = $queryFiltersManager->getFilterQueries([$filter => $phrase], [$type], true, false);

        return $queries[$type]->pluck('id');
    }

    private function pictureQuery(string $titleWord): PictureSearchQuery
    {
        return new PictureSearchQuery(
            $titleWord, null, null, null, null, null, false, false, false, false, [], [], [], [],
            PictureSearchResultsType::Items, PictureSearchSort::Title, PictureSearchOrder::Asc, 0, 10,
        );
    }

    private function musicQuery(string $titleWord): MusicSearchQuery
    {
        return new MusicSearchQuery(
            $titleWord, null, null, null, null, null, null, false, [], [], [], [],
            PictureSearchResultsType::Items, MusicSearchSort::Title, PictureSearchOrder::Asc, 0, 10,
        );
    }
}
