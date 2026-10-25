<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Releases\Dbal\ReleaseSearchDbal;
use ZxArt\Releases\Dto\ReleaseSearchFilterDto;
use ZxArt\Releases\ReleaseTypes;

/**
 * Releases by their own fields, their files, their production, publishers and
 * credited authors, paged by title.
 */
class ReleaseSearchDbalTest extends TestCase
{
    private const int ELITE_TAPE = 1;
    private const int ELITE_DISK = 2;
    private const int EXOLON_CRACK = 3;
    private const int ELITE = 100;
    private const int EXOLON = 101;
    private const int AUTHOR = 10;
    private const int AUTHOR_ALIAS = 11;
    private const int OTHER_AUTHOR = 12;
    private const int GROUP = 20;
    private const int GROUP_ALIAS = 21;
    private const int ZX48 = 60;
    private const int PENTAGON = 61;

    private SQLiteConnection $db;
    private ReleaseSearchDbal $dbal;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $pdo = $this->db->getPdo();
        $pdo->exec('CREATE TABLE module_zxprod (id INTEGER)');
        $pdo->exec('CREATE TABLE module_zxrelease (id INTEGER, languageId INTEGER, title TEXT, fileName TEXT, year INTEGER, releaseType TEXT)');
        $pdo->exec('CREATE TABLE module_zxrelease_format (elementId INTEGER, value TEXT)');
        $pdo->exec('CREATE TABLE module_zxrelease_hw_required (elementId INTEGER, hardwareId INTEGER)');
        $pdo->exec('CREATE TABLE hardware (id INTEGER, code TEXT)');
        $pdo->exec('CREATE TABLE zxitem_language (elementId INTEGER, value TEXT)');
        $pdo->exec('CREATE TABLE files_registry (md5 TEXT, elementId INTEGER)');
        $pdo->exec('CREATE TABLE module_authoralias (id INTEGER, authorId INTEGER)');
        $pdo->exec('CREATE TABLE module_groupalias (id INTEGER, groupId INTEGER)');
        $pdo->exec('CREATE TABLE authorship (authorId INTEGER, elementId INTEGER, type TEXT, roles TEXT)');
        $pdo->exec('CREATE TABLE structure_links (parentStructureId INTEGER, childStructureId INTEGER, type TEXT)');
        $this->db->table('module_zxprod')->insert([['id' => self::ELITE], ['id' => self::EXOLON]]);
        // a row per language, as the CMS stores them
        $this->db->table('module_zxrelease')->insert([
            $this->release(self::ELITE_TAPE, 1, 'Elite', 'elite.tap', 1985, ReleaseTypes::original),
            $this->release(self::ELITE_TAPE, 2, 'Elite', 'elite.tap', 1985, ReleaseTypes::original),
            $this->release(self::ELITE_DISK, 1, 'Elite', 'elite_disk.trd', 1990, ReleaseTypes::rerelease),
            $this->release(self::EXOLON_CRACK, 1, 'Exolon', 'exolon.tzx', 0, ReleaseTypes::crack),
        ]);
        $this->db->table('module_zxrelease_format')->insert([
            ['elementId' => self::ELITE_TAPE, 'value' => 'tap'],
            ['elementId' => self::ELITE_DISK, 'value' => 'trd'],
            ['elementId' => self::EXOLON_CRACK, 'value' => 'tzx'],
        ]);
        $this->db->table('hardware')->insert([['id' => self::ZX48, 'code' => 'zx48'], ['id' => self::PENTAGON, 'code' => 'pentagon128']]);
        $this->db->table('module_zxrelease_hw_required')->insert([
            ['elementId' => self::ELITE_TAPE, 'hardwareId' => self::ZX48],
            ['elementId' => self::ELITE_DISK, 'hardwareId' => self::PENTAGON],
        ]);
        $this->db->table('zxitem_language')->insert([
            ['elementId' => self::ELITE_TAPE, 'value' => 'en'],
            ['elementId' => self::ELITE_DISK, 'value' => 'ru'],
            // a production's language is no release's
            ['elementId' => self::EXOLON, 'value' => 'ru'],
        ]);
        $this->db->table('module_authoralias')->insert(['id' => self::AUTHOR_ALIAS, 'authorId' => self::AUTHOR]);
        $this->db->table('module_groupalias')->insert(['id' => self::GROUP_ALIAS, 'groupId' => self::GROUP]);
        $this->link(self::ELITE, self::ELITE_TAPE, 'structure');
        $this->link(self::ELITE, self::ELITE_DISK, 'structure');
        $this->link(self::EXOLON, self::EXOLON_CRACK, 'structure');
        $this->dbal = new ReleaseSearchDbal($this->db);
    }

    public function testTheTitleFindsEachReleaseOnceOrderedByTitle(): void
    {
        self::assertSame([self::ELITE_TAPE, self::ELITE_DISK], $this->search(new ReleaseSearchFilterDto(title: ' eli ')));
        self::assertSame(2, $this->dbal->count(new ReleaseSearchFilterDto(title: 'eli'), null));
    }

    public function testTheFileNameMatchesAPartOfIt(): void
    {
        self::assertSame([self::ELITE_DISK], $this->search(new ReleaseSearchFilterDto(fileName: 'disk')));
    }

    public function testTheMd5FindsTheReleasesHoldingTheFile(): void
    {
        $this->db->table('files_registry')->insert([
            ['md5' => 'aa', 'elementId' => self::ELITE_TAPE],
            ['md5' => 'aa', 'elementId' => self::EXOLON_CRACK],
            ['md5' => 'bb', 'elementId' => self::ELITE_DISK],
        ]);

        self::assertSame([self::ELITE_TAPE, self::EXOLON_CRACK], $this->search(new ReleaseSearchFilterDto(md5: 'aa')));
    }

    public function testTheProductionsGivenOrFoundAnswerTheirReleases(): void
    {
        $foundExolon = $this->db->table('module_zxprod')->select('id')->where('id', '=', self::EXOLON);

        self::assertSame([self::ELITE_TAPE, self::ELITE_DISK], $this->search(new ReleaseSearchFilterDto(prodIds: [self::ELITE])));
        self::assertSame([self::EXOLON_CRACK], $this->search(new ReleaseSearchFilterDto(), $foundExolon));
    }

    public function testTheYearsLeaveOutTheReleasesOfAnUnknownYear(): void
    {
        self::assertSame([self::ELITE_DISK], $this->search(new ReleaseSearchFilterDto(yearFrom: 1986)));
        self::assertSame([self::ELITE_TAPE], $this->search(new ReleaseSearchFilterDto(yearTo: 1986)));
    }

    public function testTypesFormatsHardwareAndLanguagesHoldWhenAnyOfTheirValuesDoes(): void
    {
        self::assertSame(
            [self::ELITE_DISK, self::EXOLON_CRACK],
            $this->search(new ReleaseSearchFilterDto(releaseTypes: [ReleaseTypes::rerelease, ReleaseTypes::crack])),
        );
        self::assertSame([self::ELITE_TAPE, self::EXOLON_CRACK], $this->search(new ReleaseSearchFilterDto(formats: ['tap', 'tzx'])));
        self::assertSame([self::ELITE_DISK], $this->search(new ReleaseSearchFilterDto(hardware: ['pentagon128'])));
        self::assertSame([self::ELITE_DISK], $this->search(new ReleaseSearchFilterDto(languages: ['ru'])));
    }

    public function testAPublisherIsAnAuthorOrAGroupWithTheirAliases(): void
    {
        $this->link(self::GROUP_ALIAS, self::ELITE_TAPE, 'zxReleasePublishers');
        $this->link(self::AUTHOR_ALIAS, self::ELITE_DISK, 'zxReleasePublishers');
        $this->link(self::AUTHOR, self::EXOLON_CRACK, 'zxReleasePublishers');
        $this->link(self::GROUP, self::EXOLON_CRACK, 'zxProdPublishers');

        self::assertSame([self::ELITE_TAPE], $this->search(new ReleaseSearchFilterDto(publisherIds: [self::GROUP])));
        self::assertSame([self::ELITE_DISK, self::EXOLON_CRACK], $this->search(new ReleaseSearchFilterDto(publisherIds: [self::AUTHOR])));
    }

    public function testSeveralAuthorsFindWhatTheyMadeTogetherOrWithTheFlagWhatAnyOfThemMade(): void
    {
        $this->credit(self::AUTHOR, self::ELITE_TAPE, ['release']);
        $this->credit(self::OTHER_AUTHOR, self::ELITE_TAPE, ['release']);
        $this->credit(self::AUTHOR_ALIAS, self::ELITE_DISK, ['adaptation']);
        $this->credit(self::OTHER_AUTHOR, self::EXOLON_CRACK, ['adaptation'], 'prod');
        $authors = [self::AUTHOR, self::OTHER_AUTHOR];

        self::assertSame([self::ELITE_TAPE, self::ELITE_DISK], $this->search(new ReleaseSearchFilterDto(authorIds: [self::AUTHOR])));
        self::assertSame([self::ELITE_TAPE], $this->search(new ReleaseSearchFilterDto(authorIds: $authors)));
        self::assertSame([self::ELITE_TAPE, self::ELITE_DISK], $this->search(new ReleaseSearchFilterDto(authorIds: $authors, anyAuthor: true)));
        self::assertSame([self::ELITE_DISK], $this->search(new ReleaseSearchFilterDto(authorRoles: ['adaptation'])));
    }

    public function testThePageIsCutAfterTheOrder(): void
    {
        self::assertSame([self::ELITE_DISK], $this->dbal->findIds(new ReleaseSearchFilterDto(), null, 1, 1));
    }

    /**
     * @return int[]
     */
    private function search(ReleaseSearchFilterDto $filter, ?Builder $foundProdIds = null): array
    {
        return $this->dbal->findIds($filter, $foundProdIds, 0, 10);
    }

    /**
     * @return array<string, int|string>
     */
    private function release(int $id, int $languageId, string $title, string $fileName, int $year, ReleaseTypes $type): array
    {
        return ['id' => $id, 'languageId' => $languageId, 'title' => $title, 'fileName' => $fileName, 'year' => $year, 'releaseType' => $type->value];
    }

    private function link(int $parentId, int $childId, string $type): void
    {
        $this->db->table('structure_links')->insert(['parentStructureId' => $parentId, 'childStructureId' => $childId, 'type' => $type]);
    }

    /**
     * @param string[] $roles
     */
    private function credit(int $authorId, int $elementId, array $roles, string $type = 'release'): void
    {
        $this->db->table('authorship')->insert([
            'authorId' => $authorId,
            'elementId' => $elementId,
            'type' => $type,
            'roles' => json_encode($roles, JSON_THROW_ON_ERROR),
        ]);
    }
}
