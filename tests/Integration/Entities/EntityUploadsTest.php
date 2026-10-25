<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use App\Paths\PathsManager;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Services\GroupDataService;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Dto\ProdFileUploadDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\ProdFileKind;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Dto\ReleaseFileUploadDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\ReleaseFileKind;
use ZxArt\Releases\Services\ReleaseDataService;
use ZxArt\Shared\Dto\UploadedFileDto;

/**
 * Files uploaded with an edit: an author's or a group's image, the file lists
 * of a production or a release, a release's own file, and the flows that make
 * an element per uploaded file.
 */
#[RunTestsInSeparateProcesses]
final class EntityUploadsTest extends EntityWriteTestCase
{
    private const string FIXTURES = __DIR__ . '/../Import/fixtures/files/';
    private const int CATEGORY_DEMO = 92159;

    /** @var string[] uploads the test staged */
    private array $stagedPaths = [];

    #[\Override]
    protected function tearDown(): void
    {
        // the rollback takes the rows back, not the stored files
        $paths = $this->container->get(PathsManager::class);
        foreach ($this->newElementIds() as $elementId) {
            foreach ([$paths->getPath('uploads'), $paths->getPath('releases')] as $directory) {
                if (is_file($directory . $elementId)) {
                    unlink($directory . $elementId);
                }
            }
        }
        foreach ($this->stagedPaths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        parent::tearDown();
    }

    public function testCreateStoresTheAuthorsImage(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);

        $authorId = $this->authors()->create(new AuthorFieldsDto(title: 'Goldmaster Writer'), image: $this->upload('screen-1.png'))->id;

        self::assertSame(
            ['image' => (string)$authorId, 'originalName' => 'screen-1.png'],
            $this->storedRow('module_author', $authorId, ['image', 'originalName']),
        );
        self::assertFileEquals(self::FIXTURES . 'screen-1.png', $this->uploadsPath() . $authorId);
    }

    public function testUpdateReplacesOrRemovesTheGroupsImage(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['group', 'publicReceive']]);
        $groupId = $this->groupData()->create(new GroupFieldsDto(title: 'Goldmaster Guild'), image: $this->upload('screen-1.png'))->id;

        $this->groupData()->update($groupId, new GroupFieldsDto(), image: $this->upload('screen-2.png'));

        self::assertSame(
            ['image' => (string)$groupId, 'originalName' => 'screen-2.png'],
            $this->storedRow('module_group', $groupId, ['image', 'originalName']),
        );
        self::assertFileEquals(self::FIXTURES . 'screen-2.png', $this->uploadsPath() . $groupId);

        $this->groupData()->update($groupId, new GroupFieldsDto(), removeImage: true);

        self::assertSame(['image' => '', 'originalName' => ''], $this->storedRow('module_group', $groupId, ['image', 'originalName']));
        self::assertFileDoesNotExist($this->uploadsPath() . $groupId);
    }

    public function testUpdateAddsEachUploadedFileToTheListOfItsKind(): void
    {
        $this->signInAsProdEditor();
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $this->ageModificationTime($prodId);

        $this->prods()->update($prodId, new ProdFieldsDto(), files: [
            new ProdFileUploadDto(ProdFileKind::Screenshots, $this->upload('screen-1.png')),
            new ProdFileUploadDto(ProdFileKind::Screenshots, $this->upload('screen-2.png')),
            new ProdFileUploadDto(ProdFileKind::Maps, $this->upload('map.png')),
            new ProdFileUploadDto(ProdFileKind::Rzx, $this->upload('playthrough.rzx')),
        ]);

        self::assertSame(['screen-1.png', 'screen-2.png'], $this->fileNames($this->linkedChildren($prodId, 'connectedFile')));
        self::assertSame(['map.png'], $this->fileNames($this->linkedChildren($prodId, 'mapFilesSelector')));
        self::assertSame(['playthrough.rzx'], $this->fileNames($this->linkedChildren($prodId, 'rzx')));
        self::assertGreaterThan(1, $this->modificationTime($prodId));
    }

    public function testAFileTheKindDoesNotTakeRefusesTheWholeUpdate(): void
    {
        $this->signInAsProdEditor();
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $createdBefore = $this->newElementIds();

        $this->assertRefused(400, ProdDataException::class, fn() => $this->prods()->update(
            $prodId,
            new ProdFieldsDto(altTitle: 'Gm Demo'),
            files: [
                new ProdFileUploadDto(ProdFileKind::Screenshots, $this->upload('screen-1.png')),
                new ProdFileUploadDto(ProdFileKind::Screenshots, $this->upload('manual.txt')),
            ],
        ));

        self::assertSame(['altTitle' => ''], $this->storedRow('module_zxprod', $prodId, ['altTitle']));
        self::assertSame($createdBefore, $this->newElementIds());
    }

    public function testUpdateStoresTheReleaseFileAndItsLists(): void
    {
        $this->signInAsReleaseEditor();
        $releaseId = $this->createRelease();

        $this->releases()->update(
            $releaseId,
            new ReleaseFieldsDto(),
            files: [
                new ReleaseFileUploadDto(ReleaseFileKind::Inlays, $this->upload('release-inlay.jpg')),
                new ReleaseFileUploadDto(ReleaseFileKind::Info, $this->upload('manual.txt')),
            ],
            file: $this->upload('alpha.tap'),
        );

        self::assertSame(
            ['file' => (string)$releaseId, 'fileName' => 'alpha.tap', 'parsed' => 1],
            $this->storedRow('module_zxrelease', $releaseId, ['file', 'fileName', 'parsed']),
        );
        self::assertFileEquals(self::FIXTURES . 'alpha.tap', $this->releasesPath() . $releaseId);
        self::assertSame(['release-inlay.jpg'], $this->fileNames($this->linkedChildren($releaseId, 'inlayFilesSelector')));
        self::assertSame(['manual.txt'], $this->fileNames($this->linkedChildren($releaseId, 'infoFilesSelector')));
    }

    public function testBatchUploadMakesAProductionWithAReleasePerFile(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
        $authorId = $this->authors()->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;

        $prodIds = $this->prods()->batchUpload(
            new ProdFieldsDto(year: 1999, categoryIds: [self::CATEGORY_DEMO]),
            [$this->upload('alpha.tap', 'gold_master.tap'), $this->upload('beta.tap')],
            authors: [new AuthorshipInputDto($authorId, ['code'])],
            files: [new ProdFileUploadDto(ProdFileKind::Screenshots, $this->upload('screen-1.png'))],
        )->ids;

        self::assertCount(2, $prodIds);
        self::assertSame(
            [['title' => 'Gold master', 'year' => 1999], ['title' => 'Beta', 'year' => 1999]],
            array_map(fn(int $prodId): array => $this->storedRow('module_zxprod', $prodId, ['title', 'year']), $prodIds),
        );
        foreach ([[$prodIds[0], 'gold_master.tap', 'alpha.tap', 'Gold master'], [$prodIds[1], 'beta.tap', 'beta.tap', 'Beta']] as [$prodId, $fileName, $fixture, $title]) {
            self::assertSame([self::CATEGORY_DEMO], $this->linkedParents($prodId, 'zxProdCategory'));
            self::assertSame([['roles' => ['code'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($prodId, $authorId, 'prod'));
            self::assertSame(['screen-1.png'], $this->fileNames($this->linkedChildren($prodId, 'connectedFile')));
            $releaseIds = $this->linkedChildren($prodId, 'structure');
            self::assertCount(1, $releaseIds);
            self::assertSame(
                ['title' => $title, 'fileName' => $fileName, 'parsed' => 1],
                $this->storedRow('module_zxrelease', $releaseIds[0], ['title', 'fileName', 'parsed']),
            );
            self::assertFileEquals(self::FIXTURES . $fixture, $this->releasesPath() . $releaseIds[0]);
        }
    }

    public function testBatchUploadTakesTheTitleGivenForEveryProduction(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);

        $prodIds = $this->prods()->batchUpload(
            new ProdFieldsDto(title: 'Goldmaster Demo'),
            [$this->upload('alpha.tap'), $this->upload('beta.tap')],
        )->ids;

        self::assertSame(
            [['title' => 'Goldmaster Demo'], ['title' => 'Goldmaster Demo']],
            array_map(fn(int $prodId): array => $this->storedRow('module_zxprod', $prodId, ['title']), $prodIds),
        );
    }

    public function testBatchUploadWithAWrongAuthorCreatesNothing(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
        $groupId = $this->groupData()->create(new GroupFieldsDto(title: 'Goldmaster Guild'))->id;
        $createdBefore = $this->newElementIds();

        $this->assertRefused(400, ProdDataException::class, fn() => $this->prods()->batchUpload(
            new ProdFieldsDto(),
            [$this->upload('alpha.tap'), $this->upload('beta.tap')],
            authors: [new AuthorshipInputDto($groupId)],
        ));

        self::assertSame($createdBefore, $this->newElementIds());
    }

    public function testBatchUploadNeedsAFileAndThePrivilege(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
        $this->assertRefused(400, ProdDataException::class, fn() => $this->prods()->batchUpload(new ProdFieldsDto(), []));

        $this->signIn([self::PUBLIC_VISITORS]);
        $createdBefore = $this->newElementIds();
        $this->assertRefused(403, ProdDataException::class, fn() => $this->prods()->batchUpload(new ProdFieldsDto(), [$this->upload('alpha.tap')]));
        self::assertSame($createdBefore, $this->newElementIds());
    }

    public function testCreateFromFilesMakesAReleasePerFileWithTheSameValues(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $authorId = $this->authors()->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;

        $releaseIds = $this->releases()->createFromFiles(
            new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $prodId, version: '1.0'),
            [$this->upload('alpha.tap'), $this->upload('alpha-128.tap')],
            authors: [new AuthorshipInputDto($authorId, ['release'])],
            files: [new ReleaseFileUploadDto(ReleaseFileKind::Screenshots, $this->upload('release-screen.png'))],
        )->ids;

        self::assertCount(2, $releaseIds);
        self::assertSame($releaseIds, $this->linkedChildren($prodId, 'structure'));
        foreach ([[$releaseIds[0], 'alpha.tap'], [$releaseIds[1], 'alpha-128.tap']] as [$releaseId, $fileName]) {
            self::assertSame(
                ['title' => 'Goldmaster Tape', 'version' => '1.0', 'fileName' => $fileName, 'parsed' => 1],
                $this->storedRow('module_zxrelease', $releaseId, ['title', 'version', 'fileName', 'parsed']),
            );
            self::assertFileEquals(self::FIXTURES . $fileName, $this->releasesPath() . $releaseId);
            self::assertSame([['roles' => ['release'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($releaseId, $authorId, 'release'));
            self::assertSame(['release-screen.png'], $this->fileNames($this->linkedChildren($releaseId, 'screenshotsSelector')));
        }
    }

    public function testCreateFromFilesTakesTheTitleFromAFileWhenNoneIsGiven(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;

        $releaseIds = $this->releases()->createFromFiles(
            new ReleaseFieldsDto(prodId: $prodId),
            [$this->upload('alpha.tap', 'gold_master_128.tap')],
        )->ids;

        self::assertSame(['title' => 'Gold master 128'], $this->storedRow('module_zxrelease', $releaseIds[0], ['title']));
    }

    public function testCreateFromFilesWithoutAFileMakesTheOneReleaseDescribed(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;

        $releaseIds = $this->releases()->createFromFiles(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $prodId), [])->ids;

        self::assertCount(1, $releaseIds);
        self::assertSame(
            ['title' => 'Goldmaster Tape', 'file' => '', 'fileName' => ''],
            $this->storedRow('module_zxrelease', $releaseIds[0], ['title', 'file', 'fileName']),
        );
    }

    public function testCreateFromFilesWithAWrongRoleCreatesNothing(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $authorId = $this->authors()->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $createdBefore = $this->newElementIds();

        $this->assertRefused(400, ReleaseDataException::class, fn() => $this->releases()->createFromFiles(
            new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $prodId),
            [$this->upload('alpha.tap'), $this->upload('beta.tap')],
            authors: [new AuthorshipInputDto($authorId, ['coder'])],
        ));

        self::assertSame($createdBefore, $this->newElementIds());
    }

    /**
     * A fixture staged as a request's upload is: a temporary file and the name
     * it was sent under.
     */
    private function upload(string $fixture, ?string $fileName = null): UploadedFileDto
    {
        $path = (string)tempnam(sys_get_temp_dir(), 'upload');
        copy(self::FIXTURES . $fixture, $path);
        $this->stagedPaths[] = $path;

        return new UploadedFileDto($path, $fileName ?? $fixture);
    }

    /**
     * @param list<int> $fileIds
     * @return list<string>
     */
    private function fileNames(array $fileIds): array
    {
        $names = array_map(
            fn(int $fileId): string => (string)$this->db->table('module_file')->where('id', '=', $fileId)->value('fileName'),
            $fileIds,
        );
        sort($names);

        return $names;
    }

    private function signInAsProdEditor(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive']]);
    }

    private function signInAsReleaseEditor(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxRelease', 'publicReceive']]);
    }

    private function createRelease(): int
    {
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;

        return $this->releases()->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $prodId))->id;
    }

    private function uploadsPath(): string
    {
        return $this->container->get(PathsManager::class)->getPath('uploads');
    }

    private function releasesPath(): string
    {
        return $this->container->get(PathsManager::class)->getPath('releases');
    }

    private function authors(): AuthorDataService
    {
        return $this->container->get(AuthorDataService::class);
    }

    private function groupData(): GroupDataService
    {
        return $this->container->get(GroupDataService::class);
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
