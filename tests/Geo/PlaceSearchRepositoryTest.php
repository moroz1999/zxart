<?php

declare(strict_types=1);

namespace ZxArt\Tests\Geo;

use Illuminate\Database\SQLiteConnection;
use LanguagesManager;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Geo\Dbal\PlaceSearchDbal;

/**
 * A place is found by its name in any language and answered in the current one.
 */
class PlaceSearchRepositoryTest extends TestCase
{
    private const int CURRENT_LANGUAGE = 1;
    private const int OTHER_LANGUAGE = 2;
    private const int ESTONIA = 10;
    private const int FINLAND = 11;
    private const int TALLINN = 20;
    private const int TARTU = 21;

    private SQLiteConnection $db;
    private PlaceSearchDbal $repository;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $pdo = $this->db->getPdo();
        $pdo->exec('CREATE TABLE module_country (id INTEGER, title TEXT, languageId INTEGER)');
        $pdo->exec('CREATE TABLE module_city (id INTEGER, title TEXT, languageId INTEGER)');
        $pdo->exec('CREATE TABLE structure_links (parentStructureId INTEGER, childStructureId INTEGER, type TEXT)');
        $this->addPlace('module_country', self::ESTONIA, 'Estonia', 'Эстония');
        $this->addPlace('module_country', self::FINLAND, 'Finland', 'Финляндия');
        $this->addPlace('module_city', self::TALLINN, 'Tallinn', 'Таллин');
        $this->addPlace('module_city', self::TARTU, 'Tartu', 'Тарту');
        $this->db->table('structure_links')->insert([
            ['parentStructureId' => self::ESTONIA, 'childStructureId' => self::TALLINN, 'type' => 'structure'],
            ['parentStructureId' => self::ESTONIA, 'childStructureId' => self::TARTU, 'type' => 'structure'],
        ]);
        $languagesManager = $this->createStub(LanguagesManager::class);
        $languagesManager->method('getCurrentLanguageId')->willReturn(self::CURRENT_LANGUAGE);
        $this->repository = new PlaceSearchDbal($this->db, $languagesManager);
    }

    public function testACountryIsFoundByItsNameInAnotherLanguage(): void
    {
        self::assertSame([['id' => self::ESTONIA, 'title' => 'Estonia']], $this->repository->findCountries('Эстон', 10));
    }

    public function testACityComesWithItsCountry(): void
    {
        self::assertSame(
            [['id' => self::TALLINN, 'title' => 'Tallinn', 'countryId' => self::ESTONIA, 'countryTitle' => 'Estonia']],
            $this->repository->findCities('tallin', 10),
        );
    }

    public function testTheLimitHolds(): void
    {
        self::assertCount(1, $this->repository->findCities('T', 1));
    }

    private function addPlace(string $table, int $id, string $title, string $otherTitle): void
    {
        $this->db->table($table)->insert([
            ['id' => $id, 'title' => $title, 'languageId' => self::CURRENT_LANGUAGE],
            ['id' => $id, 'title' => $otherTitle, 'languageId' => self::OTHER_LANGUAGE],
        ]);
    }
}
