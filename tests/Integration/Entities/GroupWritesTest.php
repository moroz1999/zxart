<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use Closure;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Dto\GroupSearchFilterDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Services\GroupSearchService;
use ZxArt\Groups\UseCases\CreateGroupAliasCommand;
use ZxArt\Groups\UseCases\CreateGroupAliasHandler;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Groups\UseCases\JoinGroupsCommand;
use ZxArt\Groups\UseCases\JoinGroupsHandler;
use ZxArt\Groups\UseCases\SetGroupMemberCommand;
use ZxArt\Groups\UseCases\SetGroupMemberHandler;
use ZxArt\Groups\UseCases\UpdateGroupCommand;
use ZxArt\Groups\UseCases\UpdateGroupHandler;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Import\ImportOrigin;
use ZxArt\LinkTypes;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\ProdLink;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;

/**
 * Groups written through their use cases.
 */
#[RunTestsInSeparateProcesses]
final class GroupWritesTest extends EntityWriteTestCase
{
    private const string TABLE = 'module_group';
    private const int COUNTRY_POLAND = 47450;
    private const int CITY_WARSAW = 47452;
    private const int COUNTRY_UNITED_KINGDOM = 47542;
    private const int CITY_LONDON = 47543;
    private const array COLUMNS = [
        'title', 'abbreviation', 'type', 'country', 'city', 'website', 'wikiLink', 'slogan', 'startDate', 'endDate',
    ];

    public function testCreateStoresEveryFieldUnderTheLetterOfItsName(): void
    {
        $this->signInAsRegisteredUser();
        $subGroupIds = [$this->createGroup('Goldmaster Division'), $this->createGroup('Goldmaster Branch')];

        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand($this->everyField($subGroupIds)))->id;

        self::assertSame($this->everyFieldStored(), $this->storedRow(self::TABLE, $groupId, self::COLUMNS));
        self::assertSame(['structureType' => 'letter', 'structureName' => 'g'], $this->letterOf($groupId));
        self::assertSame('goldmaster-guild', $this->structureName($groupId));
        self::assertSame($this->sorted($subGroupIds), $this->linkedChildren($groupId, LinkTypes::GROUP_SUBGROUP->value));
    }

    public function testCreateLetsTheCreatorKeepEditingAndIsLogged(): void
    {
        $this->signInAsRegisteredUser();

        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild')))->id;

        self::assertSame(
            ['group.deleteAuthor', 'group.deleteFile', 'group.publicDelete', 'group.publicReceive', 'group.showPublicForm'],
            $this->allowedOnElement($groupId),
        );
        self::assertSame(['publicAdd'], $this->loggedActions($groupId));
    }

    public function testTheCreatorMayUpdateTheGroupRightAway(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild')))->id;

        $this->container->get(UpdateGroupHandler::class)->handle(new UpdateGroupCommand($groupId, new GroupFieldsDto(slogan: 'Pixels forever')));

        self::assertSame('Pixels forever', $this->storedRow(self::TABLE, $groupId, ['slogan'])['slogan']);
    }

    public function testCreateWithoutAName(): void
    {
        $this->signInAsRegisteredUser();

        try {
            $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: '')));
            self::fail('A group needs a name');
        } catch (GroupDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame([], $this->newElementIds());
    }

    public function testCreateWithAWrongReferenceWritesNothing(): void
    {
        $this->signInAsRegisteredUser();

        foreach ([
            new GroupFieldsDto(title: 'Goldmaster Guild', countryId: self::CITY_WARSAW),
            new GroupFieldsDto(title: 'Goldmaster Guild', cityId: self::COUNTRY_POLAND),
            new GroupFieldsDto(title: 'Goldmaster Guild', subGroupIds: [self::COUNTRY_POLAND]),
        ] as $fields) {
            try {
                $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand($fields));
                self::fail('A wrong reference must be rejected');
            } catch (GroupDataException $exception) {
                self::assertSame(400, $exception->getStatusCode());
            }
        }
        self::assertSame([], $this->groupRows());
        // the create runs in one transaction, so not even the structure record is left
        self::assertSame([], $this->newElementIds());
    }

    public function testCreateIsForbiddenWithoutThePrivilege(): void
    {
        $this->signIn([self::PUBLIC_VISITORS]);

        try {
            $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild')));
            self::fail('Creating must need group.publicAdd');
        } catch (GroupDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame([], $this->newElementIds());
    }

    public function testUpdateChangesTheGivenFieldsAndKeepsTheOthers(): void
    {
        $this->signInAsEditor();
        $subGroupIds = [$this->createGroup('Goldmaster Division'), $this->createGroup('Goldmaster Branch')];
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand($this->everyField($subGroupIds)))->id;

        $this->container->get(UpdateGroupHandler::class)->handle(new UpdateGroupCommand($groupId, new GroupFieldsDto(
            type: 'studio',
            countryId: self::COUNTRY_UNITED_KINGDOM,
            cityId: self::CITY_LONDON,
            endDate: $this->date('31.12.2001'),
        )));

        self::assertSame(
            array_replace($this->everyFieldStored(), [
                'type' => 'studio',
                'country' => self::COUNTRY_UNITED_KINGDOM,
                'city' => self::CITY_LONDON,
                'endDate' => $this->date('31.12.2001'),
            ]),
            $this->storedRow(self::TABLE, $groupId, self::COLUMNS),
        );
        self::assertSame($this->sorted($subGroupIds), $this->linkedChildren($groupId, LinkTypes::GROUP_SUBGROUP->value));
        self::assertSame(['publicAdd', 'publicReceive'], $this->loggedActions($groupId));
    }

    public function testUpdateClearsWithEmptyValuesAndZero(): void
    {
        $this->signInAsEditor();
        $subGroupIds = [$this->createGroup('Goldmaster Division')];
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand($this->everyField($subGroupIds)))->id;

        $this->container->get(UpdateGroupHandler::class)->handle(new UpdateGroupCommand($groupId, new GroupFieldsDto(
            abbreviation: '',
            countryId: 0,
            cityId: 0,
            website: '',
            wikiLink: '',
            slogan: '',
            startDate: 0,
            endDate: 0,
            subGroupIds: [],
        )));

        self::assertSame(
            array_replace($this->everyFieldStored(), [
                'abbreviation' => '',
                'country' => 0,
                'city' => 0,
                'website' => '',
                'wikiLink' => '',
                'slogan' => '',
                'startDate' => 0,
                'endDate' => 0,
            ]),
            $this->storedRow(self::TABLE, $groupId, self::COLUMNS),
        );
        self::assertSame([], $this->linkedChildren($groupId, LinkTypes::GROUP_SUBGROUP->value));
    }

    public function testRenameMovesTheGroupToTheLetterOfTheNewNameAndSubgroupsFollowTheList(): void
    {
        $this->signInAsEditor();
        $divisionId = $this->createGroup('Goldmaster Division');
        $branchId = $this->createGroup('Goldmaster Branch');
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild', subGroupIds: [$divisionId])))->id;

        $this->container->get(UpdateGroupHandler::class)->handle(new UpdateGroupCommand($groupId, new GroupFieldsDto(title: 'Zeta Guild', subGroupIds: [$branchId])));

        self::assertSame('Zeta Guild', $this->storedRow(self::TABLE, $groupId, ['title'])['title']);
        self::assertSame(['structureType' => 'letter', 'structureName' => 'z'], $this->letterOf($groupId));
        self::assertSame('zeta-guild', $this->structureName($groupId));
        self::assertSame([$branchId], $this->linkedChildren($groupId, LinkTypes::GROUP_SUBGROUP->value));
        self::assertSame([$groupId], $this->linkedParents($branchId, LinkTypes::GROUP_SUBGROUP->value));
        self::assertSame([], $this->linkedParents($divisionId, LinkTypes::GROUP_SUBGROUP->value));
    }

    public function testUpdateIsForbiddenWithoutThePrivilegeOnTheGroup(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = (int)$this->db->table(self::TABLE)->min('id');
        $stored = $this->db->table(self::TABLE)->where('id', '=', $groupId)->get();

        try {
            $this->container->get(UpdateGroupHandler::class)->handle(new UpdateGroupCommand($groupId, new GroupFieldsDto(slogan: 'Pixels forever')));
            self::fail('Updating must need group.publicReceive');
        } catch (GroupDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame($stored, $this->db->table(self::TABLE)->where('id', '=', $groupId)->get());
    }

    public function testCreateStoresTheMembersAndPortalIds(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $otherAuthorId = $this->createAuthor('Goldmaster Painter');

        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(
            new GroupFieldsDto(title: 'Goldmaster Guild'),
            members: [
                new AuthorshipInputDto($authorId, ['coder'], $this->date('1991-02-03'), $this->date('1995-06-07')),
                new AuthorshipInputDto($otherAuthorId),
            ],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '7001')],
        ))->id;

        self::assertSame(
            [['roles' => ['coder'], 'startDate' => $this->date('1991-02-03'), 'endDate' => $this->date('1995-06-07')]],
            $this->authorshipRecords($groupId, $authorId, 'group'),
        );
        self::assertSame([['roles' => ['unknown'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($groupId, $otherAuthorId, 'group'));
        self::assertSame([['origin' => 'zxdb', 'importId' => '7001']], $this->storedImportOrigins($groupId));
    }

    public function testUpdateReplacesTheListsGivenAndKeepsTheOnesLeftOut(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $otherAuthorId = $this->createAuthor('Goldmaster Painter');
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(
            new GroupFieldsDto(title: 'Goldmaster Guild'),
            members: [new AuthorshipInputDto($authorId, ['coder'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '7001')],
        ))->id;

        $this->container->get(UpdateGroupHandler::class)->handle(new UpdateGroupCommand($groupId, new GroupFieldsDto(), members: [new AuthorshipInputDto($otherAuthorId, ['graphician'])]));

        self::assertSame([], $this->authorshipRecords($groupId, $authorId, 'group'));
        self::assertSame([['roles' => ['graphician'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($groupId, $otherAuthorId, 'group'));
        self::assertSame([['origin' => 'zxdb', 'importId' => '7001']], $this->storedImportOrigins($groupId));

        $this->container->get(UpdateGroupHandler::class)->handle(new UpdateGroupCommand($groupId, new GroupFieldsDto(), importOrigins: []));

        self::assertSame([], $this->storedImportOrigins($groupId));
        self::assertSame([['roles' => ['graphician'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($groupId, $otherAuthorId, 'group'));
    }

    public function testAWrongMemberRefusesTheWholeUpdate(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $otherGroupId = $this->createGroup('Goldmaster Union');
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(
            new GroupFieldsDto(title: 'Goldmaster Guild'),
            members: [new AuthorshipInputDto($authorId, ['coder'])],
        ))->id;

        $this->assertRefused(400, GroupDataException::class, fn() => $this->container->get(UpdateGroupHandler::class)->handle(new UpdateGroupCommand(
            $groupId,
            new GroupFieldsDto(title: 'Zeta Guild'),
            members: [new AuthorshipInputDto($otherGroupId)],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '7001')],
        )));

        self::assertSame(['title' => 'Goldmaster Guild'], $this->storedRow(self::TABLE, $groupId, ['title']));
        self::assertSame([['roles' => ['coder'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($groupId, $authorId, 'group'));
        self::assertSame([], $this->storedImportOrigins($groupId));
    }

    public function testAWrongRoleRefusesTheCreateAndCreatesNothing(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $createdBefore = $this->newElementIds();

        $this->assertRefused(400, GroupDataException::class, fn() => $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(
            new GroupFieldsDto(title: 'Goldmaster Guild'),
            members: [new AuthorshipInputDto($authorId, ['music'])],
        )));

        self::assertSame($createdBefore, $this->newElementIds());
    }

    public function testJoinMovesTheMembersAndAliasesAndDeletesTheJoinedGroup(): void
    {
        $this->signInAsJoiner();
        $mainId = $this->createGroup('Goldmaster Guild');
        $joinedId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Union', website: 'https://union.example')))->id;
        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Union Alias', groupId: $joinedId)))->id;
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;
        $this->addAuthorship($authorId, $joinedId, 'group');

        $this->container->get(JoinGroupsHandler::class)->handle(new JoinGroupsCommand($mainId, $joinedId));

        self::assertFalse($this->elementExists($joinedId));
        self::assertContains(['authorId' => $authorId, 'elementId' => $mainId, 'type' => 'group'], $this->authorshipRows($authorId));
        self::assertSame($mainId, $this->storedRow('module_groupalias', $aliasId, ['groupId'])['groupId']);
        self::assertSame('https://union.example', $this->storedRow(self::TABLE, $mainId, ['website'])['website']);
        self::assertSame(['publicAdd', 'join'], $this->loggedActions($mainId));
    }

    public function testJoinAsAliasKeepsTheJoinedNameAsAnAliasHoldingItsMembers(): void
    {
        $this->signInAsJoiner();
        $mainId = $this->createGroup('Goldmaster Guild');
        $joinedId = $this->createGroup('Goldmaster Union');
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;
        $this->addAuthorship($authorId, $joinedId, 'group');
        $createdBefore = $this->newElementIds();

        $this->container->get(JoinGroupsHandler::class)->handle(new JoinGroupsCommand($mainId, $joinedId, true));

        self::assertFalse($this->elementExists($joinedId));
        $newIds = array_values(array_diff($this->newElementIds(), $createdBefore));
        self::assertCount(1, $newIds);
        self::assertSame(
            ['title' => 'Goldmaster Union', 'groupId' => $mainId],
            $this->storedRow('module_groupalias', $newIds[0], ['title', 'groupId']),
        );
        self::assertContains(['authorId' => $authorId, 'elementId' => $newIds[0], 'type' => 'group'], $this->authorshipRows($authorId));
    }

    public function testJoinIsForbiddenWithoutThePrivilegeAndJoinsNothing(): void
    {
        $this->signInAsRegisteredUser();
        $mainId = $this->createGroup('Goldmaster Guild');
        $joinedId = $this->createGroup('Goldmaster Union');

        try {
            $this->container->get(JoinGroupsHandler::class)->handle(new JoinGroupsCommand($mainId, $joinedId));
            self::fail('Joining must need group.join');
        } catch (GroupDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertTrue($this->elementExists($joinedId));
    }

    public function testSetMemberWritesTheRolesAndKeepsTheDatesLeftOut(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->createGroup('Goldmaster Guild');
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;
        $this->ageModificationTime($groupId);

        $this->container->get(SetGroupMemberHandler::class)->handle(new SetGroupMemberCommand($groupId, $authorId, ['coder'], $this->date('01.01.1995')));
        $this->container->get(SetGroupMemberHandler::class)->handle(new SetGroupMemberCommand($groupId, $authorId, ['musician'], endDate: $this->date('31.12.1999')));

        self::assertSame(
            [['roles' => ['musician'], 'startDate' => $this->date('01.01.1995'), 'endDate' => $this->date('31.12.1999')]],
            $this->authorshipRecords($groupId, $authorId, 'group'),
        );
        self::assertGreaterThan(1, $this->modificationTime($groupId));
    }

    public function testSetMemberWithARoleOfAProductionIsRejected(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->createGroup('Goldmaster Guild');
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;

        try {
            $this->container->get(SetGroupMemberHandler::class)->handle(new SetGroupMemberCommand($groupId, $authorId, ['code']));
            self::fail('"code" is a production role');
        } catch (GroupDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame([], $this->authorshipRecords($groupId, $authorId, 'group'));
    }

    public function testSearchFindsTheGroupByAbbreviationAndAlias(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild', abbreviation: 'GMGLD')))->id;
        $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Goldmaster Union', groupId: $groupId)));
        $find = $this->finder();

        self::assertContains($groupId, $find(new GroupSearchFilterDto(name: 'gmgld')));
        self::assertContains($groupId, $find(new GroupSearchFilterDto(name: 'GOLDMASTER UNION')));
    }

    public function testSearchFindsTheGroupsByPlaceDatesMembersSubgroupsAndTheirSoftware(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['group', 'publicReceive']]);
        $divisionId = $this->createGroup('Goldmaster Division');
        $guildId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(
            title: 'Goldmaster Guild',
            type: 'scene',
            countryId: self::COUNTRY_POLAND,
            cityId: self::CITY_WARSAW,
            startDate: $this->date('15.03.1995'),
            subGroupIds: [$divisionId],
        )))->id;
        $crewId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Goldmaster Crew', groupId: $guildId)))->id;
        $labelId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Label', type: 'studio')))->id;
        $authorId = $this->createAuthor('Goldmaster Coder');
        $this->container->get(SetGroupMemberHandler::class)->handle(new SetGroupMemberCommand($divisionId, $authorId, ['coder']));
        $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Demo', year: 1990, groupIds: [$crewId], publisherIds: [$labelId])));
        $gameId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Game', year: 1997, groupIds: [$labelId])))->id;
        $find = $this->finder();

        self::assertSame([$guildId, $labelId], $find(new GroupSearchFilterDto(name: 'goldmaster', prods: new ProdSearchFilterDto(title: 'goldmaster demo'))));
        self::assertSame([$guildId], $find(new GroupSearchFilterDto(name: 'goldmaster', prodLink: ProdLink::Made, prods: new ProdSearchFilterDto(title: 'goldmaster demo'))));
        self::assertSame([$labelId], $find(new GroupSearchFilterDto(name: 'goldmaster', prodIds: [$gameId])));
        self::assertSame([$labelId], $find(new GroupSearchFilterDto(name: 'goldmaster', prodLink: ProdLink::Published)));
        self::assertSame([$labelId], $find(new GroupSearchFilterDto(name: 'goldmaster', activeFrom: 1995)));
        self::assertSame([$guildId], $find(new GroupSearchFilterDto(name: 'goldmaster', foundedFrom: $this->date('01.01.1995'))));
        self::assertSame([$guildId], $find(new GroupSearchFilterDto(name: 'goldmaster', types: ['scene'], cityIds: [self::CITY_WARSAW])));
        self::assertSame([$divisionId], $find(new GroupSearchFilterDto(memberIds: [$authorId])));
        self::assertSame([$divisionId], $find(new GroupSearchFilterDto(parentGroupId: $guildId)));
        self::assertSame([$guildId], $find(new GroupSearchFilterDto(subGroupId: $divisionId)));
    }

    /**
     * @return Closure(GroupSearchFilterDto): list<int>
     */
    private function finder(): Closure
    {
        $search = $this->container->get(GroupSearchService::class);

        return static fn(GroupSearchFilterDto $filter): array => $search->search($filter, 0, 50)->ids;
    }

    private function signInAsRegisteredUser(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
    }

    /**
     * A registered user who may also edit every group.
     */
    private function signInAsEditor(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['group', 'publicReceive']]);
    }

    /**
     * A registered user who may also join any groups.
     */
    private function signInAsJoiner(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['group', 'join']]);
    }

    private function createAuthor(string $title): int
    {
        return $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: $title)))->id;
    }

    private function createGroup(string $title): int
    {
        return $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: $title)))->id;
    }

    /**
     * @param list<int> $subGroupIds
     */
    private function everyField(array $subGroupIds): GroupFieldsDto
    {
        return new GroupFieldsDto(
            title: 'Goldmaster Guild',
            abbreviation: 'GMG',
            type: 'scene',
            countryId: self::COUNTRY_POLAND,
            cityId: self::CITY_WARSAW,
            website: 'https://guild.invalid/',
            wikiLink: 'https://wiki.invalid/guild',
            slogan: 'Pixels and noise',
            startDate: $this->date('15.03.1995'),
            endDate: $this->date('01.06.1999'),
            subGroupIds: $subGroupIds,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function everyFieldStored(): array
    {
        return [
            'title' => 'Goldmaster Guild',
            'abbreviation' => 'GMG',
            'type' => 'scene',
            'country' => self::COUNTRY_POLAND,
            'city' => self::CITY_WARSAW,
            'website' => 'https://guild.invalid/',
            'wikiLink' => 'https://wiki.invalid/guild',
            'slogan' => 'Pixels and noise',
            'startDate' => $this->date('15.03.1995'),
            'endDate' => $this->date('01.06.1999'),
        ];
    }

    /**
     * @param list<int> $ids
     * @return list<int>
     */
    private function sorted(array $ids): array
    {
        sort($ids);

        return $ids;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function groupRows(): array
    {
        $newIds = $this->newElementIds();

        return $newIds === [] ? [] : $this->db->table(self::TABLE)->whereIn('id', $newIds)->get();
    }
}
