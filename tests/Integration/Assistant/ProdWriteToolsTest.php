<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/../Entities/EntityWriteTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\FieldChangeDto;
use ZxArt\Assistant\Tools\ToolRegistry;
use ZxArt\Assistant\Tools\ToolRequestBuilder;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Services\GroupDataService;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Dto\ProdStateDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Prods\Services\ProdStateService;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Services\ReleaseDataService;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;
use ZxArt\Tests\Integration\Entities\EntityWriteTestCase;

/**
 * The production write tools as a plan runs them: resolved by name, built from
 * JSON, previewed, then executed.
 */
#[RunTestsInSeparateProcesses]
final class ProdWriteToolsTest extends EntityWriteTestCase
{
    public function testCreateProdPreviewsTheGivenFieldsAndCreates(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->container->get(GroupDataService::class)->create(new GroupFieldsDto(title: 'Goldwrite Guild'))->id;
        $arguments = '{"fields": {"title": "Goldwrite Demo", "year": 1999, "groupIds": [' . $groupId . ']}}';

        self::assertEquals(
            new ChangePreviewDto(
                kind: ChangeKind::Create,
                target: new ChangeTargetDto(EntityType::Prod, null, 'Goldwrite Demo'),
                changes: [
                    new FieldChangeDto('title', null, 'Goldwrite Demo'),
                    new FieldChangeDto('year', null, 1999),
                    new FieldChangeDto('groupIds', null, [new EntityMentionDto(EntityType::Group, $groupId)]),
                ],
                consequences: [],
                modifiedAt: null,
            ),
            $this->preview('create_prod', $arguments),
        );
        $result = $this->execute('create_prod', $arguments);

        self::assertInstanceOf(ProdDataResultDto::class, $result);
        self::assertSame('Goldwrite Demo', $this->state($result->id)->fields->title);
    }

    public function testCreateProdWithoutATitleIsRefusedAtThePreview(): void
    {
        $this->signInAsRegisteredUser();

        $this->assertRefused(400, ProdDataException::class, fn() => $this->preview('create_prod', '{"fields": {"year": 1999}}'));
    }

    public function testUpdateProdPreviewsWhatChangesAndUpdates(): void
    {
        $this->signInAsEditor();
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldwrite Demo', year: 1999))->id;
        $arguments = '{"id": ' . $prodId . ', "fields": {"title": "Goldwrite Demo", "year": 2001, "legalStatus": "allowed"}}';

        self::assertEquals(
            new ChangePreviewDto(
                kind: ChangeKind::Update,
                target: new ChangeTargetDto(EntityType::Prod, $prodId, 'Goldwrite Demo'),
                changes: [
                    new FieldChangeDto('year', 1999, 2001),
                    new FieldChangeDto('legalStatus', 'unknown', 'allowed'),
                ],
                consequences: [],
                modifiedAt: $this->modificationTime($prodId),
            ),
            $this->preview('update_prod', $arguments),
        );
        $this->execute('update_prod', $arguments);

        self::assertSame(2001, $this->state($prodId)->fields->year);
    }

    public function testUpdateProdIsRefusedAtThePreviewWithoutThePrivilege(): void
    {
        $this->signInAsRegisteredUser();
        $prodId = (int)$this->db->table('module_zxprod')->min('id');

        $this->assertRefused(403, ProdDataException::class, fn() => $this->preview('update_prod', '{"id": ' . $prodId . ', "fields": {"year": 2001}}'));
    }

    public function testDeleteProdPreviewsTheReleasesItTakesAlong(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicDelete']]);
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldwrite Demo'))->id;
        $this->createRelease($prodId);
        $this->createRelease($prodId);

        $preview = $this->preview('delete_prod', '{"id": ' . $prodId . '}');

        self::assertSame(ChangeKind::Delete, $preview->kind);
        self::assertSame(['2 releases are deleted with it'], $preview->consequences);
        $this->execute('delete_prod', '{"id": ' . $prodId . '}');
        self::assertFalse($this->elementExists($prodId));
    }

    public function testJoinProdsPreviewsWhatMovesAndJoins(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'join']]);
        $mainId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldwrite Demo'))->id;
        $joinedId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldwrite Intro'))->id;
        $this->createRelease($joinedId);
        $arguments = '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . '}';

        self::assertEquals(
            new ChangePreviewDto(
                kind: ChangeKind::Join,
                target: new ChangeTargetDto(EntityType::Prod, $mainId, 'Goldwrite Demo'),
                changes: [],
                consequences: [
                    "[[prod:$joinedId]] is deleted",
                    "1 release moves to [[prod:$mainId]]",
                    "The authors, groups, publishers, categories and the fields [[prod:$mainId]] lacks move from [[prod:$joinedId]]",
                ],
                modifiedAt: $this->modificationTime($mainId),
            ),
            $this->preview('join_prods', $arguments),
        );
        $this->execute('join_prods', $arguments);

        self::assertFalse($this->elementExists($joinedId));
    }

    public function testJoinOfReleasesOnlyPreviewsNothingElseMoving(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'join']]);
        $mainId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldwrite Demo'))->id;
        $joinedId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldwrite Intro'))->id;

        self::assertSame(
            ["[[prod:$joinedId]] is deleted"],
            $this->preview('join_prods', '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . ', "releasesOnly": true}')->consequences,
        );
    }

    public function testSetProdAuthorPreviewsANewAuthorThenNewRoles(): void
    {
        $this->signInAsEditor();
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldwrite Demo'))->id;
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldwrite Coder'))->id;
        $author = new EntityMentionDto(EntityType::Author, $authorId);

        self::assertEquals(
            [new FieldChangeDto('author', null, $author), new FieldChangeDto('roles', null, ['code'])],
            $this->preview('set_prod_author', '{"prodId": ' . $prodId . ', "authorId": ' . $authorId . ', "roles": ["code"]}')->changes,
        );
        $this->execute('set_prod_author', '{"prodId": ' . $prodId . ', "authorId": ' . $authorId . ', "roles": ["code"]}');

        $preview = $this->preview('set_prod_author', '{"prodId": ' . $prodId . ', "authorId": ' . $authorId . ', "roles": ["code", "music"]}');
        self::assertSame(ChangeKind::Link, $preview->kind);
        self::assertEquals([new FieldChangeDto('roles', ['code'], ['code', 'music'])], $preview->changes);
    }

    public function testSetProdAuthorOfAnUnknownRoleIsRefusedAtThePreview(): void
    {
        $this->signInAsEditor();
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldwrite Demo'))->id;
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldwrite Coder'))->id;

        $this->assertRefused(400, ProdDataException::class, fn() => $this->preview(
            'set_prod_author',
            '{"prodId": ' . $prodId . ', "authorId": ' . $authorId . ', "roles": ["juggling"]}',
        ));
    }

    public function testRemoveProdAuthorPreviewsTheCreditItRemovesAndRemoves(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive'], ['zxProd', 'deleteAuthor']]);
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldwrite Demo'))->id;
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldwrite Coder'))->id;
        $this->prodDataService()->setMember($prodId, $authorId, ['code']);
        $arguments = '{"prodId": ' . $prodId . ', "authorId": ' . $authorId . '}';

        $preview = $this->preview('remove_prod_author', $arguments);

        self::assertSame(ChangeKind::Unlink, $preview->kind);
        self::assertEquals(
            [new FieldChangeDto('author', new EntityMentionDto(EntityType::Author, $authorId), null), new FieldChangeDto('roles', ['code'], null)],
            $preview->changes,
        );
        $this->execute('remove_prod_author', $arguments);
        self::assertSame([], $this->state($prodId)->authors);
    }

    public function testRemovingAnAuthorWhoIsNotCreditedIsRefusedAtThePreview(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'deleteAuthor']]);
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldwrite Demo'))->id;
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldwrite Coder'))->id;

        $this->assertRefused(404, ProdDataException::class, fn() => $this->preview('remove_prod_author', '{"prodId": ' . $prodId . ', "authorId": ' . $authorId . '}'));
    }

    private function preview(string $toolName, string $arguments): ChangePreviewDto
    {
        $tool = $this->writeTool($toolName);

        return $tool->preview($this->container->get(ToolRequestBuilder::class)->build($tool->requestClass(), $arguments));
    }

    private function execute(string $toolName, string $arguments): object
    {
        $tool = $this->writeTool($toolName);

        return $tool->execute($this->container->get(ToolRequestBuilder::class)->build($tool->requestClass(), $arguments));
    }

    private function writeTool(string $toolName): WriteTool
    {
        $tool = $this->container->get(ToolRegistry::class)->get($toolName);
        self::assertInstanceOf(WriteTool::class, $tool);

        return $tool;
    }

    private function state(int $prodId): ProdStateDto
    {
        return $this->container->get(ProdStateService::class)->get($prodId);
    }

    private function createRelease(int $prodId): void
    {
        $this->container->get(ReleaseDataService::class)->create(new ReleaseFieldsDto(title: 'Goldwrite Tape', prodId: $prodId));
    }

    private function prodDataService(): ProdDataService
    {
        return $this->container->get(ProdDataService::class);
    }

    private function signInAsRegisteredUser(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
    }

    private function signInAsEditor(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive']]);
    }
}
