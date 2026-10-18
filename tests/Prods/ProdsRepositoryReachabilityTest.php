<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Helpers\AlphanumericColumnSearch;
use ZxArt\Prods\Repositories\ProdsRepository;

/**
 * A production is reachable on the site only through a category. The first-page
 * selections must leave the others out in the database, so a block shows as
 * many productions as it asks for.
 */
class ProdsRepositoryReachabilityTest extends TestCase
{
    private const int CATEGORY_ID = 900;

    private SQLiteConnection $db;
    private ProdsRepository $repository;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $pdo = $this->db->getPdo();
        $pdo->exec('CREATE TABLE module_zxprod (id INTEGER PRIMARY KEY, votes REAL, year INTEGER, dateAdded INTEGER, legalStatus TEXT)');
        $pdo->exec('CREATE TABLE structure_links (parentStructureId INTEGER, childStructureId INTEGER, type TEXT)');
        $this->repository = new ProdsRepository($this->db, new AlphanumericColumnSearch());
    }

    public function testLatestAddedLeavesOutProdWithoutCategory(): void
    {
        $this->addProd(1, true);
        $this->addProd(2, false);
        $this->addProd(3, true);

        $this->assertSame([3, 1], $this->repository->getLatestAddedIds(2));
    }

    public function testNewProdsLeaveOutProdWithoutCategory(): void
    {
        $this->addProd(1, true);
        $this->addProd(2, false);

        $this->assertSame([1], $this->repository->getNewProdIds(10, 0.0, 1));
    }

    public function testForSaleLeavesOutProdWithoutCategory(): void
    {
        $this->addProd(1, true, 'insales');
        $this->addProd(2, false, 'insales');

        $this->assertSame([1], $this->repository->getForSaleOrDonationIds(10));
    }

    private function addProd(int $id, bool $categorized, string $legalStatus = ''): void
    {
        $this->db->table('module_zxprod')->insert([
            'id' => $id,
            'votes' => 5,
            'year' => 2020,
            'dateAdded' => time() - 100 + $id,
            'legalStatus' => $legalStatus,
        ]);
        if ($categorized) {
            $this->db->table('structure_links')->insert([
                'parentStructureId' => self::CATEGORY_ID,
                'childStructureId' => $id,
                'type' => 'zxProdCategory',
            ]);
        }
    }
}
