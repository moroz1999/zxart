<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

require_once __DIR__ . '/../Doubles/Elements/ZxReleaseElementSpy.php';

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureManager;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ReleaseCreator;
use ZxArt\Releases\Services\ReleaseFieldsWriter;
use ZxArt\Shared\Elements\CreatorPrivilegesGranter;
use ZxArt\Tests\Doubles\Elements\ZxReleaseElementSpy;

final class ReleaseCreatorTest extends TestCase
{
    public function testCreatesTheReleaseUnderItsProductionAndWritesItsFields(): void
    {
        $release = new ZxReleaseElementSpy(elementId: 42, createdTimestamp: 1700000000);
        $fields = new ReleaseFieldsDto(title: 'Dizzy (crack)', prodId: 9);
        $structureManager = $this->createMock(structureManager::class);
        $structureManager->expects($this->once())
            ->method('createElement')
            ->with('zxRelease', 'show', 9)
            ->willReturn($release);
        $writer = $this->createMock(ReleaseFieldsWriter::class);
        $writer->expects($this->once())->method('apply')->with($release, $fields);

        $created = $this->createCreator($structureManager, $writer)->create($fields);

        self::assertSame($release, $created);
        self::assertSame(1700000000, $release->values['dateAdded']);
        self::assertSame(7, $release->values['userId']);
    }

    public function testReleaseWithoutProductionIsBadRequest(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $structureManager->expects($this->never())->method('createElement');

        try {
            $this->createCreator($structureManager)->create(new ReleaseFieldsDto(title: 'Dizzy'));
            self::fail('ReleaseDataException was not thrown');
        } catch (ReleaseDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
    }

    public function testFailedElementCreationIsAnInternalError(): void
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('createElement')->willReturn(null);

        try {
            $this->createCreator($structureManager)->create(new ReleaseFieldsDto(prodId: 9));
            self::fail('ReleaseDataException was not thrown');
        } catch (ReleaseDataException $exception) {
            self::assertSame(500, $exception->getStatusCode());
        }
    }

    public function testCompletingAnElementTheFormCreatedFillsAndGrantsIt(): void
    {
        // the release form creates its element itself and hands it over
        $release = new ZxReleaseElementSpy(elementId: 42);
        $granted = [];
        $privilegesManager = $this->createStub(privilegesManager::class);
        $privilegesManager->method('setPrivilege')->willReturnCallback(
            function (int $userId, int $elementId, string $type, string $action, string $kind) use (&$granted): void {
                $granted[] = "$userId:$elementId:$type.$action:$kind";
            },
        );

        $this->createCreator(privilegesManager: $privilegesManager)->completeCreation($release, new ReleaseFieldsDto());

        self::assertSame(7, $release->values['userId']);
        self::assertSame([
            '7:42:zxRelease.showPublicForm:allow',
            '7:42:zxRelease.publicReceive:allow',
            '7:42:zxRelease.publicDelete:allow',
            '7:42:zxRelease.deleteFile:allow',
            '7:42:zxRelease.clone:allow',
        ], $granted);
    }

    private function createCreator(
        ?structureManager $structureManager = null,
        ?ReleaseFieldsWriter $writer = null,
        ?privilegesManager $privilegesManager = null,
    ): ReleaseCreator {
        $user = $this->createStub(CurrentUser::class);
        $user->id = 7;
        $currentUserService = $this->createStub(CurrentUserService::class);
        $currentUserService->method('getCurrentUser')->willReturn($user);

        return new ReleaseCreator(
            new ReleaseRepository($structureManager ?? $this->createStub(structureManager::class)),
            $writer ?? $this->createStub(ReleaseFieldsWriter::class),
            new CreatorPrivilegesGranter($privilegesManager ?? $this->createStub(privilegesManager::class), $currentUserService),
            $currentUserService,
        );
    }
}
