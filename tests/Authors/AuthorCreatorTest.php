<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

require_once __DIR__ . '/../Doubles/Elements/AuthorElementSpy.php';

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureManager;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Authors\Services\AuthorCreator;
use ZxArt\Authors\Services\AuthorFieldsWriter;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Shared\Elements\CreatorPrivilegesGranter;
use ZxArt\Tests\Doubles\Elements\AuthorElementSpy;

final class AuthorCreatorTest extends TestCase
{
    public function testCreatesTheElementWhereItsNameBelongsAndWritesItsFields(): void
    {
        $element = new AuthorElementSpy();
        $fields = new AuthorFieldsDto(title: 'Fikus');
        $service = $this->createMock(AuthorsService::class);
        $service->expects($this->once())->method('manufactureAuthorElement')->with('Fikus')->willReturn($element);
        $writer = $this->createMock(AuthorFieldsWriter::class);
        $writer->expects($this->once())->method('apply')->with($element, $fields);

        self::assertSame($element, $this->createCreator($service, $writer)->create($fields));
    }

    public function testElementWithoutANameIsBadRequest(): void
    {
        $service = $this->createMock(AuthorsService::class);
        $service->expects($this->never())->method('manufactureAuthorElement');

        $this->assertStatus(400, fn() => $this->createCreator($service)->create(new AuthorFieldsDto(realName: '')));
    }

    public function testAuthorWithoutTitleIsCreatedUnderItsRealName(): void
    {
        $service = $this->createMock(AuthorsService::class);
        $service->expects($this->once())
            ->method('manufactureAuthorElement')
            ->with('Ivan Petrov')
            ->willReturn(new AuthorElementSpy());

        $this->createCreator($service)->create(new AuthorFieldsDto(title: '', realName: 'Ivan Petrov'));
    }

    public function testFailedElementCreationIsAnInternalError(): void
    {
        $service = $this->createStub(AuthorsService::class);
        $service->method('manufactureAuthorElement')->willReturn(null);

        $this->assertStatus(500, fn() => $this->createCreator($service)->create(new AuthorFieldsDto(title: 'Fikus')));
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

        $this->createCreator(privilegesManager: $privilegesManager)->completeCreation(new AuthorElementSpy(), new AuthorFieldsDto());

        self::assertSame([
            '7:42:author.showPublicForm:allow',
            '7:42:author.publicReceive:allow',
            '7:42:author.publicDelete:allow',
            '7:42:author.deleteFile:allow',
        ], $granted);
    }

    private function createCreator(
        ?AuthorsService $service = null,
        ?AuthorFieldsWriter $writer = null,
        ?privilegesManager $privilegesManager = null,
    ): AuthorCreator {
        $user = $this->createStub(CurrentUser::class);
        $user->id = 7;
        $currentUserService = $this->createStub(CurrentUserService::class);
        $currentUserService->method('getCurrentUser')->willReturn($user);

        return new AuthorCreator(
            new AuthorRepository($this->createStub(structureManager::class), $service ?? $this->createStub(AuthorsService::class)),
            $writer ?? $this->createStub(AuthorFieldsWriter::class),
            new CreatorPrivilegesGranter($privilegesManager ?? $this->createStub(privilegesManager::class), $currentUserService),
        );
    }

    private function assertStatus(int $expectedStatus, callable $call): void
    {
        try {
            $call();
            self::fail('AuthorDataException was not thrown');
        } catch (AuthorDataException $exception) {
            self::assertSame($expectedStatus, $exception->getStatusCode());
        }
    }
}
