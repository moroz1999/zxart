<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/WriteToolTestCase.php';
require_once __DIR__ . '/../../Doubles/Http/LocalFileServer.php';
require_once __DIR__ . '/../../Doubles/Http/LoopbackUrlGuard.php';

use fileElement;
use Override;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Prods\ProdDto;
use ZxArt\Assistant\Prods\ProdDtos;
use ZxArt\Assistant\Releases\ReleaseDto;
use ZxArt\Assistant\Releases\ReleaseDtos;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\ProdFileKind;
use ZxArt\Prods\UseCases\AddProdFilesCommand;
use ZxArt\Prods\UseCases\AddProdFilesHandler;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\UseCases\CreateReleaseCommand;
use ZxArt\Releases\UseCases\CreateReleaseHandler;
use ZxArt\Shared\Dto\AddedFilesDto;
use ZxArt\Shared\Dto\ListedFileDto;
use ZxArt\Shared\Http\UrlGuard;
use ZxArt\Tests\Doubles\Http\LocalFileServer;
use ZxArt\Tests\Doubles\Http\LoopbackUrlGuard;
use zxReleaseElement;

/**
 * The file actions of productions and releases, fetching from a local server the
 * test guard lets through.
 */
#[RunTestsInSeparateProcesses]
final class FileWriteToolsTest extends WriteToolTestCase
{
    private LocalFileServer $server;
    /** @var string[] stored files the rollback does not take back */
    private array $storedPaths = [];

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->server = new LocalFileServer();
        $this->container->set(UrlGuard::class, new LoopbackUrlGuard());
    }

    #[Override]
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

    public function testAddProdFilesStateIsTheProductionWithItsFilesAndItAdds(): void
    {
        $this->signInAsEditor();
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldwrite Demo')))->id;
        $mapIds = $this->container->get(AddProdFilesHandler::class)->handle(new AddProdFilesCommand($prodId, ProdFileKind::Maps, [$this->server->url('/image.png')]))->fileIds;
        $this->remember($mapIds);
        $arguments = '{"prodId": ' . $prodId . ', "kind": "maps", "urls": ["' . $this->server->url('/redirect-image') . '"]}';

        $state = $this->actionState('add_prod_files', $arguments);

        self::assertInstanceOf(ProdDto::class, $state);
        self::assertEquals([new ListedFileDto('maps', $mapIds[0], 'image.png')], $state->files);
        $added = $this->actionWrite('add_prod_files', $arguments);
        self::assertInstanceOf(AddedFilesDto::class, $added);
        $this->remember($added->fileIds);
        self::assertCount(2, $this->container->get(ProdDtos::class)->prod($prodId)->files);
    }

    public function testAddProdFilesWithoutAUrlIsRefusedWhenItRuns(): void
    {
        $this->signInAsEditor();
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldwrite Demo')))->id;

        $this->assertRefused(400, ProdDataException::class, fn() => $this->actionWrite('add_prod_files', '{"prodId": ' . $prodId . ', "kind": "maps", "urls": []}'));
    }

    public function testDeleteProdFileStateIsTheProductionWithTheFileAndItDeletes(): void
    {
        $this->signInAsEditor();
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldwrite Demo')))->id;
        $fileIds = $this->container->get(AddProdFilesHandler::class)->handle(new AddProdFilesCommand($prodId, ProdFileKind::Maps, [$this->server->url('/image.png')]))->fileIds;
        $this->remember($fileIds);
        $arguments = '{"prodId": ' . $prodId . ', "fileId": ' . $fileIds[0] . '}';

        self::assertEquals($this->container->get(ProdDtos::class)->prod($prodId), $this->actionState('delete_prod_file', $arguments));
        $this->actionWrite('delete_prod_file', $arguments);

        self::assertFalse($this->elementExists($fileIds[0]));
    }

    public function testAddReleaseFilesStateIsTheReleaseWithItsFilesAndItAddsThenDeletes(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease();
        $arguments = '{"releaseId": ' . $releaseId . ', "kind": "inlays", "urls": ["' . $this->server->url('/image.png') . '"]}';

        self::assertEquals($this->release($releaseId), $this->actionState('add_release_files', $arguments));
        $added = $this->actionWrite('add_release_files', $arguments);

        self::assertInstanceOf(AddedFilesDto::class, $added);
        $this->remember($added->fileIds);
        self::assertEquals([new ListedFileDto('inlays', $added->fileIds[0], 'image.png')], $this->release($releaseId)->files);
        $deleteArguments = '{"releaseId": ' . $releaseId . ', "fileId": ' . $added->fileIds[0] . '}';
        self::assertEquals($this->release($releaseId), $this->actionState('delete_release_file', $deleteArguments));
        $this->actionWrite('delete_release_file', $deleteArguments);
        self::assertFalse($this->elementExists($added->fileIds[0]));
    }

    public function testSetReleaseFileStateIsTheReleaseWithItsFileAndItReplacesIt(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease();
        $arguments = '{"releaseId": ' . $releaseId . ', "url": "' . $this->server->url('/intro.tap') . '", "fileName": "game.tap"}';

        self::assertEquals($this->release($releaseId), $this->actionState('set_release_file', $arguments));
        $this->actionWrite('set_release_file', $arguments);

        $release = $this->structureManager->getElementById($releaseId);
        self::assertInstanceOf(zxReleaseElement::class, $release);
        $this->storedPaths[] = $release->getFilePath();
        self::assertSame('game.tap', $this->release($releaseId)->fileName);
    }

    private function release(int $releaseId): ReleaseDto
    {
        return $this->container->get(ReleaseDtos::class)->release($releaseId);
    }

    private function createRelease(): int
    {
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldwrite Demo')))->id;

        return $this->container->get(CreateReleaseHandler::class)->handle(new CreateReleaseCommand(new ReleaseFieldsDto(title: 'Goldwrite Tape', prodId: $prodId)))->id;
    }

    /**
     * @param int[] $fileIds
     */
    private function remember(array $fileIds): void
    {
        foreach ($fileIds as $fileId) {
            $file = $this->structureManager->getElementById($fileId);
            if ($file instanceof fileElement) {
                $this->storedPaths[] = $file->getFilePath();
            }
        }
    }
}
