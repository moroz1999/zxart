<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Releases\Dbal\ReleasesDbal;

/**
 * A release is reachable only through its production, which needs a category.
 * The latest-added selection leaves the others out in the database, so a block
 * shows as many releases as it asks for.
 */
class ReleasesRepositoryTest extends TestCase
{
    private const int CATEGORY_ID = 900;
    private const int PROD_ID = 800;
    private const int ORPHAN_PROD_ID = 801;

    private SQLiteConnection $db;
    private ReleasesDbal $repository;

    protected function setUp(): void
    {
        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $pdo = $this->db->getPdo();
        $pdo->exec("CREATE TABLE module_zxrelease (id INTEGER PRIMARY KEY, title TEXT DEFAULT '', fileName TEXT DEFAULT '')");
        $pdo->exec('CREATE TABLE files_registry (id INTEGER PRIMARY KEY, md5 TEXT, parentId INTEGER, elementId INTEGER)');
        $pdo->exec('CREATE TABLE structure_elements (id INTEGER PRIMARY KEY, dateCreated INTEGER)');
        $pdo->exec('CREATE TABLE structure_links (parentStructureId INTEGER, childStructureId INTEGER, type TEXT)');
        $this->addLink(self::CATEGORY_ID, self::PROD_ID, 'zxProdCategory');
        $this->repository = new ReleasesDbal($this->db);
    }

    public function testLatestAddedLeavesOutReleaseOfProdWithoutCategory(): void
    {
        $this->addRelease(1, self::PROD_ID);
        $this->addRelease(2, self::ORPHAN_PROD_ID);
        $this->addRelease(3, self::PROD_ID);

        $this->assertSame([3, 1], $this->repository->getLatestAddedIds(2));
    }

    public function testCountByProdCountsEveryReleaseUnderTheProduction(): void
    {
        $this->addRelease(1, self::PROD_ID);
        $this->addRelease(2, self::ORPHAN_PROD_ID);
        $this->addRelease(3, self::PROD_ID);
        $this->addLink(self::PROD_ID, 4, 'connectedFile');

        $this->assertSame(2, $this->repository->countByProdId(self::PROD_ID));
        $this->assertSame(1, $this->repository->countByProdId(self::ORPHAN_PROD_ID));
    }

    public function testFindByProdAnswersTheReleasesUnderIt(): void
    {
        $this->addRelease(1, self::PROD_ID);
        $this->addRelease(2, self::ORPHAN_PROD_ID);
        $this->addRelease(3, self::PROD_ID);

        $this->assertSame([1, 3], $this->repository->findIdsByProdId(self::PROD_ID));
    }

    public function testFindByTitleAndByFileNameMatchAPart(): void
    {
        $this->addRelease(1, self::PROD_ID, 'Elite (128k)', 'elite128.tap');
        $this->addRelease(2, self::PROD_ID, 'Elite', 'elite.trd');
        $this->addRelease(3, self::PROD_ID, 'Exolon', 'exolon_100%.tap');

        $this->assertSame([1, 2], $this->repository->findIdsByTitle('lite', 10));
        $this->assertSame([1], $this->repository->findIdsByTitle('lite', 1));
        $this->assertSame([3], $this->repository->findIdsByFileName('olon', 10));
    }

    public function testFindByMd5AnswersTheReleasesHoldingThatFileAnywhere(): void
    {
        $this->addRelease(1, self::PROD_ID);
        $this->addRelease(2, self::PROD_ID);
        $this->addRelease(3, self::PROD_ID);
        $this->db->table('files_registry')->insert([
            ['md5' => 'aa', 'parentId' => 0, 'elementId' => 1],
            ['md5' => 'bb', 'parentId' => 5, 'elementId' => 1],
            ['md5' => 'bb', 'parentId' => 0, 'elementId' => 3],
            ['md5' => 'cc', 'parentId' => 0, 'elementId' => 2],
        ]);

        $this->assertSame([1, 3], $this->repository->findIdsByMd5('bb'));
    }

    private function addRelease(int $id, int $prodId, string $title = '', string $fileName = ''): void
    {
        $this->db->table('module_zxrelease')->insert(['id' => $id, 'title' => $title, 'fileName' => $fileName]);
        $this->db->table('structure_elements')->insert(['id' => $id, 'dateCreated' => $id]);
        $this->addLink($prodId, $id, 'structure');
    }

    private function addLink(int $parentId, int $childId, string $type): void
    {
        $this->db->table('structure_links')->insert([
            'parentStructureId' => $parentId,
            'childStructureId' => $childId,
            'type' => $type,
        ]);
    }
}
