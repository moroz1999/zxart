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
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Services\GroupAliasDataService;
use ZxArt\Groups\Services\GroupAliasStateService;
use ZxArt\Groups\Services\GroupDataService;
use ZxArt\Groups\Services\GroupStateService;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * The group and group alias write tools.
 */
#[RunTestsInSeparateProcesses]
final class GroupWriteToolsTest extends WriteToolTestCase
{
    public function testCreateGroupPreviewsTheGivenFieldsAndCreates(): void
    {
        $this->signInAsEditor();
        $start = (int)strtotime('1994-05-01 12:00');
        $arguments = '{"fields": {"title": "Goldwrite Crew", "startDate": ' . $start . '}}';

        self::assertEquals(
            new ChangePreviewDto(
                kind: ChangeKind::Create,
                target: new ChangeTargetDto(EntityType::Group, null, 'Goldwrite Crew'),
                changes: [new FieldChangeDto('title', null, 'Goldwrite Crew'), new FieldChangeDto('startDate', null, '1994-05-01')],
                consequences: [],
                modifiedAt: null,
            ),
            $this->preview('create_group', $arguments),
        );
        $result = $this->execute('create_group', $arguments);

        self::assertInstanceOf(GroupDataResultDto::class, $result);
        self::assertSame('Goldwrite Crew', $this->container->get(GroupStateService::class)->get($result->id)->fields->title);
    }

    public function testUpdateGroupPreviewsWhatChangesAndUpdates(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup('Goldwrite Crew');
        $arguments = '{"id": ' . $groupId . ', "fields": {"abbreviation": "GWC"}}';

        $preview = $this->preview('update_group', $arguments);

        self::assertEquals([new FieldChangeDto('abbreviation', '', 'GWC')], $preview->changes);
        self::assertSame($this->modificationTime($groupId), $preview->modifiedAt);
        $this->execute('update_group', $arguments);
        self::assertSame('GWC', $this->container->get(GroupStateService::class)->get($groupId)->fields->abbreviation);
    }

    public function testUpdateGroupIsRefusedAtThePreviewWithoutThePrivilege(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = (int)$this->db->table('module_group')->min('id');

        $this->assertRefused(403, GroupDataException::class, fn() => $this->preview('update_group', '{"id": ' . $groupId . ', "fields": {"slogan": "X"}}'));
    }

    public function testDeleteGroupPreviewsItsMembersAndDeletes(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup('Goldwrite Crew');
        $this->container->get(GroupDataService::class)->setMember($groupId, $this->createAuthor(), ['coder']);

        $preview = $this->preview('delete_group', '{"id": ' . $groupId . '}');

        self::assertSame(["[[group:$groupId]] has 1 member"], $preview->consequences);
        $this->execute('delete_group', '{"id": ' . $groupId . '}');
        self::assertFalse($this->elementExists($groupId));
    }

    public function testJoinGroupsPreviewsWhatMovesAndJoins(): void
    {
        $this->signInAsEditor();
        $mainId = $this->createGroup('Goldwrite Crew');
        $joinedId = $this->createGroup('Goldwrite Krew');
        $this->container->get(GroupDataService::class)->setMember($joinedId, $this->createAuthor(), ['coder']);
        $arguments = '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . '}';

        self::assertSame(
            [
                "[[group:$joinedId]] is deleted",
                "1 member moves to [[group:$mainId]]",
                "Its aliases and import origins move to [[group:$mainId]]",
            ],
            $this->preview('join_groups', $arguments)->consequences,
        );
        $this->execute('join_groups', $arguments);
        self::assertFalse($this->elementExists($joinedId));
    }

    public function testConvertGroupToAuthorPreviewsTheReplacementAndConverts(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup('Goldwrite Solo');

        self::assertSame(
            ["[[group:$groupId]] is deleted; a new author of the same title is credited on its productions and published releases and takes its publications and press mentions"],
            $this->preview('convert_group_to_author', '{"id": ' . $groupId . '}')->consequences,
        );
        $result = $this->execute('convert_group_to_author', '{"id": ' . $groupId . '}');
        self::assertInstanceOf(GroupDataResultDto::class, $result);
        self::assertSame('author', $this->db->table('structure_elements')->where('id', '=', $result->id)->value('structureType'));
    }

    public function testSetAndRemoveGroupMemberPreviewTheMembershipWithDates(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup('Goldwrite Crew');
        $authorId = $this->createAuthor();
        $start = (int)strtotime('1994-05-01 12:00');
        $arguments = '{"groupId": ' . $groupId . ', "authorId": ' . $authorId . ', "roles": ["coder"], "startDate": ' . $start . '}';
        $author = new EntityMentionDto(EntityType::Author, $authorId);

        self::assertEquals(
            [
                new FieldChangeDto('author', null, $author),
                new FieldChangeDto('roles', null, ['coder']),
                new FieldChangeDto('startDate', null, '1994-05-01'),
            ],
            $this->preview('set_group_member', $arguments)->changes,
        );
        $this->execute('set_group_member', $arguments);
        $removal = '{"groupId": ' . $groupId . ', "authorId": ' . $authorId . '}';

        self::assertSame(ChangeKind::Unlink, $this->preview('remove_group_member', $removal)->kind);
        $this->execute('remove_group_member', $removal);
        self::assertSame([], $this->container->get(GroupStateService::class)->get($groupId)->members);
    }

    public function testCreateUpdateAndDeleteGroupAliasPreviewAndWrite(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup('Goldwrite Crew');
        $creation = '{"fields": {"title": "GWC", "groupId": ' . $groupId . '}}';

        self::assertEquals(
            [new FieldChangeDto('title', null, 'GWC'), new FieldChangeDto('groupId', null, new EntityMentionDto(EntityType::Group, $groupId))],
            $this->preview('create_group_alias', $creation)->changes,
        );
        $result = $this->execute('create_group_alias', $creation);
        self::assertInstanceOf(GroupAliasDataResultDto::class, $result);
        $aliasId = $result->id;

        self::assertEquals(
            [new FieldChangeDto('title', 'GWC', 'G.W.C.')],
            $this->preview('update_group_alias', '{"id": ' . $aliasId . ', "fields": {"title": "G.W.C."}}')->changes,
        );
        $this->execute('update_group_alias', '{"id": ' . $aliasId . ', "fields": {"title": "G.W.C."}}');
        self::assertSame(ChangeKind::Delete, $this->preview('delete_group_alias', '{"id": ' . $aliasId . '}')->kind);
        $this->execute('delete_group_alias', '{"id": ' . $aliasId . '}');
        self::assertFalse($this->elementExists($aliasId));
    }

    public function testJoinAndConvertGroupAliasesPreviewWhatHappens(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup('Goldwrite Crew');
        $mainId = $this->createAlias($groupId, 'GWC');
        $joinedId = $this->createAlias($groupId, 'G.W.C.');

        self::assertSame(
            ["[[groupAlias:$joinedId]] is deleted", "Its productions, publications and members move to [[groupAlias:$mainId]]"],
            $this->preview('join_group_aliases', '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . '}')->consequences,
        );
        $this->execute('join_group_aliases', '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . '}');

        self::assertSame(
            ["[[groupAlias:$mainId]] is deleted; a new group of the same title takes its productions, publications, members and import origins"],
            $this->preview('convert_group_alias_to_group', '{"id": ' . $mainId . '}')->consequences,
        );
        $result = $this->execute('convert_group_alias_to_group', '{"id": ' . $mainId . '}');
        self::assertInstanceOf(GroupAliasDataResultDto::class, $result);
        self::assertSame('group', $this->db->table('structure_elements')->where('id', '=', $result->id)->value('structureType'));
    }

    public function testSetAndRemoveGroupAliasMemberPreviewTheMembership(): void
    {
        $this->signInAsEditor();
        $aliasId = $this->createAlias($this->createGroup('Goldwrite Crew'), 'GWC');
        $authorId = $this->createAuthor();
        $arguments = '{"aliasId": ' . $aliasId . ', "authorId": ' . $authorId . ', "roles": ["coder"]}';

        self::assertSame(ChangeKind::Link, $this->preview('set_group_alias_member', $arguments)->kind);
        $this->execute('set_group_alias_member', $arguments);
        $removal = '{"aliasId": ' . $aliasId . ', "authorId": ' . $authorId . '}';
        self::assertEquals(
            [new FieldChangeDto('author', new EntityMentionDto(EntityType::Author, $authorId), null), new FieldChangeDto('roles', ['coder'], null)],
            $this->preview('remove_group_alias_member', $removal)->changes,
        );
        $this->execute('remove_group_alias_member', $removal);
        self::assertSame([], $this->container->get(GroupAliasStateService::class)->get($aliasId)->members);
    }

    private function createGroup(string $title): int
    {
        return $this->container->get(GroupDataService::class)->create(new GroupFieldsDto(title: $title))->id;
    }

    private function createAlias(int $groupId, string $title): int
    {
        return $this->container->get(GroupAliasDataService::class)->create(new GroupAliasFieldsDto(title: $title, groupId: $groupId))->id;
    }

    private function createAuthor(): int
    {
        return $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldwrite Coder'))->id;
    }
}
