<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

require_once __DIR__ . '/../Doubles/Elements/AuthorAliasElementSpy.php';

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureManager;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Repositories\AuthorAliasRepository;
use ZxArt\Authors\Services\AuthorAliasCreator;
use ZxArt\Authors\Services\AuthorAliasFieldsWriter;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Shared\Elements\CreatorPrivilegesGranter;
use ZxArt\Tests\Doubles\Elements\AuthorAliasElementSpy;

final class AuthorAliasCreatorTest extends TestCase
{
    public function testCreatesTheElementWhereItsNameBelongsAndWritesItsFields(): void
    {
        $element = new AuthorAliasElementSpy();
        $fields = new AuthorAliasFieldsDto(title: 'Fx', authorId: 7);
        $service = $this->createMock(AuthorsService::class);
        $service->expects($this->once())->method('manufactureAliasElement')->with('Fx')->willReturn($element);
        $writer = $this->createMock(AuthorAliasFieldsWriter::class);
        $writer->expects($this->once())->method('apply')->with($element, $fields);

        self::assertSame($element, $this->createCreator($service, $writer)->create($fields));
    }

    public function testElementWithoutANameIsBadRequest(): void
    {
        $service = $this->createMock(AuthorsService::class);
        $service->expects($this->never())->method('manufactureAliasElement');

        $this->assertStatus(400, fn() => $this->createCreator($service)->create(new AuthorAliasFieldsDto(authorId: 7)));
    }

    public function testAliasWithoutItsOwnerIsBadRequest(): void
    {
        $service = $this->createMock(AuthorsService::class);
        $service->expects($this->never())->method('manufactureAliasElement');

        $this->assertStatus(400, fn() => $this->createCreator($service)->create(new AuthorAliasFieldsDto(title: 'Fx')));
    }

    public function testFailedElementCreationIsAnInternalError(): void
    {
        $service = $this->createStub(AuthorsService::class);
        $service->method('manufactureAliasElement')->willReturn(null);

        $this->assertStatus(500, fn() => $this->createCreator($service)->create(new AuthorAliasFieldsDto(title: 'Fx', authorId: 7)));
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

        $this->createCreator(privilegesManager: $privilegesManager)->completeCreation(new AuthorAliasElementSpy(), new AuthorAliasFieldsDto());

        self::assertSame([
            '7:42:authorAlias.showPublicForm:allow',
            '7:42:authorAlias.publicReceive:allow',
            '7:42:authorAlias.publicDelete:allow',
        ], $granted);
    }

    private function createCreator(
        ?AuthorsService $service = null,
        ?AuthorAliasFieldsWriter $writer = null,
        ?privilegesManager $privilegesManager = null,
    ): AuthorAliasCreator {
        $user = $this->createStub(CurrentUser::class);
        $user->id = 7;
        $currentUserService = $this->createStub(CurrentUserService::class);
        $currentUserService->method('getCurrentUser')->willReturn($user);

        return new AuthorAliasCreator(
            new AuthorAliasRepository($this->createStub(structureManager::class), $service ?? $this->createStub(AuthorsService::class)),
            $writer ?? $this->createStub(AuthorAliasFieldsWriter::class),
            new CreatorPrivilegesGranter($privilegesManager ?? $this->createStub(privilegesManager::class), $currentUserService),
        );
    }

    private function assertStatus(int $expectedStatus, callable $call): void
    {
        try {
            $call();
            self::fail('AuthorAliasDataException was not thrown');
        } catch (AuthorAliasDataException $exception) {
            self::assertSame($expectedStatus, $exception->getStatusCode());
        }
    }
}
