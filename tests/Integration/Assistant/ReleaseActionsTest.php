<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/WriteToolTestCase.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Assistant\Releases\ReleaseDto;
use ZxArt\Assistant\Releases\ReleaseDtos;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\ReleaseFileKind;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\UseCases\CreateReleaseCommand;
use ZxArt\Releases\UseCases\CreateReleaseHandler;
use ZxArt\Releases\UseCases\SetReleaseMemberCommand;
use ZxArt\Releases\UseCases\SetReleaseMemberHandler;
use ZxArt\Shared\Dto\CreditDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefDto;

/**
 * The assistant's release actions: the fields they offer the model, the state
 * of their target a plan shows, and what they write. The file actions are in
 * `FileWriteToolsTest`.
 */
#[RunTestsInSeparateProcesses]
final class ReleaseActionsTest extends WriteToolTestCase
{
    /**
     * @return array<string, array{string, list<string>, list<string>}>
     */
    public static function schemas(): array
    {
        $releaseFields = ['title', 'year', 'version', 'description', 'releaseType', 'releaseFormats', 'languages', 'hardware', 'publisherIds', 'prodId'];

        return [
            'get_release' => ['get_release', ['id'], ['id']],
            'create_release' => ['create_release', $releaseFields, ['title', 'prodId']],
            'update_release' => ['update_release', ['id', ...$releaseFields], ['id']],
            'delete_release' => ['delete_release', ['id'], ['id']],
            'set_release_author' => ['set_release_author', ['releaseId', 'authorId', 'roles'], ['releaseId', 'authorId', 'roles']],
            'remove_release_author' => ['remove_release_author', ['releaseId', 'authorId'], ['releaseId', 'authorId']],
            'add_release_files' => ['add_release_files', ['releaseId', 'kind', 'urls'], ['releaseId', 'kind', 'urls']],
            'delete_release_file' => ['delete_release_file', ['releaseId', 'fileId'], ['releaseId', 'fileId']],
            'set_release_file' => ['set_release_file', ['releaseId', 'url', 'fileName'], ['releaseId', 'url']],
            'set_release_itch_io_file' => ['set_release_itch_io_file', ['releaseId', 'url', 'uploadId'], ['releaseId', 'url', 'uploadId']],
        ];
    }

    /**
     * @param list<string> $properties
     * @param list<string> $required
     */
    #[DataProvider('schemas')]
    public function testAnActionOffersTheModelOnlyItsOwnFields(string $actionName, array $properties, array $required): void
    {
        $schema = $this->actionSchema($actionName);

        self::assertSame('object', $schema['type']);
        self::assertSame($properties, array_keys($schema['properties']));
        self::assertSame($required, $schema['required']);
        self::assertFalse($schema['additionalProperties']);
    }

    public function testTheClosedListsAreTheReleaseTypesAndTheFileLists(): void
    {
        self::assertSame(
            [...array_map(static fn(ReleaseTypes $type): string => $type->value, ReleaseTypes::cases()), null],
            $this->actionSchema('update_release')['properties']['releaseType']['enum'],
        );
        self::assertSame(
            array_map(static fn(ReleaseFileKind $kind): string => $kind->value, ReleaseFileKind::cases()),
            $this->actionSchema('add_release_files')['properties']['kind']['enum'],
        );
    }

    /**
     * @return array<string, array{string, ChangeKind}>
     */
    public static function kinds(): array
    {
        return [
            'create_release' => ['create_release', ChangeKind::Create],
            'update_release' => ['update_release', ChangeKind::Update],
            'delete_release' => ['delete_release', ChangeKind::Delete],
            'set_release_author' => ['set_release_author', ChangeKind::Link],
            'remove_release_author' => ['remove_release_author', ChangeKind::Unlink],
            'add_release_files' => ['add_release_files', ChangeKind::AttachFile],
            'delete_release_file' => ['delete_release_file', ChangeKind::DeleteFile],
            'set_release_file' => ['set_release_file', ChangeKind::AttachFile],
            'set_release_itch_io_file' => ['set_release_itch_io_file', ChangeKind::AttachFile],
        ];
    }

    #[DataProvider('kinds')]
    public function testAWriteActionNamesItsKindAndThatItChangesARelease(string $actionName, ChangeKind $kind): void
    {
        $action = $this->writeAction($actionName);

        self::assertSame($kind, $action->kind());
        self::assertSame(EntityType::Release, $action->entityType());
    }

    public function testGetReleaseAnswersTheFieldsWhatTheyNameTheCreditsAndWhatItTakesFromItsProduction(): void
    {
        $this->signInAsEditor();
        $prodId = $this->createProd(new ProdFieldsDto(title: 'Goldwrite Demo', languages: ['en'], hardware: ['zx128']));
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldwrite Guild')))->id;
        $coderId = $this->createAuthor();
        $releaseId = $this->createRelease(new ReleaseFieldsDto(
            title: 'Goldwrite Tape',
            year: 2001,
            version: '1.1',
            description: 'The tape.',
            releaseType: ReleaseTypes::original,
            releaseFormats: ['tap'],
            publisherIds: [$groupId],
            prodId: $prodId,
        ));
        $this->container->get(SetReleaseMemberHandler::class)->handle(new SetReleaseMemberCommand($releaseId, $coderId, ['release']));

        self::assertEquals(
            new ReleaseDto(
                id: $releaseId,
                title: 'Goldwrite Tape',
                year: 2001,
                version: '1.1',
                description: 'The tape.',
                releaseType: 'original',
                releaseFormats: ['tap'],
                languages: [],
                hardware: [],
                publisherIds: [$groupId],
                prodId: $prodId,
                refs: [new EntityRefDto(EntityType::Group, $groupId, 'Goldwrite Guild'), new EntityRefDto(EntityType::Prod, $prodId, 'Goldwrite Demo')],
                authors: [new CreditDto(new EntityRefDto(EntityType::Author, $coderId, 'Goldwrite Coder'), ['release'])],
                files: [],
                fileName: '',
                fileSize: null,
                prodHardware: ['zx128'],
                prodLanguages: ['en'],
            ),
            $this->actionRead('get_release', '{"id": ' . $releaseId . '}'),
        );
    }

    public function testGetReleaseOfNoReleaseIsRefused(): void
    {
        $this->signInAsEditor();

        $this->assertRefused(404, ReleaseDataException::class, fn() => $this->actionRead('get_release', '{"id": 999999999}'));
    }

    public function testCreateReleaseHasNoStateAndCreatesUnderItsProduction(): void
    {
        $this->signInAsEditor();
        $prodId = $this->createProd(new ProdFieldsDto(title: 'Goldwrite Demo'));
        $arguments = '{"title": "Goldwrite Tape", "prodId": ' . $prodId . ', "releaseType": "rerelease", "releaseFormats": ["tzx"]}';

        self::assertNull($this->actionState('create_release', $arguments));
        $result = $this->actionWrite('create_release', $arguments);

        self::assertInstanceOf(ReleaseDataResultDto::class, $result);
        $release = $this->release($result->id);
        self::assertSame('Goldwrite Tape', $release->title);
        self::assertSame($prodId, $release->prodId);
        self::assertSame('rerelease', $release->releaseType);
        self::assertSame(['tzx'], $release->releaseFormats);
    }

    public function testCreateReleaseWithoutAProductionIsRefused(): void
    {
        $this->signInAsEditor();
        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage('prodId');

        $this->actionWrite('create_release', '{"title": "Goldwrite Tape"}');
    }

    public function testUpdateReleaseStateIsTheReleaseAndItChangesTheGivenFieldsOnly(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease(new ReleaseFieldsDto(title: 'Goldwrite Tape', version: '1.0', prodId: $this->createProd(new ProdFieldsDto(title: 'Goldwrite Demo'))));
        $arguments = '{"id": ' . $releaseId . ', "year": 2001, "releaseType": "crack"}';

        self::assertEquals($this->release($releaseId), $this->actionState('update_release', $arguments));
        $this->actionWrite('update_release', $arguments);

        $release = $this->release($releaseId);
        self::assertSame(2001, $release->year);
        self::assertSame('crack', $release->releaseType);
        self::assertSame('1.0', $release->version);
    }

    public function testUpdateReleaseWithAReleaseTypeItDoesNotKnowIsRefused(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease(new ReleaseFieldsDto(title: 'Goldwrite Tape', prodId: $this->createProd(new ProdFieldsDto(title: 'Goldwrite Demo'))));
        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage('releaseType');

        $this->actionWrite('update_release', '{"id": ' . $releaseId . ', "releaseType": "bootleg"}');
    }

    public function testUpdateReleaseWithoutThePrivilegeIsRefusedWhenItRuns(): void
    {
        $this->signInAsRegisteredUser();
        $releaseId = (int)$this->db->table('module_zxrelease')->min('id');

        $this->assertRefused(403, ReleaseDataException::class, fn() => $this->actionWrite('update_release', '{"id": ' . $releaseId . ', "year": 2001}'));
    }

    public function testDeleteReleaseStateIsTheReleaseAndItDeletes(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease(new ReleaseFieldsDto(title: 'Goldwrite Tape', prodId: $this->createProd(new ProdFieldsDto(title: 'Goldwrite Demo'))));

        self::assertEquals($this->release($releaseId), $this->actionState('delete_release', '{"id": ' . $releaseId . '}'));
        $this->actionWrite('delete_release', '{"id": ' . $releaseId . '}');

        self::assertFalse($this->elementExists($releaseId));
    }

    public function testSetReleaseAuthorStateIsTheReleaseAndItCreditsThenRemoveDropsTheCredit(): void
    {
        $this->signInAsEditor();
        $releaseId = $this->createRelease(new ReleaseFieldsDto(title: 'Goldwrite Tape', prodId: $this->createProd(new ProdFieldsDto(title: 'Goldwrite Demo'))));
        $authorId = $this->createAuthor();
        $arguments = '{"releaseId": ' . $releaseId . ', "authorId": ' . $authorId . ', "roles": ["release"]}';

        self::assertEquals($this->release($releaseId), $this->actionState('set_release_author', $arguments));
        $this->actionWrite('set_release_author', $arguments);

        self::assertEquals(
            [new CreditDto(new EntityRefDto(EntityType::Author, $authorId, 'Goldwrite Coder'), ['release'])],
            $this->release($releaseId)->authors,
        );
        $this->actionWrite('remove_release_author', '{"releaseId": ' . $releaseId . ', "authorId": ' . $authorId . '}');
        self::assertSame([], $this->release($releaseId)->authors);
    }

    private function createProd(ProdFieldsDto $fields): int
    {
        return $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand($fields))->id;
    }

    private function createRelease(ReleaseFieldsDto $fields): int
    {
        return $this->container->get(CreateReleaseHandler::class)->handle(new CreateReleaseCommand($fields))->id;
    }

    private function createAuthor(): int
    {
        return $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldwrite Coder')))->id;
    }

    private function release(int $releaseId): ReleaseDto
    {
        return $this->container->get(ReleaseDtos::class)->release($releaseId);
    }
}
