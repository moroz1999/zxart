<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/WriteToolTestCase.php';

use authorAliasElement;
use authorElement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Assistant\Authors\AuthorAliasDto;
use ZxArt\Assistant\Authors\AuthorDto;
use ZxArt\Assistant\Authors\AuthorDtos;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Services\AuthorDetailsService;
use ZxArt\Authors\UseCases\CreateAuthorAliasCommand;
use ZxArt\Authors\UseCases\CreateAuthorAliasHandler;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Groups\UseCases\SetGroupMemberCommand;
use ZxArt\Groups\UseCases\SetGroupMemberHandler;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Shared\Dto\AliasDto;
use ZxArt\Shared\Dto\MembershipDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefDto;

/**
 * The assistant's author and author alias actions: the fields they offer the
 * model, the state of their target a plan shows, and what they write.
 */
#[RunTestsInSeparateProcesses]
final class AuthorActionsTest extends WriteToolTestCase
{
    private const int COUNTRY_POLAND = 47450;
    private const int CITY_WARSAW = 47452;
    private const int CATEGORY_DEMO = 92159;

    /**
     * @return array<string, array{string, list<string>, list<string>}>
     */
    public static function schemas(): array
    {
        return [
            'get_author' => ['get_author', ['id'], ['id']],
            'get_author_alias' => ['get_author_alias', ['id'], ['id']],
            'create_author' => ['create_author', ['title', 'realName', 'countryId', 'cityId', 'wikiLink'], ['title']],
            'update_author' => ['update_author', ['id', 'title', 'realName', 'countryId', 'cityId', 'wikiLink'], ['id']],
            'delete_author' => ['delete_author', ['id'], ['id']],
            'join_authors' => ['join_authors', ['mainId', 'joinedId', 'asAlias'], ['mainId', 'joinedId']],
            'convert_author_to_group' => ['convert_author_to_group', ['id'], ['id']],
            'create_author_alias' => ['create_author_alias', ['authorId', 'title', 'startDate', 'endDate'], ['authorId', 'title']],
            'update_author_alias' => ['update_author_alias', ['id', 'authorId', 'title', 'startDate', 'endDate'], ['id']],
            'delete_author_alias' => ['delete_author_alias', ['id'], ['id']],
            'join_author_aliases' => ['join_author_aliases', ['mainId', 'joinedId'], ['mainId', 'joinedId']],
            'convert_author_alias_to_author' => ['convert_author_alias_to_author', ['id'], ['id']],
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

    /**
     * @return array<string, array{string, ChangeKind, EntityType}>
     */
    public static function kinds(): array
    {
        return [
            'create_author' => ['create_author', ChangeKind::Create, EntityType::Author],
            'update_author' => ['update_author', ChangeKind::Update, EntityType::Author],
            'delete_author' => ['delete_author', ChangeKind::Delete, EntityType::Author],
            'join_authors' => ['join_authors', ChangeKind::Join, EntityType::Author],
            'convert_author_to_group' => ['convert_author_to_group', ChangeKind::Convert, EntityType::Author],
            'create_author_alias' => ['create_author_alias', ChangeKind::Create, EntityType::AuthorAlias],
            'update_author_alias' => ['update_author_alias', ChangeKind::Update, EntityType::AuthorAlias],
            'delete_author_alias' => ['delete_author_alias', ChangeKind::Delete, EntityType::AuthorAlias],
            'join_author_aliases' => ['join_author_aliases', ChangeKind::Join, EntityType::AuthorAlias],
            'convert_author_alias_to_author' => ['convert_author_alias_to_author', ChangeKind::Convert, EntityType::AuthorAlias],
        ];
    }

    #[DataProvider('kinds')]
    public function testAWriteActionNamesItsKindAndTheTypeItChanges(string $actionName, ChangeKind $kind, EntityType $entityType): void
    {
        $action = $this->writeAction($actionName);

        self::assertSame($kind, $action->kind());
        self::assertSame($entityType, $action->entityType());
    }

    public function testGetAuthorAnswersTheAuthorWithItsAliasesGroupsAndCounters(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor(new AuthorFieldsDto(
            title: 'Goldwrite Coder',
            realName: 'Jan Kowalski',
            countryId: self::COUNTRY_POLAND,
            cityId: self::CITY_WARSAW,
            wikiLink: 'https://speccy.info/Goldwrite',
        ));
        $aliasId = $this->createAlias($authorId, 'Goldie', $this->date('01.05.1994'));
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldwrite Crew')))->id;
        $this->container->get(SetGroupMemberHandler::class)->handle(new SetGroupMemberCommand($groupId, $authorId, ['coder'], $this->date('01.01.1995'), 0));
        $this->creditOnNewProd($authorId);

        self::assertEquals(
            new AuthorDto(
                id: $authorId,
                title: 'Goldwrite Coder',
                realName: 'Jan Kowalski',
                countryId: self::COUNTRY_POLAND,
                cityId: self::CITY_WARSAW,
                wikiLink: 'https://speccy.info/Goldwrite',
                refs: [$this->ref(EntityType::Country, self::COUNTRY_POLAND), $this->ref(EntityType::City, self::CITY_WARSAW)],
                aliases: [new AliasDto($aliasId, 'Goldie', $this->date('01.05.1994'), 0)],
                groups: [new MembershipDto(new EntityRefDto(EntityType::Group, $groupId, 'Goldwrite Crew'), ['coder'], $this->date('01.01.1995'), 0)],
                counters: $this->container->get(AuthorDetailsService::class)->getCounters($this->element($authorId, authorElement::class)),
            ),
            $this->actionRead('get_author', '{"id": ' . $authorId . '}'),
        );
    }

    public function testGetAuthorAliasAnswersTheAliasWithItsAuthorAndDates(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Coder'));
        $aliasId = $this->createAlias($authorId, 'Goldie', $this->date('01.05.1994'));

        self::assertEquals(
            new AuthorAliasDto(
                id: $aliasId,
                title: 'Goldie',
                authorId: $authorId,
                startDate: $this->date('01.05.1994'),
                endDate: 0,
                refs: [new EntityRefDto(EntityType::Author, $authorId, 'Goldwrite Coder')],
                groups: [],
                counters: $this->container->get(AuthorDetailsService::class)->getCounters($this->element($aliasId, authorAliasElement::class)),
            ),
            $this->actionRead('get_author_alias', '{"id": ' . $aliasId . '}'),
        );
    }

    public function testGetAuthorOfNoAuthorIsRefused(): void
    {
        $this->signInAsEditor();

        $this->assertRefused(404, AuthorDataException::class, fn() => $this->actionRead('get_author', '{"id": 999999999}'));
    }

    public function testCreateAuthorHasNoStateAndCreatesWithTheGivenFields(): void
    {
        $this->signInAsEditor();
        $arguments = '{"title": "Goldwrite Coder", "realName": "Jan Kowalski", "countryId": ' . self::COUNTRY_POLAND . '}';

        self::assertNull($this->actionState('create_author', $arguments));
        $result = $this->actionWrite('create_author', $arguments);

        self::assertInstanceOf(AuthorDataResultDto::class, $result);
        $author = $this->author($result->id);
        self::assertSame('Goldwrite Coder', $author->title);
        self::assertSame('Jan Kowalski', $author->realName);
        self::assertSame(self::COUNTRY_POLAND, $author->countryId);
    }

    public function testUpdateAuthorStateIsTheAuthorAsItIs(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Coder'));

        self::assertEquals($this->author($authorId), $this->actionState('update_author', '{"id": ' . $authorId . ', "realName": "Jan Kowalski"}'));
    }

    public function testUpdateAuthorChangesTheGivenFieldsAndLeavesTheOthersAlone(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Coder', wikiLink: 'https://speccy.info/Goldwrite', displayInMusic: true));

        $this->actionWrite('update_author', '{"id": ' . $authorId . ', "realName": "Jan Kowalski", "countryId": ' . self::COUNTRY_POLAND . '}');

        $author = $this->author($authorId);
        self::assertSame('Goldwrite Coder', $author->title);
        self::assertSame('Jan Kowalski', $author->realName);
        self::assertSame(self::COUNTRY_POLAND, $author->countryId);
        self::assertSame('https://speccy.info/Goldwrite', $author->wikiLink);
        self::assertEquals(['displayInMusic' => 1], $this->storedRow('module_author', $authorId, ['displayInMusic']));
    }

    public function testUpdateAuthorOfNoAuthorHasNoStateToShow(): void
    {
        $this->signInAsEditor();

        $this->assertRefused(404, AuthorDataException::class, fn() => $this->actionState('update_author', '{"id": 999999999, "realName": "X"}'));
    }

    public function testUpdateAuthorWithoutThePrivilegeIsRefusedWhenItRuns(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = (int)$this->db->table('module_author')->min('id');

        $this->assertRefused(403, AuthorDataException::class, fn() => $this->actionWrite('update_author', '{"id": ' . $authorId . ', "realName": "X"}'));
    }

    public function testDeleteAuthorStateCountsItsWorksAndAliasesAndItDeletes(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Coder'));
        $this->creditOnNewProd($authorId);
        $this->createAlias($authorId, 'Goldie');

        $state = $this->actionState('delete_author', '{"id": ' . $authorId . '}');

        self::assertInstanceOf(AuthorDto::class, $state);
        self::assertSame(1, $state->counters->prods);
        self::assertCount(1, $state->aliases);
        $this->actionWrite('delete_author', '{"id": ' . $authorId . '}');
        self::assertFalse($this->elementExists($authorId));
    }

    public function testJoinAuthorsStateIsTheJoinedAuthorAndItJoins(): void
    {
        $this->signInAsEditor();
        $mainId = $this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Coder'));
        $joinedId = $this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Koder'));
        $arguments = '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . '}';

        self::assertEquals($this->author($joinedId), $this->actionState('join_authors', $arguments));
        $this->actionWrite('join_authors', $arguments);

        self::assertFalse($this->elementExists($joinedId));
        self::assertSame([], $this->author($mainId)->aliases);
    }

    public function testJoinAuthorsAsAliasLeavesTheJoinedNameAsAnAlias(): void
    {
        $this->signInAsEditor();
        $mainId = $this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Coder'));
        $joinedId = $this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Koder'));

        $this->actionWrite('join_authors', '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . ', "asAlias": true}');

        self::assertSame(
            [$mainId],
            array_map('intval', $this->db->table('module_authoralias')->where('title', '=', 'Goldwrite Koder')->pluck('authorId')),
        );
    }

    public function testConvertAuthorToGroupStateIsTheAuthorAndItConverts(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Crew'));

        self::assertEquals($this->author($authorId), $this->actionState('convert_author_to_group', '{"id": ' . $authorId . '}'));
        $result = $this->actionWrite('convert_author_to_group', '{"id": ' . $authorId . '}');

        self::assertInstanceOf(AuthorDataResultDto::class, $result);
        self::assertSame('group', $this->db->table('structure_elements')->where('id', '=', $result->id)->value('structureType'));
    }

    public function testCreateAuthorAliasHasNoStateAndCreatesWithItsDates(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Coder'));
        $arguments = '{"authorId": ' . $authorId . ', "title": "Goldie", "startDate": ' . $this->date('01.05.1994') . '}';

        self::assertNull($this->actionState('create_author_alias', $arguments));
        $result = $this->actionWrite('create_author_alias', $arguments);

        self::assertInstanceOf(AuthorAliasDataResultDto::class, $result);
        $alias = $this->alias($result->id);
        self::assertSame('Goldie', $alias->title);
        self::assertSame($authorId, $alias->authorId);
        self::assertSame($this->date('01.05.1994'), $alias->startDate);
    }

    public function testUpdateAuthorAliasStateIsTheAliasAndItChangesTheGivenFields(): void
    {
        $this->signInAsEditor();
        $aliasId = $this->createAlias($this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Coder')), 'Goldie', $this->date('01.05.1994'));
        $arguments = '{"id": ' . $aliasId . ', "title": "Goldy"}';

        self::assertEquals($this->alias($aliasId), $this->actionState('update_author_alias', $arguments));
        $this->actionWrite('update_author_alias', $arguments);

        $alias = $this->alias($aliasId);
        self::assertSame('Goldy', $alias->title);
        self::assertSame($this->date('01.05.1994'), $alias->startDate);
    }

    public function testDeleteAuthorAliasStateIsTheAliasAndItDeletes(): void
    {
        $this->signInAsEditor();
        $aliasId = $this->createAlias($this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Coder')), 'Goldie');

        self::assertEquals($this->alias($aliasId), $this->actionState('delete_author_alias', '{"id": ' . $aliasId . '}'));
        $this->actionWrite('delete_author_alias', '{"id": ' . $aliasId . '}');

        self::assertFalse($this->elementExists($aliasId));
    }

    public function testJoinAuthorAliasesStateIsTheJoinedAliasAndItJoins(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Coder'));
        $mainId = $this->createAlias($authorId, 'Goldie');
        $joinedId = $this->createAlias($authorId, 'Goldy');
        $arguments = '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . '}';

        self::assertEquals($this->alias($joinedId), $this->actionState('join_author_aliases', $arguments));
        $this->actionWrite('join_author_aliases', $arguments);

        self::assertFalse($this->elementExists($joinedId));
    }

    public function testConvertAuthorAliasToAuthorStateIsTheAliasAndItConverts(): void
    {
        $this->signInAsEditor();
        $aliasId = $this->createAlias($this->createAuthor(new AuthorFieldsDto(title: 'Goldwrite Coder')), 'Goldie');

        self::assertEquals($this->alias($aliasId), $this->actionState('convert_author_alias_to_author', '{"id": ' . $aliasId . '}'));
        $result = $this->actionWrite('convert_author_alias_to_author', '{"id": ' . $aliasId . '}');

        self::assertInstanceOf(AuthorAliasDataResultDto::class, $result);
        self::assertSame('author', $this->db->table('structure_elements')->where('id', '=', $result->id)->value('structureType'));
    }

    private function createAuthor(AuthorFieldsDto $fields): int
    {
        return $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand($fields))->id;
    }

    private function createAlias(int $authorId, string $title, ?int $startDate = null): int
    {
        return $this->container->get(CreateAuthorAliasHandler::class)->handle(new CreateAuthorAliasCommand(new AuthorAliasFieldsDto(title: $title, authorId: $authorId, startDate: $startDate)))->id;
    }

    private function creditOnNewProd(int $authorId): void
    {
        // the counters count the productions of a category
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldwrite Demo', categoryIds: [self::CATEGORY_DEMO])))->id;
        $this->addAuthorship($authorId, $prodId, 'prod');
    }

    private function author(int $authorId): AuthorDto
    {
        return $this->container->get(AuthorDtos::class)->author($authorId);
    }

    private function alias(int $aliasId): AuthorAliasDto
    {
        return $this->container->get(AuthorDtos::class)->alias($aliasId);
    }

    private function ref(EntityType $type, int $id): EntityRefDto
    {
        $element = $this->structureManager->getElementById($id);
        self::assertNotNull($element);

        return new EntityRefDto($type, $id, html_entity_decode((string)$element->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function element(int $id, string $class): object
    {
        $element = $this->structureManager->getElementById($id);
        self::assertInstanceOf($class, $element);

        return $element;
    }
}
