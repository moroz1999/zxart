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
use ZxArt\Authors\Services\AuthorAliasDataService;
use ZxArt\Authors\Services\AuthorAliasFieldsReader;
use ZxArt\Authors\Services\AuthorAliasStateService;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Authors\Services\AuthorFieldsReader;
use ZxArt\Authors\Services\AuthorStateService;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupAliasStateDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Dto\GroupStateDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Services\GroupAliasDataService;
use ZxArt\Groups\Services\GroupAliasFieldsReader;
use ZxArt\Groups\Services\GroupAliasStateService;
use ZxArt\Groups\Services\GroupDataService;
use ZxArt\Groups\Services\GroupFieldsReader;
use ZxArt\Groups\Services\GroupStateService;
use ZxArt\Import\Dbal\ImportOriginsDbal;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Import\ImportOrigin;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Dto\ProdStateDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Prods\Services\ProdFieldsReader;
use ZxArt\Prods\Services\ProdStateService;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Dto\ReleaseStateDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Services\ReleaseDataService;
use ZxArt\Releases\Services\ReleaseFieldsReader;
use ZxArt\Releases\Services\ReleaseStateService;
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
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo', year: 1999))->id;
        $authorId = $this->authors()->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $this->prods()->setMember($prodId, $authorId, ['code']);
        $releaseId = $this->releases()->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $prodId))->id;
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
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $releaseId = $this->releases()->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $prodId))->id;
        $authorId = $this->authors()->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $this->releases()->setMember($releaseId, $authorId, ['release']);
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
        $authorId = $this->authors()->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $aliasId = $this->authorAliases()->create(new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: $authorId))->id;
        $groupId = $this->groupService()->create(new GroupFieldsDto(title: 'Goldmaster Guild'))->id;
        $this->groupService()->setMember($groupId, $authorId, ['coder'], $this->date('15.03.1995'), null);
        $prodId = $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo', categoryIds: [self::CATEGORY_DEMO]))->id;
        $this->prods()->setMember($prodId, $authorId, ['code']);
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
        $authorId = $this->authors()->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $aliasId = $this->authorAliases()->create(new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: $authorId))->id;
        $groupId = $this->groupService()->create(new GroupFieldsDto(title: 'Goldmaster Guild'))->id;
        $this->groupService()->setMember($groupId, $aliasId, ['graphician'], null, null);

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
        $groupId = $this->groupService()->create(new GroupFieldsDto(title: 'Goldmaster Division'))->id;
        $parentId = $this->groupService()->create(new GroupFieldsDto(title: 'Goldmaster Guild', subGroupIds: [$groupId]))->id;
        $aliasId = $this->groupAliases()->create(new GroupAliasFieldsDto(title: 'Wandering Division', groupId: $groupId))->id;
        $authorId = $this->authors()->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $this->groupService()->setMember($groupId, $authorId, ['coder'], null, $this->date('01.06.1999'));
        $this->prods()->create(new ProdFieldsDto(title: 'Goldmaster Demo', categoryIds: [self::CATEGORY_DEMO], groupIds: [$groupId]));

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
        $groupId = $this->groupService()->create(new GroupFieldsDto(title: 'Goldmaster Guild'))->id;
        $aliasId = $this->groupAliases()->create(new GroupAliasFieldsDto(title: 'Wandering Guild', groupId: $groupId))->id;

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
        $groupId = $this->groupService()->create(new GroupFieldsDto(title: 'Goldmaster Guild'))->id;
        $authorId = $this->authors()->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;

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

    private function prods(): ProdDataService
    {
        return $this->container->get(ProdDataService::class);
    }

    private function releases(): ReleaseDataService
    {
        return $this->container->get(ReleaseDataService::class);
    }

    private function authors(): AuthorDataService
    {
        return $this->container->get(AuthorDataService::class);
    }

    private function authorAliases(): AuthorAliasDataService
    {
        return $this->container->get(AuthorAliasDataService::class);
    }

    private function groupService(): GroupDataService
    {
        return $this->container->get(GroupDataService::class);
    }

    private function groupAliases(): GroupAliasDataService
    {
        return $this->container->get(GroupAliasDataService::class);
    }
}
