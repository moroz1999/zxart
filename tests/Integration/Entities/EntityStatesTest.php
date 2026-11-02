<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorAliasStateDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\AuthorshipDto;
use ZxArt\Authors\Dto\AuthorStateDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Services\AuthorAliasFieldsReader;
use ZxArt\Authors\Services\AuthorAliasStateService;
use ZxArt\Authors\Services\AuthorFieldsReader;
use ZxArt\Authors\Services\AuthorStateService;
use ZxArt\Authors\UseCases\CreateAuthorAliasCommand;
use ZxArt\Authors\UseCases\CreateAuthorAliasHandler;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupAliasStateDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Dto\GroupStateDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Services\GroupAliasFieldsReader;
use ZxArt\Groups\Services\GroupAliasStateService;
use ZxArt\Groups\Services\GroupFieldsReader;
use ZxArt\Groups\Services\GroupStateService;
use ZxArt\Groups\UseCases\CreateGroupAliasCommand;
use ZxArt\Groups\UseCases\CreateGroupAliasHandler;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Groups\UseCases\SetGroupMemberCommand;
use ZxArt\Groups\UseCases\SetGroupMemberHandler;
use ZxArt\Import\Dbal\ImportOriginsDbal;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Import\ImportOrigin;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Dto\ProdStateDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Services\ProdFieldsReader;
use ZxArt\Prods\Services\ProdStateService;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Prods\UseCases\SetProdMemberCommand;
use ZxArt\Prods\UseCases\SetProdMemberHandler;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Dto\ReleaseStateDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Services\ReleaseFieldsReader;
use ZxArt\Releases\Services\ReleaseStateService;
use ZxArt\Releases\UseCases\CreateReleaseCommand;
use ZxArt\Releases\UseCases\CreateReleaseHandler;
use ZxArt\Releases\UseCases\SetReleaseMemberCommand;
use ZxArt\Releases\UseCases\SetReleaseMemberHandler;
use ZxArt\Shared\EntityType;
use zxProdElement;
use zxReleaseElement;

/**
 * The complete state of each entity type: its editable fields, what it is
 * linked with, and its modification time.
 */
#[RunTestsInSeparateProcesses]
final class EntityStatesTest extends EntityWriteTestCase
{
    private const int CATEGORY_DEMO = 92159;

    public function testProdStateHoldsFieldsAuthorsReleasesFilesOriginsAndModificationTime(): void
    {
        $this->signInAsEditor();
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Demo', year: 1999)))->id;
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;
        $this->container->get(SetProdMemberHandler::class)->handle(new SetProdMemberCommand($prodId, $authorId, ['code']));
        $releaseId = $this->container->get(CreateReleaseHandler::class)->handle(new CreateReleaseCommand(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $prodId)))->id;
        $this->importOrigins()->saveOrigin($prodId, ImportOrigin::Zxdb, '31337', EntityType::Prod);
        $this->importOrigins()->saveOrigin($prodId, ImportOrigin::Pouet, '42', EntityType::Prod);

        $prod = $this->element($prodId, zxProdElement::class);
        self::assertEquals(new ProdStateDto(
            id: $prodId,
            modifiedAt: $this->modificationTime($prodId),
            fields: $this->container->get(ProdFieldsReader::class)->read($prod),
            authors: [new AuthorshipDto(elementId: $prodId, authorId: $authorId, roles: ['code'], startDate: 0, endDate: 0)],
            releaseIds: [$releaseId],
            files: ['screenshots' => [], 'inlays' => [], 'maps' => [], 'rzx' => []],
            importOrigins: [new ImportOriginDto(ImportOrigin::Pouet, '42'), new ImportOriginDto(ImportOrigin::Zxdb, '31337')],
        ), $this->container->get(ProdStateService::class)->get($prodId));
    }

    public function testReleaseStateHoldsFieldsAuthorsFilesOriginsAndModificationTime(): void
    {
        $this->signInAsEditor();
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Demo')))->id;
        $releaseId = $this->container->get(CreateReleaseHandler::class)->handle(new CreateReleaseCommand(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $prodId)))->id;
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;
        $this->container->get(SetReleaseMemberHandler::class)->handle(new SetReleaseMemberCommand($releaseId, $authorId, ['release']));
        $this->importOrigins()->saveOrigin($releaseId, ImportOrigin::Vtrdos, '777', EntityType::Release);

        $release = $this->element($releaseId, zxReleaseElement::class);
        self::assertEquals(new ReleaseStateDto(
            id: $releaseId,
            modifiedAt: $this->modificationTime($releaseId),
            fields: $this->container->get(ReleaseFieldsReader::class)->read($release),
            authors: [new AuthorshipDto(elementId: $releaseId, authorId: $authorId, roles: ['release'], startDate: 0, endDate: 0)],
            fileName: '',
            fileSize: null,
            files: ['screenshots' => [], 'inlays' => [], 'info' => [], 'ads' => []],
            importOrigins: [new ImportOriginDto(ImportOrigin::Vtrdos, '777')],
        ), $this->container->get(ReleaseStateService::class)->get($releaseId));
    }

    public function testAuthorStateHoldsFieldsAliasesMembershipsAndCounts(): void
    {
        $this->signInAsEditor();
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;
        $aliasId = $this->container->get(CreateAuthorAliasHandler::class)->handle(new CreateAuthorAliasCommand(new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: $authorId)))->id;
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild')))->id;
        $this->container->get(SetGroupMemberHandler::class)->handle(new SetGroupMemberCommand($groupId, $authorId, ['coder'], $this->date('15.03.1995'), null));
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Demo', categoryIds: [self::CATEGORY_DEMO])))->id;
        $this->container->get(SetProdMemberHandler::class)->handle(new SetProdMemberCommand($prodId, $authorId, ['code']));
        $this->importOrigins()->saveOrigin($authorId, ImportOrigin::Zxdb, '1001', EntityType::Author);

        $state = $this->container->get(AuthorStateService::class)->get($authorId);

        $author = $this->element($authorId, authorElement::class);
        self::assertEquals($this->container->get(AuthorFieldsReader::class)->read($author), $state->fields);
        self::assertSame($authorId, $state->id);
        self::assertSame($this->modificationTime($authorId), $state->modifiedAt);
        self::assertSame([$aliasId], $state->aliasIds);
        self::assertEquals(
            [new AuthorshipDto(elementId: $groupId, authorId: $authorId, roles: ['coder'], startDate: $this->date('15.03.1995'), endDate: 0)],
            $state->memberships,
        );
        self::assertSame(1, $state->counters->prods);
        self::assertEquals([new ImportOriginDto(ImportOrigin::Zxdb, '1001')], $state->importOrigins);
        self::assertInstanceOf(AuthorStateDto::class, $state);
    }

    public function testAuthorAliasStateHoldsFieldsMembershipsAndCounts(): void
    {
        $this->signInAsEditor();
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;
        $aliasId = $this->container->get(CreateAuthorAliasHandler::class)->handle(new CreateAuthorAliasCommand(new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: $authorId)))->id;
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild')))->id;
        $this->container->get(SetGroupMemberHandler::class)->handle(new SetGroupMemberCommand($groupId, $aliasId, ['graphician'], null, null));

        $this->importOrigins()->saveOrigin($aliasId, ImportOrigin::Zxdb, '1002', EntityType::Author);

        $state = $this->container->get(AuthorAliasStateService::class)->get($aliasId);

        $alias = $this->element($aliasId, authorAliasElement::class);
        self::assertInstanceOf(AuthorAliasStateDto::class, $state);
        self::assertSame($aliasId, $state->id);
        self::assertSame($this->modificationTime($aliasId), $state->modifiedAt);
        self::assertEquals($this->container->get(AuthorAliasFieldsReader::class)->read($alias), $state->fields);
        self::assertEquals(
            [new AuthorshipDto(elementId: $groupId, authorId: $aliasId, roles: ['graphician'], startDate: 0, endDate: 0)],
            $state->memberships,
        );
        self::assertSame(0, $state->counters->prods);
        self::assertEquals([new ImportOriginDto(ImportOrigin::Zxdb, '1002')], $state->importOrigins);
    }

    public function testGroupStateHoldsFieldsMembersParentsAliasesAndCounts(): void
    {
        $this->signInAsEditor();
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Division')))->id;
        $parentId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild', subGroupIds: [$groupId])))->id;
        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Wandering Division', groupId: $groupId)))->id;
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;
        $this->container->get(SetGroupMemberHandler::class)->handle(new SetGroupMemberCommand($groupId, $authorId, ['coder'], null, $this->date('01.06.1999')));
        $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Demo', categoryIds: [self::CATEGORY_DEMO], groupIds: [$groupId])));

        $this->importOrigins()->saveOrigin($groupId, ImportOrigin::Zxaaa, 'division', EntityType::Group);

        $state = $this->container->get(GroupStateService::class)->get($groupId);

        $group = $this->element($groupId, groupElement::class);
        self::assertInstanceOf(GroupStateDto::class, $state);
        self::assertSame($groupId, $state->id);
        self::assertSame($this->modificationTime($groupId), $state->modifiedAt);
        self::assertEquals($this->container->get(GroupFieldsReader::class)->read($group), $state->fields);
        self::assertEquals(
            [new AuthorshipDto(elementId: $groupId, authorId: $authorId, roles: ['coder'], startDate: 0, endDate: $this->date('01.06.1999'))],
            $state->members,
        );
        self::assertSame([$parentId], $state->parentGroupIds);
        self::assertSame([$aliasId], $state->aliasIds);
        self::assertSame(1, $state->counters->prods);
        self::assertEquals([new ImportOriginDto(ImportOrigin::Zxaaa, 'division')], $state->importOrigins);
    }

    public function testGroupAliasStateHoldsFieldsMembersAndCounts(): void
    {
        $this->signInAsEditor();
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild')))->id;
        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Wandering Guild', groupId: $groupId)))->id;

        $state = $this->container->get(GroupAliasStateService::class)->get($aliasId);

        $alias = $this->element($aliasId, groupAliasElement::class);
        self::assertInstanceOf(GroupAliasStateDto::class, $state);
        self::assertSame($aliasId, $state->id);
        self::assertSame($this->modificationTime($aliasId), $state->modifiedAt);
        self::assertEquals($this->container->get(GroupAliasFieldsReader::class)->read($alias), $state->fields);
        self::assertSame([], $state->members);
        self::assertSame(0, $state->counters->prods);
        self::assertSame([], $state->importOrigins);
    }

    public function testTheStateOfAnElementOfAnotherTypeIsNotFound(): void
    {
        $this->signInAsEditor();
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild')))->id;
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;

        $readers = [
            ProdDataException::class => fn() => $this->container->get(ProdStateService::class)->get($groupId),
            ReleaseDataException::class => fn() => $this->container->get(ReleaseStateService::class)->get($groupId),
            AuthorDataException::class => fn() => $this->container->get(AuthorStateService::class)->get($groupId),
            AuthorAliasDataException::class => fn() => $this->container->get(AuthorAliasStateService::class)->get($groupId),
            GroupDataException::class => fn() => $this->container->get(GroupStateService::class)->get($authorId),
            GroupAliasDataException::class => fn() => $this->container->get(GroupAliasStateService::class)->get($authorId),
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

    /**
     * @template T of \structureElement
     * @param class-string<T> $class
     * @return T
     */
    private function element(int $elementId, string $class): \structureElement
    {
        $element = $this->structureManager->getElementById($elementId);
        self::assertInstanceOf($class, $element);

        return $element;
    }

    private function importOrigins(): ImportOriginsDbal
    {
        return $this->container->get(ImportOriginsDbal::class);
    }
}
