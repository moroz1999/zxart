<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

require_once __DIR__ . '/../Doubles/Elements/GroupAliasElementSpy.php';

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureManager;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Groups\Services\GroupAliasCreator;
use ZxArt\Groups\Services\GroupAliasFieldsWriter;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Shared\Elements\CreatorPrivilegesGranter;
use ZxArt\Tests\Doubles\Elements\GroupAliasElementSpy;

final class GroupAliasCreatorTest extends TestCase
{
    public function testCreatesTheElementWhereItsNameBelongsAndWritesItsFields(): void
    {
        $element = new GroupAliasElementSpy();
        $fields = new GroupAliasFieldsDto(title: 'FNM', groupId: 5);
        $service = $this->createMock(GroupsService::class);
        $service->expects($this->once())->method('manufactureAliasElement')->with('FNM')->willReturn($element);
        $writer = $this->createMock(GroupAliasFieldsWriter::class);
        $writer->expects($this->once())->method('apply')->with($element, $fields);

        self::assertSame($element, $this->createCreator($service, $writer)->create($fields));
    }

    public function testElementWithoutANameIsBadRequest(): void
    {
        $service = $this->createMock(GroupsService::class);
        $service->expects($this->never())->method('manufactureAliasElement');

        $this->assertStatus(400, fn() => $this->createCreator($service)->create(new GroupAliasFieldsDto(groupId: 5)));
    }

    public function testAliasWithoutItsOwnerIsBadRequest(): void
    {
        $service = $this->createMock(GroupsService::class);
        $service->expects($this->never())->method('manufactureAliasElement');

        $this->assertStatus(400, fn() => $this->createCreator($service)->create(new GroupAliasFieldsDto(title: 'FNM')));
    }

    public function testFailedElementCreationIsAnInternalError(): void
    {
        $service = $this->createStub(GroupsService::class);
        $service->method('manufactureAliasElement')->willReturn(null);

        $this->assertStatus(500, fn() => $this->createCreator($service)->create(new GroupAliasFieldsDto(title: 'FNM', groupId: 5)));
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

        $this->createCreator(privilegesManager: $privilegesManager)->completeCreation(new GroupAliasElementSpy(), new GroupAliasFieldsDto());

        self::assertSame([
            '7:42:groupAlias.showPublicForm:allow',
            '7:42:groupAlias.publicReceive:allow',
            '7:42:groupAlias.publicDelete:allow',
        ], $granted);
    }

    private function createCreator(
        ?GroupsService $service = null,
        ?GroupAliasFieldsWriter $writer = null,
        ?privilegesManager $privilegesManager = null,
    ): GroupAliasCreator {
        $user = $this->createStub(CurrentUser::class);
        $user->id = 7;
        $currentUserService = $this->createStub(CurrentUserService::class);
        $currentUserService->method('getCurrentUser')->willReturn($user);

        return new GroupAliasCreator(
            new GroupAliasRepository($this->createStub(structureManager::class), $service ?? $this->createStub(GroupsService::class)),
            $writer ?? $this->createStub(GroupAliasFieldsWriter::class),
            new CreatorPrivilegesGranter($privilegesManager ?? $this->createStub(privilegesManager::class), $currentUserService),
        );
    }

    private function assertStatus(int $expectedStatus, callable $call): void
    {
        try {
            $call();
            self::fail('GroupAliasDataException was not thrown');
        } catch (GroupAliasDataException $exception) {
            self::assertSame($expectedStatus, $exception->getStatusCode());
        }
    }
}
