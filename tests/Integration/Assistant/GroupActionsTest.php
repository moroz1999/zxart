<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/WriteToolTestCase.php';

use groupAliasElement;
use groupElement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Assistant\Groups\GroupAliasDto;
use ZxArt\Assistant\Groups\GroupDto;
use ZxArt\Assistant\Groups\GroupDtos;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\GroupTypes;
use ZxArt\Groups\Services\GroupDetailsService;
use ZxArt\Groups\UseCases\CreateGroupAliasCommand;
use ZxArt\Groups\UseCases\CreateGroupAliasHandler;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Groups\UseCases\SetGroupMemberCommand;
use ZxArt\Groups\UseCases\SetGroupMemberHandler;
use ZxArt\Shared\Dto\AliasDto;
use ZxArt\Shared\Dto\MembershipDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefDto;

/**
 * The assistant's group and group alias actions: the fields they offer the
 * model, the state of their target a plan shows, and what they write.
 */
#[RunTestsInSeparateProcesses]
final class GroupActionsTest extends WriteToolTestCase
{
    private const int COUNTRY_POLAND = 47450;
    private const int CITY_WARSAW = 47452;

    /**
     * @return array<string, array{string, list<string>, list<string>}>
     */
    public static function schemas(): array
    {
        $groupFields = ['title', 'abbreviation', 'type', 'countryId', 'cityId', 'website', 'wikiLink', 'slogan', 'startDate', 'endDate', 'subGroupIds'];
        $aliasFields = ['groupId', 'title', 'startDate', 'endDate'];

        return [
            'get_group' => ['get_group', ['id'], ['id']],
            'get_group_alias' => ['get_group_alias', ['id'], ['id']],
            'create_group' => ['create_group', $groupFields, ['title']],
            'update_group' => ['update_group', ['id', ...$groupFields], ['id']],
            'delete_group' => ['delete_group', ['id'], ['id']],
            'join_groups' => ['join_groups', ['mainId', 'joinedId', 'asAlias'], ['mainId', 'joinedId']],
            'convert_group_to_author' => ['convert_group_to_author', ['id'], ['id']],
            'set_group_member' => ['set_group_member', ['groupId', 'authorId', 'roles', 'startDate', 'endDate'], ['groupId', 'authorId', 'roles']],
            'remove_group_member' => ['remove_group_member', ['groupId', 'authorId'], ['groupId', 'authorId']],
            'create_group_alias' => ['create_group_alias', $aliasFields, ['groupId', 'title']],
            'update_group_alias' => ['update_group_alias', ['id', ...$aliasFields], ['id']],
            'delete_group_alias' => ['delete_group_alias', ['id'], ['id']],
            'join_group_aliases' => ['join_group_aliases', ['mainId', 'joinedId'], ['mainId', 'joinedId']],
            'convert_group_alias_to_group' => ['convert_group_alias_to_group', ['id'], ['id']],
            'set_group_alias_member' => ['set_group_alias_member', ['aliasId', 'authorId', 'roles', 'startDate', 'endDate'], ['aliasId', 'authorId', 'roles']],
            'remove_group_alias_member' => ['remove_group_alias_member', ['aliasId', 'authorId'], ['aliasId', 'authorId']],
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

    public function testTheGroupTypeIsOneOfTheGroupTypes(): void
    {
        self::assertSame([...GroupTypes::LIST, null], $this->actionSchema('update_group')['properties']['type']['enum']);
    }

    /**
     * @return array<string, array{string, ChangeKind, EntityType}>
     */
    public static function kinds(): array
    {
        return [
            'create_group' => ['create_group', ChangeKind::Create, EntityType::Group],
            'update_group' => ['update_group', ChangeKind::Update, EntityType::Group],
            'delete_group' => ['delete_group', ChangeKind::Delete, EntityType::Group],
            'join_groups' => ['join_groups', ChangeKind::Join, EntityType::Group],
            'convert_group_to_author' => ['convert_group_to_author', ChangeKind::Convert, EntityType::Group],
            'set_group_member' => ['set_group_member', ChangeKind::Link, EntityType::Group],
            'remove_group_member' => ['remove_group_member', ChangeKind::Unlink, EntityType::Group],
            'create_group_alias' => ['create_group_alias', ChangeKind::Create, EntityType::GroupAlias],
            'update_group_alias' => ['update_group_alias', ChangeKind::Update, EntityType::GroupAlias],
            'delete_group_alias' => ['delete_group_alias', ChangeKind::Delete, EntityType::GroupAlias],
            'join_group_aliases' => ['join_group_aliases', ChangeKind::Join, EntityType::GroupAlias],
            'convert_group_alias_to_group' => ['convert_group_alias_to_group', ChangeKind::Convert, EntityType::GroupAlias],
            'set_group_alias_member' => ['set_group_alias_member', ChangeKind::Link, EntityType::GroupAlias],
            'remove_group_alias_member' => ['remove_group_alias_member', ChangeKind::Unlink, EntityType::GroupAlias],
        ];
    }

    #[DataProvider('kinds')]
    public function testAWriteActionNamesItsKindAndTheTypeItChanges(string $actionName, ChangeKind $kind, EntityType $entityType): void
    {
        $action = $this->writeAction($actionName);

        self::assertSame($kind, $action->kind());
        self::assertSame($entityType, $action->entityType());
    }

    public function testGetGroupAnswersTheGroupWithItsAliasesMembersSubgroupsAndCounters(): void
    {
        $this->signInAsEditor();
        $divisionId = $this->createGroup(new GroupFieldsDto(title: 'Goldwrite Division'));
        $groupId = $this->createGroup(new GroupFieldsDto(
            title: 'Goldwrite Crew',
            abbreviation: 'GWC',
            type: 'scene',
            countryId: self::COUNTRY_POLAND,
            cityId: self::CITY_WARSAW,
            website: 'https://goldwrite.example',
            wikiLink: 'https://speccy.info/Goldwrite_Crew',
            slogan: 'Gold forever',
            startDate: $this->date('01.01.1993'),
            subGroupIds: [$divisionId],
        ));
        $aliasId = $this->createAlias($groupId, 'G.W.C.');
        $authorId = $this->createAuthor();
        $this->container->get(SetGroupMemberHandler::class)->handle(new SetGroupMemberCommand($groupId, $authorId, ['coder'], $this->date('01.01.1995'), 0));

        self::assertEquals(
            new GroupDto(
                id: $groupId,
                title: 'Goldwrite Crew',
                abbreviation: 'GWC',
                type: 'scene',
                countryId: self::COUNTRY_POLAND,
                cityId: self::CITY_WARSAW,
                website: 'https://goldwrite.example',
                wikiLink: 'https://speccy.info/Goldwrite_Crew',
                slogan: 'Gold forever',
                startDate: $this->date('01.01.1993'),
                endDate: 0,
                subGroupIds: [$divisionId],
                refs: [
                    $this->ref(EntityType::Country, self::COUNTRY_POLAND),
                    $this->ref(EntityType::City, self::CITY_WARSAW),
                    new EntityRefDto(EntityType::Group, $divisionId, 'Goldwrite Division'),
                ],
                aliases: [new AliasDto($aliasId, 'G.W.C.', 0, 0)],
                members: [new MembershipDto(new EntityRefDto(EntityType::Author, $authorId, 'Goldwrite Coder'), ['coder'], $this->date('01.01.1995'), 0)],
                parentGroups: [],
                counters: $this->container->get(GroupDetailsService::class)->getCounters($this->element($groupId, groupElement::class)),
            ),
            $this->actionRead('get_group', '{"id": ' . $groupId . '}'),
        );
        self::assertEquals(
            [new EntityRefDto(EntityType::Group, $groupId, 'Goldwrite Crew')],
            $this->group($divisionId)->parentGroups,
        );
    }

    public function testGetGroupAliasAnswersTheAliasWithItsGroupAndMembers(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup(new GroupFieldsDto(title: 'Goldwrite Crew'));
        $aliasId = $this->createAlias($groupId, 'G.W.C.', $this->date('01.05.1994'));

        self::assertEquals(
            new GroupAliasDto(
                id: $aliasId,
                title: 'G.W.C.',
                groupId: $groupId,
                startDate: $this->date('01.05.1994'),
                endDate: 0,
                refs: [new EntityRefDto(EntityType::Group, $groupId, 'Goldwrite Crew')],
                members: [],
                counters: $this->container->get(GroupDetailsService::class)->getCounters($this->element($aliasId, groupAliasElement::class)),
            ),
            $this->actionRead('get_group_alias', '{"id": ' . $aliasId . '}'),
        );
    }

    public function testGetGroupOfNoGroupIsRefused(): void
    {
        $this->signInAsEditor();

        $this->assertRefused(404, GroupDataException::class, fn() => $this->actionRead('get_group', '{"id": 999999999}'));
    }

    public function testCreateGroupHasNoStateAndCreatesWithTheGivenFields(): void
    {
        $this->signInAsEditor();
        $divisionId = $this->createGroup(new GroupFieldsDto(title: 'Goldwrite Division'));
        $arguments = '{"title": "Goldwrite Crew", "type": "scene", "countryId": ' . self::COUNTRY_POLAND . ', "subGroupIds": [' . $divisionId . ']}';

        self::assertNull($this->actionState('create_group', $arguments));
        $result = $this->actionWrite('create_group', $arguments);

        self::assertInstanceOf(GroupDataResultDto::class, $result);
        $group = $this->group($result->id);
        self::assertSame('Goldwrite Crew', $group->title);
        self::assertSame('scene', $group->type);
        self::assertSame(self::COUNTRY_POLAND, $group->countryId);
        self::assertSame([$divisionId], $group->subGroupIds);
    }

    public function testUpdateGroupStateIsTheGroupAndItChangesTheGivenFieldsOnly(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup(new GroupFieldsDto(title: 'Goldwrite Crew', slogan: 'Gold forever'));
        $arguments = '{"id": ' . $groupId . ', "abbreviation": "GWC", "endDate": ' . $this->date('01.01.2001') . '}';

        self::assertEquals($this->group($groupId), $this->actionState('update_group', $arguments));
        $this->actionWrite('update_group', $arguments);

        $group = $this->group($groupId);
        self::assertSame('GWC', $group->abbreviation);
        self::assertSame($this->date('01.01.2001'), $group->endDate);
        self::assertSame('Gold forever', $group->slogan);
    }

    public function testUpdateGroupWithAGroupTypeItDoesNotKnowIsRefused(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup(new GroupFieldsDto(title: 'Goldwrite Crew'));

        $this->expectExceptionMessage('type');

        $this->actionWrite('update_group', '{"id": ' . $groupId . ', "type": "band"}');
    }

    public function testUpdateGroupWithoutThePrivilegeIsRefusedWhenItRuns(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = (int)$this->db->table('module_group')->min('id');

        $this->assertRefused(403, GroupDataException::class, fn() => $this->actionWrite('update_group', '{"id": ' . $groupId . ', "slogan": "X"}'));
    }

    public function testDeleteGroupStateCountsItsMembersAndItDeletes(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup(new GroupFieldsDto(title: 'Goldwrite Crew'));
        $this->container->get(SetGroupMemberHandler::class)->handle(new SetGroupMemberCommand($groupId, $this->createAuthor(), ['coder']));

        $state = $this->actionState('delete_group', '{"id": ' . $groupId . '}');

        self::assertInstanceOf(GroupDto::class, $state);
        self::assertSame(1, $state->counters->members);
        $this->actionWrite('delete_group', '{"id": ' . $groupId . '}');
        self::assertFalse($this->elementExists($groupId));
    }

    public function testJoinGroupsStateIsTheJoinedGroupAndItJoins(): void
    {
        $this->signInAsEditor();
        $mainId = $this->createGroup(new GroupFieldsDto(title: 'Goldwrite Crew'));
        $joinedId = $this->createGroup(new GroupFieldsDto(title: 'Goldwrite Krew'));
        $arguments = '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . ', "asAlias": true}';

        self::assertEquals($this->group($joinedId), $this->actionState('join_groups', $arguments));
        $this->actionWrite('join_groups', $arguments);

        self::assertFalse($this->elementExists($joinedId));
        self::assertSame(
            [$mainId],
            array_map('intval', $this->db->table('module_groupalias')->where('title', '=', 'Goldwrite Krew')->pluck('groupId')),
        );
    }

    public function testConvertGroupToAuthorStateIsTheGroupAndItConverts(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup(new GroupFieldsDto(title: 'Goldwrite Solo'));

        self::assertEquals($this->group($groupId), $this->actionState('convert_group_to_author', '{"id": ' . $groupId . '}'));
        $result = $this->actionWrite('convert_group_to_author', '{"id": ' . $groupId . '}');

        self::assertInstanceOf(GroupDataResultDto::class, $result);
        self::assertSame('author', $this->db->table('structure_elements')->where('id', '=', $result->id)->value('structureType'));
    }

    public function testSetGroupMemberStateIsTheGroupAndItSetsTheMembershipThenRemoveDropsIt(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup(new GroupFieldsDto(title: 'Goldwrite Crew'));
        $authorId = $this->createAuthor();
        $arguments = '{"groupId": ' . $groupId . ', "authorId": ' . $authorId . ', "roles": ["coder"], "startDate": ' . $this->date('01.05.1994') . '}';

        self::assertEquals($this->group($groupId), $this->actionState('set_group_member', $arguments));
        $this->actionWrite('set_group_member', $arguments);
        self::assertEquals(
            [new MembershipDto(new EntityRefDto(EntityType::Author, $authorId, 'Goldwrite Coder'), ['coder'], $this->date('01.05.1994'), 0)],
            $this->group($groupId)->members,
        );

        $this->actionWrite('remove_group_member', '{"groupId": ' . $groupId . ', "authorId": ' . $authorId . '}');
        self::assertSame([], $this->group($groupId)->members);
    }

    public function testCreateGroupAliasHasNoStateAndCreates(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup(new GroupFieldsDto(title: 'Goldwrite Crew'));
        $arguments = '{"groupId": ' . $groupId . ', "title": "GWC", "startDate": ' . $this->date('01.05.1994') . '}';

        self::assertNull($this->actionState('create_group_alias', $arguments));
        $result = $this->actionWrite('create_group_alias', $arguments);

        self::assertInstanceOf(GroupAliasDataResultDto::class, $result);
        $alias = $this->alias($result->id);
        self::assertSame('GWC', $alias->title);
        self::assertSame($groupId, $alias->groupId);
        self::assertSame($this->date('01.05.1994'), $alias->startDate);
    }

    public function testUpdateAndDeleteGroupAliasShowTheAliasAndWrite(): void
    {
        $this->signInAsEditor();
        $aliasId = $this->createAlias($this->createGroup(new GroupFieldsDto(title: 'Goldwrite Crew')), 'GWC');

        self::assertEquals($this->alias($aliasId), $this->actionState('update_group_alias', '{"id": ' . $aliasId . ', "title": "G.W.C."}'));
        $this->actionWrite('update_group_alias', '{"id": ' . $aliasId . ', "title": "G.W.C."}');
        self::assertSame('G.W.C.', $this->alias($aliasId)->title);

        self::assertEquals($this->alias($aliasId), $this->actionState('delete_group_alias', '{"id": ' . $aliasId . '}'));
        $this->actionWrite('delete_group_alias', '{"id": ' . $aliasId . '}');
        self::assertFalse($this->elementExists($aliasId));
    }

    public function testJoinAndConvertGroupAliasesShowTheAliasAndWrite(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup(new GroupFieldsDto(title: 'Goldwrite Crew'));
        $mainId = $this->createAlias($groupId, 'GWC');
        $joinedId = $this->createAlias($groupId, 'G.W.C.');
        $arguments = '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . '}';

        self::assertEquals($this->alias($joinedId), $this->actionState('join_group_aliases', $arguments));
        $this->actionWrite('join_group_aliases', $arguments);
        self::assertFalse($this->elementExists($joinedId));

        self::assertEquals($this->alias($mainId), $this->actionState('convert_group_alias_to_group', '{"id": ' . $mainId . '}'));
        $result = $this->actionWrite('convert_group_alias_to_group', '{"id": ' . $mainId . '}');
        self::assertInstanceOf(GroupAliasDataResultDto::class, $result);
        self::assertSame('group', $this->db->table('structure_elements')->where('id', '=', $result->id)->value('structureType'));
    }

    public function testSetAndRemoveGroupAliasMemberShowTheAliasAndWrite(): void
    {
        $this->signInAsEditor();
        $aliasId = $this->createAlias($this->createGroup(new GroupFieldsDto(title: 'Goldwrite Crew')), 'GWC');
        $authorId = $this->createAuthor();
        $arguments = '{"aliasId": ' . $aliasId . ', "authorId": ' . $authorId . ', "roles": ["coder"]}';

        self::assertEquals($this->alias($aliasId), $this->actionState('set_group_alias_member', $arguments));
        $this->actionWrite('set_group_alias_member', $arguments);
        self::assertEquals(
            [new MembershipDto(new EntityRefDto(EntityType::Author, $authorId, 'Goldwrite Coder'), ['coder'], 0, 0)],
            $this->alias($aliasId)->members,
        );

        $this->actionWrite('remove_group_alias_member', '{"aliasId": ' . $aliasId . ', "authorId": ' . $authorId . '}');
        self::assertSame([], $this->alias($aliasId)->members);
    }

    private function createGroup(GroupFieldsDto $fields): int
    {
        return $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand($fields))->id;
    }

    private function createAlias(int $groupId, string $title, ?int $startDate = null): int
    {
        return $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: $title, groupId: $groupId, startDate: $startDate)))->id;
    }

    private function createAuthor(): int
    {
        return $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldwrite Coder')))->id;
    }

    private function group(int $groupId): GroupDto
    {
        return $this->container->get(GroupDtos::class)->group($groupId);
    }

    private function alias(int $aliasId): GroupAliasDto
    {
        return $this->container->get(GroupDtos::class)->alias($aliasId);
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
