<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/WriteToolTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\FieldChangeDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Services\ReleaseStateService;
use ZxArt\Releases\UseCases\CreateReleaseCommand;
use ZxArt\Releases\UseCases\CreateReleaseHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * The release write tools.
 */
#[RunTestsInSeparateProcesses]
final class ReleaseWriteToolsTest extends WriteToolTestCase
{
    public function testCreateReleasePreviewsItsProductionAndCreates(): void
    {
        $this->signInAsEditor();
        $prodId = $this->createProd();
        $arguments = '{"fields": {"title": "Goldwrite Tape", "prodId": ' . $prodId . '}}';

        self::assertEquals(
            new ChangePreviewDto(
                kind: ChangeKind::Create,
                target: new ChangeTargetDto(EntityType::Release, null, 'Goldwrite Tape'),
                changes: [
                    new FieldChangeDto('title', null, 'Goldwrite Tape'),
                    new FieldChangeDto('prodId', null, new EntityMentionDto(EntityType::Prod, $prodId)),
                ],
                consequences: [],
                modifiedAt: null,
            ),
            $this->preview('create_release', $arguments),
        );
        $result = $this->execute('create_release', $arguments);

        self::assertInstanceOf(ReleaseDataResultDto::class, $result);
        self::assertSame($prodId, $this->container->get(ReleaseStateService::class)->get($result->id)->fields->prodId);
    }

    public function testCreateReleaseWithoutAProductionIsRefusedAtThePreview(): void
    {
        $this->signInAsEditor();

        $this->assertRefused(400, ReleaseDataException::class, fn() => $this->preview('create_release', '{"fields": {"title": "Goldwrite Tape"}}'));
    }

    public function testUpdateReleasePreviewsWhatChangesAndUpdates(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease();
        $arguments = '{"id": ' . $releaseId . ', "fields": {"year": 1995, "releaseType": "crack"}}';

        $preview = $this->preview('update_release', $arguments);

        self::assertSame(ChangeKind::Update, $preview->kind);
        self::assertEquals(new ChangeTargetDto(EntityType::Release, $releaseId, 'Goldwrite Tape'), $preview->target);
        self::assertEquals([new FieldChangeDto('year', 0, 1995), new FieldChangeDto('releaseType', 'unknown', 'crack')], $preview->changes);
        self::assertSame($this->modificationTime($releaseId), $preview->modifiedAt);
        $this->execute('update_release', $arguments);
        self::assertSame(1995, $this->container->get(ReleaseStateService::class)->get($releaseId)->fields->year);
    }

    public function testUpdateReleaseIsRefusedAtThePreviewWithoutThePrivilege(): void
    {
        $this->signInAsRegisteredUser();
        $releaseId = (int)$this->db->table('module_zxrelease')->min('id');

        $this->assertRefused(403, ReleaseDataException::class, fn() => $this->preview('update_release', '{"id": ' . $releaseId . ', "fields": {"year": 1995}}'));
    }

    public function testDeleteReleasePreviewsAndDeletes(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease();

        $preview = $this->preview('delete_release', '{"id": ' . $releaseId . '}');

        self::assertSame(ChangeKind::Delete, $preview->kind);
        self::assertSame([], $preview->consequences);
        $this->execute('delete_release', '{"id": ' . $releaseId . '}');
        self::assertFalse($this->elementExists($releaseId));
    }

    public function testSetAndRemoveReleaseAuthorPreviewTheCredit(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease();
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldwrite Cracker')))->id;
        $author = new EntityMentionDto(EntityType::Author, $authorId);
        $arguments = '{"releaseId": ' . $releaseId . ', "authorId": ' . $authorId . ', "roles": ["adaptation"]}';

        self::assertEquals(
            [new FieldChangeDto('author', null, $author), new FieldChangeDto('roles', null, ['adaptation'])],
            $this->preview('set_release_author', $arguments)->changes,
        );
        $this->execute('set_release_author', $arguments);
        $removal = '{"releaseId": ' . $releaseId . ', "authorId": ' . $authorId . '}';

        self::assertEquals(
            [new FieldChangeDto('author', $author, null), new FieldChangeDto('roles', ['adaptation'], null)],
            $this->preview('remove_release_author', $removal)->changes,
        );
        $this->execute('remove_release_author', $removal);
        self::assertSame([], $this->container->get(ReleaseStateService::class)->get($releaseId)->authors);
    }

    private function createProd(): int
    {
        return $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldwrite Demo')))->id;
    }

    private function createRelease(): int
    {
        return $this->container->get(CreateReleaseHandler::class)->handle(new CreateReleaseCommand(new ReleaseFieldsDto(title: 'Goldwrite Tape', prodId: $this->createProd())))->id;
    }
}
