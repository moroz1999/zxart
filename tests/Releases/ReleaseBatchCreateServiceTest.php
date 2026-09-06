<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use App\Paths\PathsManager;
use controller;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use structureElement;
use ZxArt\Forms\FormCreateException;
use ZxArt\Forms\FormCreateService;
use ZxArt\Forms\FormCreateType;
use ZxArt\Forms\StagedUploadsCleaner;
use ZxArt\Releases\Services\ReleaseBatchCreateService;

/**
 * How one submitted release form becomes several releases. The only thing that
 * varies between them is the release file, and the whole question is telling one
 * upload from a list of them: PHP describes a single upload with its own
 * properties and several as a list of exactly those.
 *
 * The files attached beside them — screenshots, inlays — belong to every release,
 * and reach each of them as the very same staged upload, which is why the batch
 * is also what removes those uploads when it is done with them.
 */
#[AllowMockObjectsWithoutExpectations]
class ReleaseBatchCreateServiceTest extends TestCase
{
    private const array SHARED_FIELDS = ['title' => '', 'year' => '1996', 'zxProd' => '17'];

    /** @var list<array<string, mixed>> */
    private array $submittedFields = [];

    private ReleaseBatchCreateService $service;
    private controller $controller;
    private string $cachePath;

    protected function setUp(): void
    {
        $this->submittedFields = [];
        $this->cachePath = sys_get_temp_dir() . '/zxart-release-batch-' . uniqid() . '/';
        mkdir($this->cachePath, 0777, true);

        $formCreateService = $this->createMock(FormCreateService::class);
        $formCreateService->method('submit')->willReturnCallback(
            function (FormCreateType $type, ?int $year, controller $controller, array $fields, ?int $parentId): structureElement {
                $this->submittedFields[] = $fields;
                return $this->createMock(structureElement::class);
            },
        );

        $this->service = new ReleaseBatchCreateService($formCreateService, $this->stagedUploadsCleaner());
        $this->controller = $this->createMock(controller::class);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->cachePath . '*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->cachePath);
    }

    public function testEachUploadedFileBecomesItsOwnRelease(): void
    {
        $releases = $this->service->create(
            $this->controller,
            [...self::SHARED_FIELDS, 'file' => [$this->upload('a.tap'), $this->upload('b.trd')]],
            17,
        );

        $this->assertCount(2, $releases);
        $this->assertSame(['a.tap', 'b.trd'], array_column(array_column($this->submittedFields, 'file'), 'name'));
    }

    public function testEveryReleaseCarriesTheValuesTypedOnce(): void
    {
        $this->service->create(
            $this->controller,
            [...self::SHARED_FIELDS, 'file' => [$this->upload('a.tap'), $this->upload('b.trd')]],
            17,
        );

        foreach ($this->submittedFields as $fields) {
            $this->assertSame('1996', $fields['year']);
            $this->assertSame('17', $fields['zxProd']);
        }
    }

    public function testEveryReleaseGetsTheScreenshotsBesideTheReleaseFiles(): void
    {
        $screenshot = $this->upload('shot.png');

        $this->service->create(
            $this->controller,
            [
                ...self::SHARED_FIELDS,
                'file' => [$this->upload('a.tap'), $this->upload('b.trd')],
                'screenshotsSelector' => [$screenshot],
            ],
            17,
        );

        $this->assertSame([$screenshot], $this->submittedFields[0]['screenshotsSelector']);
        $this->assertSame([$screenshot], $this->submittedFields[1]['screenshotsSelector']);
    }

    public function testListedValuesThatAreNotUploadsReachEveryRelease(): void
    {
        $this->service->create(
            $this->controller,
            [
                ...self::SHARED_FIELDS,
                'file' => [$this->upload('a.tap'), $this->upload('b.trd')],
                'releaseFormat' => ['tap', 'trd'],
                'addAuthorRole' => ['42' => ['code']],
            ],
            17,
        );

        $this->assertSame(['tap', 'trd'], $this->submittedFields[1]['releaseFormat']);
        $this->assertSame(['42' => ['code']], $this->submittedFields[1]['addAuthorRole']);
    }

    public function testASingleUploadIsPassedThroughAsItIs(): void
    {
        $upload = $this->upload('only.tap');

        $releases = $this->service->create($this->controller, [...self::SHARED_FIELDS, 'file' => $upload], 17);

        $this->assertCount(1, $releases);
        $this->assertSame($upload, $this->submittedFields[0]['file']);
    }

    public function testAFormWithNoFileStillCreatesTheReleaseItDescribes(): void
    {
        $releases = $this->service->create($this->controller, self::SHARED_FIELDS, 17);

        $this->assertCount(1, $releases);
        $this->assertSame(self::SHARED_FIELDS, $this->submittedFields[0]);
    }

    public function testTheStagedUploadsAreRemovedOnceEveryReleaseHasReadThem(): void
    {
        $screenshot = $this->stagedUpload('shot.png');

        $this->service->create(
            $this->controller,
            [
                ...self::SHARED_FIELDS,
                'file' => [$this->upload('a.tap'), $this->upload('b.trd')],
                'screenshotsSelector' => [$screenshot],
            ],
            17,
        );

        $this->assertFileDoesNotExist($this->cachePath . basename($screenshot['tmp_name']));
    }

    public function testTheStagedUploadsAreRemovedWhenTheBatchGivesUpHalfWay(): void
    {
        $screenshot = $this->stagedUpload('shot.png');
        $service = new ReleaseBatchCreateService($this->refusingFormCreateService(), $this->stagedUploadsCleaner());

        try {
            $service->create($this->controller, [...self::SHARED_FIELDS, 'file' => [$this->upload('a.tap')], 'screenshotsSelector' => [$screenshot]], 17);
            $this->fail('The refused submit should have been reported');
        } catch (FormCreateException) {
            $this->assertFileDoesNotExist($this->cachePath . basename($screenshot['tmp_name']));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function upload(string $name): array
    {
        return ['name' => $name, 'type' => 'application/octet-stream', 'tmp_name' => '/tmp/' . $name, 'error' => 0, 'size' => 1024];
    }

    /**
     * An upload as it is once the data chunk has moved it into the cache: the
     * submitted `tmp_name` still names it, by its base name.
     *
     * @return array<string, mixed>
     */
    private function stagedUpload(string $name): array
    {
        $upload = $this->upload($name);
        file_put_contents($this->cachePath . basename($upload['tmp_name']), 'staged');

        return $upload;
    }

    private function refusingFormCreateService(): FormCreateService
    {
        $formCreateService = $this->createMock(FormCreateService::class);
        $formCreateService->method('submit')->willThrowException(new FormCreateException('refused', 403));

        return $formCreateService;
    }

    private function stagedUploadsCleaner(): StagedUploadsCleaner
    {
        $pathsManager = $this->createMock(PathsManager::class);
        $pathsManager->method('getPath')->willReturn($this->cachePath);

        return new StagedUploadsCleaner($pathsManager);
    }
}
