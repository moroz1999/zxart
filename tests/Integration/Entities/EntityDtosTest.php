<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Authors\AuthorDtos;
use ZxArt\Assistant\Groups\GroupDtos;
use ZxArt\Assistant\Prods\ProdDtos;
use ZxArt\Assistant\Releases\ReleaseDtos;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Releases\Exception\ReleaseDataException;

/**
 * The assistant's DTOs of each entity type refuse an id of another type.
 */
#[RunTestsInSeparateProcesses]
final class EntityDtosTest extends EntityWriteTestCase
{
    public function testTheDtoOfAnElementOfAnotherTypeIsNotFound(): void
    {
        $this->signInAsEditor();
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild')))->id;
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;

        $readers = [
            ProdDataException::class => fn() => $this->container->get(ProdDtos::class)->prod($groupId),
            ReleaseDataException::class => fn() => $this->container->get(ReleaseDtos::class)->release($groupId),
            AuthorDataException::class => fn() => $this->container->get(AuthorDtos::class)->author($groupId),
            AuthorAliasDataException::class => fn() => $this->container->get(AuthorDtos::class)->alias($groupId),
            GroupDataException::class => fn() => $this->container->get(GroupDtos::class)->group($authorId),
            GroupAliasDataException::class => fn() => $this->container->get(GroupDtos::class)->alias($authorId),
        ];
        foreach ($readers as $exceptionClass => $read) {
            try {
                $read();
                self::fail("$exceptionClass expected");
            } catch (ProdDataException|ReleaseDataException|AuthorDataException|AuthorAliasDataException|GroupDataException|GroupAliasDataException $exception) {
                self::assertInstanceOf($exceptionClass, $exception);
                self::assertSame(404, $exception->getStatusCode());
            }
        }
    }

    private function signInAsEditor(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [
            ['zxProd', 'publicReceive'],
            ['zxRelease', 'publicReceive'],
            ['group', 'publicReceive'],
        ]);
    }
}
