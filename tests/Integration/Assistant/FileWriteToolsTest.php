<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/WriteToolTestCase.php';
require_once __DIR__ . '/../../Doubles/Http/LocalFileServer.php';
require_once __DIR__ . '/../../Doubles/Http/LoopbackUrlGuard.php';

use fileElement;
use Override;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\FieldChangeDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\ProdFileKind;
use ZxArt\Prods\Services\ProdStateService;
use ZxArt\Prods\UseCases\AddProdFilesCommand;
use ZxArt\Prods\UseCases\AddProdFilesHandler;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\ReleaseFileKind;
use ZxArt\Releases\Services\ReleaseStateService;
use ZxArt\Releases\UseCases\AddReleaseFilesCommand;
use ZxArt\Releases\UseCases\AddReleaseFilesHandler;
use ZxArt\Releases\UseCases\CreateReleaseCommand;
use ZxArt\Releases\UseCases\CreateReleaseHandler;
use ZxArt\Shared\Dto\AddedFilesDto;
use ZxArt\Shared\Http\UrlGuard;
use ZxArt\Tests\Doubles\Http\LocalFileServer;
use ZxArt\Tests\Doubles\Http\LoopbackUrlGuard;
use zxReleaseElement;

/**
 * The file tools of productions and releases, fetching from a local server the
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

    public function testAddProdFilesPreviewsTheListGrowingAndAdds(): void
    {
        $this->signInAsEditor();
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldwrite Demo')))->id;
        $this->remember($this->container->get(AddProdFilesHandler::class)->handle(new AddProdFilesCommand($prodId, ProdFileKind::Maps, [$this->server->url('/image.png')]))->fileIds);
        $url = $this->server->url('/redirect-image');
        $arguments = '{"prodId": ' . $prodId . ', "kind": "maps", "urls": ["' . $url . '"]}';

        $preview = $this->preview('add_prod_files', $arguments);

        self::assertSame(ChangeKind::AttachFile, $preview->kind);
        self::assertEquals([new FieldChangeDto('maps', ['image.png'], ['image.png', $url])], $preview->changes);
        $added = $this->execute('add_prod_files', $arguments);
        self::assertInstanceOf(AddedFilesDto::class, $added);
        $this->remember($added->fileIds);
        self::assertCount(2, $this->container->get(ProdStateService::class)->get($prodId)->files['maps']);
    }

    public function testAddProdFilesWithoutAUrlIsRefusedAtThePreview(): void
    {
        $this->signInAsEditor();
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldwrite Demo')))->id;

        $this->assertRefused(400, ProdDataException::class, fn() => $this->preview('add_prod_files', '{"prodId": ' . $prodId . ', "kind": "maps", "urls": []}'));
    }

    public function testDeleteProdFilePreviewsTheListShrinkingAndDeletes(): void
    {
        $this->signInAsEditor();
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldwrite Demo')))->id;
        $fileIds = $this->container->get(AddProdFilesHandler::class)->handle(new AddProdFilesCommand($prodId, ProdFileKind::Maps, [$this->server->url('/image.png')]))->fileIds;
        $this->remember($fileIds);
        $arguments = '{"prodId": ' . $prodId . ', "fileId": ' . $fileIds[0] . '}';

        $preview = $this->preview('delete_prod_file', $arguments);

        self::assertSame(ChangeKind::DeleteFile, $preview->kind);
        self::assertEquals([new FieldChangeDto('maps', ['image.png'], [])], $preview->changes);
        $this->execute('delete_prod_file', $arguments);
        self::assertFalse($this->elementExists($fileIds[0]));
    }

    public function testAddAndDeleteReleaseFilesPreviewTheirList(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease();
        $url = $this->server->url('/image.png');

        self::assertEquals(
            [new FieldChangeDto('inlays', [], [$url])],
            $this->preview('add_release_files', '{"releaseId": ' . $releaseId . ', "kind": "inlays", "urls": ["' . $url . '"]}')->changes,
        );
        $fileIds = $this->container->get(AddReleaseFilesHandler::class)->handle(new AddReleaseFilesCommand($releaseId, ReleaseFileKind::Inlays, [$url]))->fileIds;
        $this->remember($fileIds);

        self::assertEquals(
            [new FieldChangeDto('inlays', ['image.png'], [])],
            $this->preview('delete_release_file', '{"releaseId": ' . $releaseId . ', "fileId": ' . $fileIds[0] . '}')->changes,
        );
    }

    public function testSetReleaseFilePreviewsTheFileItReplacesAndSetsIt(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease();
        $arguments = '{"releaseId": ' . $releaseId . ', "url": "' . $this->server->url('/intro.tap') . '", "fileName": "game.tap"}';

        $preview = $this->preview('set_release_file', $arguments);

        self::assertSame(ChangeKind::AttachFile, $preview->kind);
        self::assertEquals([new FieldChangeDto('file', null, 'game.tap')], $preview->changes);
        $this->execute('set_release_file', $arguments);
        $release = $this->structureManager->getElementById($releaseId);
        self::assertInstanceOf(zxReleaseElement::class, $release);
        $this->storedPaths[] = $release->getFilePath();
        self::assertSame('game.tap', $this->container->get(ReleaseStateService::class)->get($releaseId)->fileName);
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
