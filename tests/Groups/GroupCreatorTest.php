<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

require_once __DIR__ . '/../Doubles/Elements/GroupElementSpy.php';

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureManager;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupCreator;
use ZxArt\Groups\Services\GroupFieldsWriter;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Shared\Elements\CreatorPrivilegesGranter;
use ZxArt\Tests\Doubles\Elements\GroupElementSpy;

final class GroupCreatorTest extends TestCase
{
    public function testCreatesTheElementWhereItsNameBelongsAndWritesItsFields(): void
    {
        $element = new GroupElementSpy();
        $fields = new GroupFieldsDto(title: 'Fenomen');
        $service = $this->createMock(GroupsService::class);
        $service->expects($this->once())->method('manufactureGroupElement')->with('Fenomen')->willReturn($element);
        $writer = $this->createMock(GroupFieldsWriter::class);
        $writer->expects($this->once())->method('apply')->with($element, $fields);

        self::assertSame($element, $this->createCreator($service, $writer)->create($fields));
    }

    public function testElementWithoutANameIsBadRequest(): void
    {
        $service = $this->createMock(GroupsService::class);
        $service->expects($this->never())->method('manufactureGroupElement');

        $this->assertStatus(400, fn() => $this->createCreator($service)->create(new GroupFieldsDto(abbreviation: 'FNM')));
    }

    public function testFailedElementCreationIsAnInternalError(): void
    {
        $service = $this->createStub(GroupsService::class);
        $service->method('manufactureGroupElement')->willReturn(null);

        $this->assertStatus(500, fn() => $this->createCreator($service)->create(new GroupFieldsDto(title: 'Fenomen')));
    }

    public function testCompletingAnElementGrantsItToItsCreator(): void
    {
        $granted = [];
        $privilegesManager = $this->createStub(privilegesManager::class);
        $privilegesManager->method('setPrivilege')->willReturnCallback(
            function (int $userId, int $elementId, string $type, string $action, string $kind) use (&$granted): void {
                $granted[] = "$userId:$elementId:$type.$action:$kind";
            },
        );

        $this->createCreator(privilegesManager: $privilegesManager)->completeCreation(new GroupElementSpy(), new GroupFieldsDto());

        self::assertSame([
            '7:42:group.showPublicForm:allow',
            '7:42:group.publicReceive:allow',
            '7:42:group.publicDelete:allow',
            '7:42:group.deleteFile:allow',
            '7:42:group.deleteAuthor:allow',
        ], $granted);
    }

    private function createCreator(
        ?GroupsService $service = null,
        ?GroupFieldsWriter $writer = null,
        ?privilegesManager $privilegesManager = null,
    ): GroupCreator {
        $user = $this->createStub(CurrentUser::class);
        $user->id = 7;
        $currentUserService = $this->createStub(CurrentUserService::class);
        $currentUserService->method('getCurrentUser')->willReturn($user);

        return new GroupCreator(
            new GroupRepository($this->createStub(structureManager::class), $service ?? $this->createStub(GroupsService::class)),
            $writer ?? $this->createStub(GroupFieldsWriter::class),
            new CreatorPrivilegesGranter($privilegesManager ?? $this->createStub(privilegesManager::class), $currentUserService),
        );
    }

    private function assertStatus(int $expectedStatus, callable $call): void
    {
        try {
            $call();
            self::fail('GroupDataException was not thrown');
        } catch (GroupDataException $exception) {
            self::assertSame($expectedStatus, $exception->getStatusCode());
        }
    }
}
