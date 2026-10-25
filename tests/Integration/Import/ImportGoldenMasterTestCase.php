<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Import;

require_once __DIR__ . '/ChangesSnapshot.php';
require_once __DIR__ . '/../../Doubles/Import/FixtureProdsDownloader.php';

use App\Paths\PathsManager;
use App\Users\CurrentUserService;
use Cache;
use ConfigManager;
use controller;
use DI\Container;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;
use ProdsDownloader;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use renderer;
use RuntimeException;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Shared\EntityType;
use ZxArt\Tests\Doubles\Import\FixtureProdsDownloader;

/**
 * Golden master of the imports: a scenario imports fixed DTOs the way the
 * import cron does, and everything it wrote must equal the stored snapshot in
 * `snapshots/<test class>/<test>.json`.
 *
 * Each test must run in its own process — the services keep import ids and
 * elements in memory, so a subclass carries #[RunTestsInSeparateProcesses],
 * which is not inherited — and in a transaction that is rolled back; uploads go to a
 * temporary folder, downloads come from `fixtures/`. A missing snapshot is
 * recorded and the test is reported incomplete; a stored one is never
 * rewritten — delete it to record it again.
 */
abstract class ImportGoldenMasterTestCase extends TestCase
{
    private const array REDIRECTED_PATHS = ['uploads', 'releases', 'uploadsCache'];

    protected Container $container;
    protected Connection $db;
    protected FixtureProdsDownloader $downloader;
    private ChangesSnapshot $changes;
    private string $temporaryPath = '';

    #[\Override]
    protected function setUp(): void
    {
        $this->container = controller::getInstance()->getContainer();
        // as in the import applications: resolving the admin structure manager
        // makes it the structure manager of every service resolved after it
        $structureManager = $this->container->get('adminStructureManager');
        $this->db = $this->container->get(Connection::class);

        $relativePath = 'temporary/import-golden-master/' . getmypid() . '/';
        $this->temporaryPath = ROOT_PATH . $relativePath;
        $pathsConfig = $this->container->get(ConfigManager::class)->getConfig('paths');
        foreach (self::REDIRECTED_PATHS as $pathName) {
            $pathsConfig->set($pathName, $relativePath . $pathName . '/');
            $this->container->get(PathsManager::class)->ensureDirectory(ROOT_PATH . $relativePath . $pathName . '/');
        }

        // what an import application sets up before it runs the import
        renderer::getFactory()->setContainer($this->container);
        renderer::createInstance('smarty');
        $this->container->get(Cache::class)->enable(false, false, true);
        $user = $this->container->get(CurrentUserService::class)->getCurrentUser();
        $userId = $user->checkUser('crontab', null, true);
        if (!$userId) {
            throw new RuntimeException('The crontab user is missing from the local database');
        }
        $user->switchUser($userId);
        $structureManager->setRequestedPath([]);
        $structureManager->setPrivilegeChecking(false);

        $this->downloader = $this->container->make(FixtureProdsDownloader::class);
        $this->downloader->setFixturesPath(__DIR__ . '/fixtures/files/');
        // every import service downloads through it
        $this->container->set(ProdsDownloader::class, $this->downloader);

        $this->db->beginTransaction();
        $this->changes = new ChangesSnapshot($this->db);
        $this->changes->begin();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $createdIds = $this->changes->newElementIds();
        $this->db->rollBack();
        $this->removeTemporaryFolder();
        if ($createdIds !== [] && $this->db->table('structure_elements')->whereIn('id', $createdIds)->exists()) {
            throw new RuntimeException('The import committed its writes: the rollback left elements ' . implode(', ', $createdIds));
        }
    }

    protected function assertMatchesSnapshot(): void
    {
        $folders = [];
        foreach (self::REDIRECTED_PATHS as $pathName) {
            $folders[$pathName] = $this->temporaryPath . $pathName . '/';
        }
        $actual = ChangesSnapshot::encode($this->changes->capture($folders)) . "\n";

        $snapshotFolder = __DIR__ . '/snapshots/' . (new ReflectionClass($this))->getShortName() . '/';
        $snapshotPath = $snapshotFolder . $this->name() . '.json';
        if (!is_file($snapshotPath)) {
            $this->container->get(PathsManager::class)->ensureDirectory($snapshotFolder);
            file_put_contents($snapshotPath, $actual);
            self::markTestIncomplete('Snapshot recorded: ' . $snapshotPath);
        }
        self::assertSame(file_get_contents($snapshotPath), $actual);
    }

    /**
     * @return int[] the structure elements written since the test began
     */
    protected function newElementIds(): array
    {
        return $this->changes->newElementIds();
    }

    /**
     * Points a source's id at an existing element, as an earlier import of
     * countries or categories would have.
     */
    protected function mapImportId(int $elementId, string $importId, ImportOrigin $origin, EntityType $type): void
    {
        $this->container->get(ImportIdOperator::class)->saveImportId($elementId, $importId, $origin, $type);
    }

    private function removeTemporaryFolder(): void
    {
        if (!is_dir($this->temporaryPath)) {
            return;
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->temporaryPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->temporaryPath);
    }
}
