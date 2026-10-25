<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';
require_once __DIR__ . '/../../Doubles/Http/LocalFileServer.php';
require_once __DIR__ . '/../../Doubles/Http/LoopbackUrlGuard.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\ProdFileKind;
use ZxArt\Prods\Services\ProdStateService;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\ReleaseFileKind;
use ZxArt\Releases\Services\ReleaseDataService;
use ZxArt\Releases\Services\ReleaseStateService;
use ZxArt\Shared\Dto\AttachedFileDto;
use ZxArt\Shared\Http\UrlGuard;
use ZxArt\Tests\Doubles\Http\LocalFileServer;
use ZxArt\Tests\Doubles\Http\LoopbackUrlGuard;
use zxReleaseElement;

/**
 * Files fetched from a URL into a production or a release, from a local server
 * the test guard lets through.
 */
#[RunTestsInSeparateProcesses]
final class EntityFilesTest extends EntityWriteTestCase
{
    private LocalFileServer $server;
    /** @var string[] stored files the rollback does not take back */
    private array $storedPaths = [];

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->server = new LocalFileServer();
        $this->container->set(UrlGuard::class, new LoopbackUrlGuard());
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->server->stop();
        foreach ($this->storedPaths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        parent::tearDown();
    }

    public function testAddFilesStoresEachFileInTheListOfItsKind(): void
    {
        $this->signInAsEditor();
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $this->ageModificationTime($prodId);

        $added = $this->prods()->addFiles($prodId, ProdFileKind::Screenshots, [
            $this->server->url('/image.png'),
            $this->server->url('/redirect-image'),
        ]);
        $this->rememberStoredFiles($added->fileIds);

        self::assertSame($prodId, $added->id);
        self::assertCount(2, $added->fileIds);
        self::assertSame($added->fileIds, $this->linkedChildren($prodId, 'connectedFile'));
        self::assertSame(['image.png', 'image.png'], $this->storedFileNames($added->fileIds));
        self::assertGreaterThan(1, $this->modificationTime($prodId));
        self::assertContains('publicReceive', $this->loggedActions($prodId));
    }

    public function testAFileOfATypeTheKindDoesNotTakeIsRefusedAndNothingIsStored(): void
    {
        $this->signInAsEditor();
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;

        try {
            $this->prods()->addFiles($prodId, ProdFileKind::Inlays, [
                $this->server->url('/image.png'),
                $this->server->url('/page.html'),
            ]);
            self::fail('An HTML page is not an inlay');
        } catch (ProdDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame([], $this->linkedChildren($prodId, 'inlayFilesSelector'));
    }

    public function testAnAddressTheGuardRefusesIsABadRequest(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease();

        try {
            $this->releases()->addFiles($releaseId, ReleaseFileKind::Screenshots, [$this->server->url('/image.png', 'refused.test')]);
            self::fail('The guard refuses the host');
        } catch (ReleaseDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame([], $this->linkedChildren($releaseId, 'screenshotsSelector'));
    }

    public function testAddingFilesNeedsThePrivilegeOnTheElement(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
        $prodId = (int)$this->db->table('module_zxprod')->min('id');

        try {
            $this->prods()->addFiles($prodId, ProdFileKind::Maps, [$this->server->url('/image.png')]);
            self::fail('Adding files must need zxProd.publicReceive');
        } catch (ProdDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
    }

    public function testSetFileMakesTheDownloadTheReleaseFileAndParsesIt(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease();
        $this->ageModificationTime($releaseId);

        $this->releases()->setFile($releaseId, $this->server->url('/intro.tap'), null);

        $release = $this->structureManager->getElementById($releaseId);
        self::assertInstanceOf(zxReleaseElement::class, $release);
        $this->storedPaths[] = $release->getFilePath();
        self::assertSame(
            ['file' => (string)$releaseId, 'fileName' => 'intro.tap', 'parsed' => 1],
            $this->storedRow('module_zxrelease', $releaseId, ['file', 'fileName', 'parsed']),
        );
        self::assertFileExists($release->getFilePath());
        self::assertGreaterThan(1, $this->modificationTime($releaseId));
    }

    public function testSetFileTakesTheGivenFileName(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease();

        $this->releases()->setFile($releaseId, $this->server->url('/intro.tap'), 'Goldmaster Intro.tap');

        $release = $this->structureManager->getElementById($releaseId);
        self::assertInstanceOf(zxReleaseElement::class, $release);
        $this->storedPaths[] = $release->getFilePath();
        // the name is stored URL-encoded, as an upload's is
        self::assertSame(['fileName' => 'Goldmaster+Intro.tap'], $this->storedRow('module_zxrelease', $releaseId, ['fileName']));
    }

    public function testARefusedDownloadKeepsTheReleaseFile(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease();
        $stored = $this->storedRow('module_zxrelease', $releaseId, ['file', 'fileName', 'parsed']);

        try {
            $this->releases()->setFile($releaseId, $this->server->url('/missing'), null);
            self::fail('A missing file cannot be the release file');
        } catch (ReleaseDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame($stored, $this->storedRow('module_zxrelease', $releaseId, ['file', 'fileName', 'parsed']));
    }

    private function signInAsEditor(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [
            ['zxProd', 'publicReceive'],
            ['zxRelease', 'publicReceive'],
        ]);
    }

    private function createRelease(): int
    {
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;

        return $this->releases()->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $prodId))->id;
    }

    /**
     * @param int[] $fileIds
     * @return list<string>
     */
    private function storedFileNames(array $fileIds): array
    {
        return array_map(
            fn(int $fileId): string => (string)$this->db->table('module_file')->where('id', '=', $fileId)->value('fileName'),
            $fileIds,
        );
    }

    /**
     * @param int[] $fileIds
     */
    private function rememberStoredFiles(array $fileIds): void
    {
        foreach ($fileIds as $fileId) {
            $file = $this->structureManager->getElementById($fileId);
            if ($file instanceof \fileElement) {
                $this->storedPaths[] = $file->getFilePath();
            }
        }
    }

    public function testTheStatesListTheFilesOfEachKindAndTheReleaseFileSize(): void
    {
        $this->signInAsEditor();
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $prodFileIds = $this->prods()->addFiles($prodId, ProdFileKind::Maps, [$this->server->url('/image.png')])->fileIds;
        $this->rememberStoredFiles($prodFileIds);
        $releaseId = $this->createRelease();
        $releaseFileIds = $this->releases()->addFiles($releaseId, ReleaseFileKind::Inlays, [$this->server->url('/image.png')])->fileIds;
        $this->rememberStoredFiles($releaseFileIds);
        $this->releases()->setFile($releaseId, $this->server->url('/intro.tap'), null);
        $release = $this->structureManager->getElementById($releaseId);
        self::assertInstanceOf(zxReleaseElement::class, $release);
        $this->storedPaths[] = $release->getFilePath();

        $prodState = $this->container->get(ProdStateService::class)->get($prodId);
        $releaseState = $this->container->get(ReleaseStateService::class)->get($releaseId);

        self::assertEquals(
            ['screenshots' => [], 'inlays' => [], 'maps' => [new AttachedFileDto($prodFileIds[0], 'image.png')], 'rzx' => []],
            $prodState->files,
        );
        self::assertEquals(
            ['screenshots' => [], 'inlays' => [new AttachedFileDto($releaseFileIds[0], 'image.png')], 'info' => [], 'ads' => []],
            $releaseState->files,
        );
        self::assertSame('intro.tap', $releaseState->fileName);
        self::assertSame(filesize($release->getFilePath()), $releaseState->fileSize);
    }

    private function prods(): ProdDataService
    {
        return $this->container->get(ProdDataService::class);
    }

    private function releases(): ReleaseDataService
    {
        return $this->container->get(ReleaseDataService::class);
    }
}
