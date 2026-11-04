<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/WriteToolTestCase.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Assistant\Prods\ProdDto;
use ZxArt\Assistant\Prods\ProdDtos;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\UseCases\CreateGroupAliasCommand;
use ZxArt\Groups\UseCases\CreateGroupAliasHandler;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\ProdFileKind;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Prods\UseCases\SetProdMemberCommand;
use ZxArt\Prods\UseCases\SetProdMemberHandler;
use ZxArt\Releases\Dbal\ReleasesDbal;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Services\FoundReleasesReader;
use ZxArt\Releases\UseCases\CreateReleaseCommand;
use ZxArt\Releases\UseCases\CreateReleaseHandler;
use ZxArt\Shared\Dto\CreditDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefDto;

/**
 * The assistant's production actions: the fields they offer the model, the
 * state of their target a plan shows, and what they write. The file actions
 * are in `FileWriteToolsTest`.
 */
#[RunTestsInSeparateProcesses]
final class ProdActionsTest extends WriteToolTestCase
{
    private const int CATEGORY_DEMO = 92159;

    /**
     * @return array<string, array{string, list<string>, list<string>}>
     */
    public static function schemas(): array
    {
        $prodFields = [
            'title', 'altTitle', 'year', 'description', 'instructions', 'legalStatus', 'externalLink', 'youtubeId',
            'partyId', 'partyPlace', 'compo', 'languages', 'hardware', 'categoryIds', 'groupIds', 'publisherIds', 'tags',
            'compilationItemIds', 'seriesProdIds',
        ];

        return [
            'get_prod' => ['get_prod', ['id'], ['id']],
            'create_prod' => ['create_prod', $prodFields, ['title']],
            'update_prod' => ['update_prod', ['id', ...$prodFields], ['id']],
            'delete_prod' => ['delete_prod', ['id'], ['id']],
            'join_prods' => ['join_prods', ['mainId', 'joinedId', 'releasesOnly'], ['mainId', 'joinedId']],
            'set_prod_author' => ['set_prod_author', ['prodId', 'authorId', 'roles'], ['prodId', 'authorId', 'roles']],
            'remove_prod_author' => ['remove_prod_author', ['prodId', 'authorId'], ['prodId', 'authorId']],
            'add_prod_files' => ['add_prod_files', ['prodId', 'kind', 'urls'], ['prodId', 'kind', 'urls']],
            'delete_prod_file' => ['delete_prod_file', ['prodId', 'fileId'], ['prodId', 'fileId']],
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

    public function testTheClosedListsAreTheLegalStatusesAndTheFileLists(): void
    {
        self::assertSame(
            [...array_map(static fn(LegalStatus $status): string => $status->value, LegalStatus::cases()), null],
            $this->actionSchema('update_prod')['properties']['legalStatus']['enum'],
        );
        self::assertSame(
            array_map(static fn(ProdFileKind $kind): string => $kind->value, ProdFileKind::cases()),
            $this->actionSchema('add_prod_files')['properties']['kind']['enum'],
        );
    }

    /**
     * @return array<string, array{string, ChangeKind}>
     */
    public static function kinds(): array
    {
        return [
            'create_prod' => ['create_prod', ChangeKind::Create],
            'update_prod' => ['update_prod', ChangeKind::Update],
            'delete_prod' => ['delete_prod', ChangeKind::Delete],
            'join_prods' => ['join_prods', ChangeKind::Join],
            'set_prod_author' => ['set_prod_author', ChangeKind::Link],
            'remove_prod_author' => ['remove_prod_author', ChangeKind::Unlink],
            'add_prod_files' => ['add_prod_files', ChangeKind::AttachFile],
            'delete_prod_file' => ['delete_prod_file', ChangeKind::DeleteFile],
        ];
    }

    #[DataProvider('kinds')]
    public function testAWriteActionNamesItsKindAndThatItChangesAProduction(string $actionName, ChangeKind $kind): void
    {
        $action = $this->writeAction($actionName);

        self::assertSame($kind, $action->kind());
        self::assertSame(EntityType::Prod, $action->entityType());
    }

    public function testGetProdAnswersTheFieldsWhatTheyNameTheCreditsReleasesAndWhatIncludesIt(): void
    {
        $this->signInAsEditor();
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldwrite Guild')))->id;
        $crewId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Goldwrite Crew', groupId: $groupId)))->id;
        $labelId = $this->createAuthor('Goldwrite Label');
        $coderId = $this->createAuthor('Goldwrite Coder');
        $demoId = $this->createProd(new ProdFieldsDto(
            title: 'Goldwrite Demo',
            altTitle: 'Gold Demo',
            year: 1999,
            description: 'A demo of gold.',
            legalStatus: LegalStatus::allowed,
            compo: 'demo',
            languages: ['en'],
            hardware: ['zx48'],
            categoryIds: [self::CATEGORY_DEMO],
            groupIds: [$crewId],
            publisherIds: [$labelId],
        ));
        $sagaId = $this->createProd(new ProdFieldsDto(title: 'Goldwrite Saga', categoryIds: [self::CATEGORY_DEMO], seriesProdIds: [$demoId]));
        $packId = $this->createProd(new ProdFieldsDto(title: 'Goldwrite Pack', categoryIds: [self::CATEGORY_DEMO], compilationItemIds: [$demoId]));
        $this->container->get(SetProdMemberHandler::class)->handle(new SetProdMemberCommand($demoId, $coderId, ['code']));
        $tapeId = $this->createRelease($demoId);

        self::assertEquals(
            new ProdDto(
                id: $demoId,
                title: 'Goldwrite Demo',
                altTitle: 'Gold Demo',
                year: 1999,
                description: 'A demo of gold.',
                instructions: '',
                legalStatus: 'allowed',
                externalLink: '',
                youtubeId: '',
                partyId: 0,
                partyPlace: 0,
                compo: 'demo',
                languages: ['en'],
                hardware: ['zx48'],
                categoryIds: [self::CATEGORY_DEMO],
                groupIds: [$crewId],
                publisherIds: [$labelId],
                tags: [],
                compilationItemIds: [],
                seriesProdIds: [],
                refs: [
                    $this->ref(EntityType::Category, self::CATEGORY_DEMO),
                    new EntityRefDto(EntityType::GroupAlias, $crewId, 'Goldwrite Crew'),
                    new EntityRefDto(EntityType::Author, $labelId, 'Goldwrite Label'),
                ],
                authors: [new CreditDto(new EntityRefDto(EntityType::Author, $coderId, 'Goldwrite Coder'), ['code'])],
                releases: $this->container->get(FoundReleasesReader::class)->read([$tapeId]),
                files: [],
                inSeries: [new EntityRefDto(EntityType::Prod, $sagaId, 'Goldwrite Saga')],
                inCompilations: [new EntityRefDto(EntityType::Prod, $packId, 'Goldwrite Pack')],
            ),
            $this->actionRead('get_prod', '{"id": ' . $demoId . '}'),
        );
    }

    public function testGetProdOfNoProductionIsRefused(): void
    {
        $this->signInAsEditor();

        $this->assertRefused(404, ProdDataException::class, fn() => $this->actionRead('get_prod', '{"id": 999999999}'));
    }

    public function testCreateProdHasNoStateAndCreatesWithTheGivenFields(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldwrite Guild')))->id;
        $arguments = '{"title": "Goldwrite Demo", "year": 1999, "legalStatus": "mia", "hardware": ["zx128"], "groupIds": [' . $groupId . ']}';

        self::assertNull($this->actionState('create_prod', $arguments));
        $result = $this->actionWrite('create_prod', $arguments);

        self::assertInstanceOf(ProdDataResultDto::class, $result);
        $prod = $this->prod($result->id);
        self::assertSame('Goldwrite Demo', $prod->title);
        self::assertSame(1999, $prod->year);
        self::assertSame('mia', $prod->legalStatus);
        self::assertSame(['zx128'], $prod->hardware);
        self::assertSame([$groupId], $prod->groupIds);
    }

    public function testCreateProdWithoutATitleIsRefused(): void
    {
        $this->signInAsRegisteredUser();
        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage('title');

        $this->actionWrite('create_prod', '{"year": 1999}');
    }

    public function testUpdateProdStateIsTheProductionAndItChangesTheGivenFieldsOnly(): void
    {
        $this->signInAsEditor();
        $prodId = $this->createProd(new ProdFieldsDto(title: 'Goldwrite Demo', year: 1999, compo: 'demo'));
        $arguments = '{"id": ' . $prodId . ', "year": 2001, "legalStatus": "allowed", "tags": ["Goldwrite"]}';

        self::assertEquals($this->prod($prodId), $this->actionState('update_prod', $arguments));
        $this->actionWrite('update_prod', $arguments);

        $prod = $this->prod($prodId);
        self::assertSame(2001, $prod->year);
        self::assertSame('allowed', $prod->legalStatus);
        self::assertSame(['Goldwrite'], $prod->tags);
        self::assertSame('demo', $prod->compo);
    }

    public function testUpdateProdWithALegalStatusItDoesNotKnowIsRefused(): void
    {
        $this->signInAsEditor();
        $prodId = $this->createProd(new ProdFieldsDto(title: 'Goldwrite Demo'));
        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage('legalStatus');

        $this->actionWrite('update_prod', '{"id": ' . $prodId . ', "legalStatus": "free"}');
    }

    public function testUpdateProdWithoutThePrivilegeIsRefusedWhenItRuns(): void
    {
        $this->signInAsRegisteredUser();
        $prodId = (int)$this->db->table('module_zxprod')->min('id');

        $this->assertRefused(403, ProdDataException::class, fn() => $this->actionWrite('update_prod', '{"id": ' . $prodId . ', "year": 2001}'));
    }

    public function testDeleteProdStateListsTheReleasesItTakesAlongAndItDeletes(): void
    {
        $this->signInAsEditor();
        $prodId = $this->createProd(new ProdFieldsDto(title: 'Goldwrite Demo'));
        $this->createRelease($prodId);
        $this->createRelease($prodId);

        $state = $this->actionState('delete_prod', '{"id": ' . $prodId . '}');

        self::assertInstanceOf(ProdDto::class, $state);
        self::assertCount(2, $state->releases);
        $this->actionWrite('delete_prod', '{"id": ' . $prodId . '}');
        self::assertFalse($this->elementExists($prodId));
    }

    public function testJoinProdsStateIsTheJoinedProductionAndItJoins(): void
    {
        $this->signInAsEditor();
        $mainId = $this->createProd(new ProdFieldsDto(title: 'Goldwrite Demo'));
        $joinedId = $this->createProd(new ProdFieldsDto(title: 'Goldwrite Intro'));
        $tapeId = $this->createRelease($joinedId);
        $arguments = '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . ', "releasesOnly": true}';

        self::assertEquals($this->prod($joinedId), $this->actionState('join_prods', $arguments));
        $this->actionWrite('join_prods', $arguments);

        self::assertFalse($this->elementExists($joinedId));
        self::assertSame([$tapeId], $this->container->get(ReleasesDbal::class)->findIdsByProdId($mainId));
    }

    public function testSetProdAuthorStateIsTheProductionAndItCreditsThenRemoveDropsTheCredit(): void
    {
        $this->signInAsEditor();
        $prodId = $this->createProd(new ProdFieldsDto(title: 'Goldwrite Demo'));
        $authorId = $this->createAuthor('Goldwrite Coder');
        $arguments = '{"prodId": ' . $prodId . ', "authorId": ' . $authorId . ', "roles": ["code", "music"]}';

        self::assertEquals($this->prod($prodId), $this->actionState('set_prod_author', $arguments));
        $this->actionWrite('set_prod_author', $arguments);

        self::assertEquals(
            [new CreditDto(new EntityRefDto(EntityType::Author, $authorId, 'Goldwrite Coder'), ['code', 'music'])],
            $this->prod($prodId)->authors,
        );
        $this->actionWrite('remove_prod_author', '{"prodId": ' . $prodId . ', "authorId": ' . $authorId . '}');
        self::assertSame([], $this->prod($prodId)->authors);
    }

    public function testRemovingAnAuthorWhoIsNotCreditedIsRefusedWhenItRuns(): void
    {
        $this->signInAsEditor();
        $prodId = $this->createProd(new ProdFieldsDto(title: 'Goldwrite Demo'));
        $authorId = $this->createAuthor('Goldwrite Coder');

        $this->assertRefused(404, ProdDataException::class, fn() => $this->actionWrite('remove_prod_author', '{"prodId": ' . $prodId . ', "authorId": ' . $authorId . '}'));
    }

    private function createProd(ProdFieldsDto $fields): int
    {
        return $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand($fields))->id;
    }

    private function createRelease(int $prodId): int
    {
        return $this->container->get(CreateReleaseHandler::class)->handle(new CreateReleaseCommand(new ReleaseFieldsDto(title: 'Goldwrite Tape', prodId: $prodId)))->id;
    }

    private function createAuthor(string $title): int
    {
        return $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: $title)))->id;
    }

    private function prod(int $prodId): ProdDto
    {
        return $this->container->get(ProdDtos::class)->prod($prodId);
    }

    private function ref(EntityType $type, int $id): EntityRefDto
    {
        $element = $this->structureManager->getElementById($id);
        self::assertNotNull($element);

        return new EntityRefDto($type, $id, html_entity_decode((string)$element->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
