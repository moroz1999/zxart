<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Groups\Dbal\GroupSearchRepository;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Services\GroupAliasDataService;
use ZxArt\Groups\Services\GroupDataService;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Import\ImportOrigin;
use ZxArt\LinkTypes;
use ZxArt\Groups\Services\GroupFieldsReader;
use groupElement;

/**
 * Groups written through {@see GroupDataService}.
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

        $groupId = $this->groupDataService()->create($this->everyField($subGroupIds))->id;

        self::assertSame($this->everyFieldStored(), $this->storedRow(self::TABLE, $groupId, self::COLUMNS));
        self::assertSame(['structureType' => 'letter', 'structureName' => 'g'], $this->letterOf($groupId));
        self::assertSame('goldmaster-guild', $this->structureName($groupId));
        self::assertSame($this->sorted($subGroupIds), $this->linkedChildren($groupId, LinkTypes::GROUP_SUBGROUP->value));
    }

    public function testCreateLetsTheCreatorKeepEditingAndIsLogged(): void
    {
        $this->signInAsRegisteredUser();

        $groupId = $this->groupDataService()->create(new GroupFieldsDto(title: 'Goldmaster Guild'))->id;

        self::assertSame(
            ['group.deleteAuthor', 'group.deleteFile', 'group.publicDelete', 'group.publicReceive', 'group.showPublicForm'],
            $this->allowedOnElement($groupId),
        );
        self::assertSame(['publicAdd'], $this->loggedActions($groupId));
    }

    public function testTheCreatorMayUpdateTheGroupRightAway(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->groupDataService()->create(new GroupFieldsDto(title: 'Goldmaster Guild'))->id;

        $this->groupDataService()->update($groupId, new GroupFieldsDto(slogan: 'Pixels forever'));

        self::assertSame('Pixels forever', $this->storedRow(self::TABLE, $groupId, ['slogan'])['slogan']);
    }

    public function testCreateWithoutAName(): void
    {
        $this->signInAsRegisteredUser();

        try {
            $this->groupDataService()->create(new GroupFieldsDto(title: ''));
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
                $this->groupDataService()->create($fields);
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
            $this->groupDataService()->create(new GroupFieldsDto(title: 'Goldmaster Guild'));
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
        $groupId = $this->groupDataService()->create($this->everyField($subGroupIds))->id;

        $this->groupDataService()->update($groupId, new GroupFieldsDto(
            type: 'studio',
            countryId: self::COUNTRY_UNITED_KINGDOM,
            cityId: self::CITY_LONDON,
            endDate: $this->date('31.12.2001'),
        ));

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
        $groupId = $this->groupDataService()->create($this->everyField($subGroupIds))->id;

        $this->groupDataService()->update($groupId, new GroupFieldsDto(
            abbreviation: '',
            countryId: 0,
            cityId: 0,
            website: '',
            wikiLink: '',
            slogan: '',
            startDate: 0,
            endDate: 0,
            subGroupIds: [],
        ));

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
        $groupId = $this->groupDataService()->create(new GroupFieldsDto(title: 'Goldmaster Guild', subGroupIds: [$divisionId]))->id;

        $this->groupDataService()->update($groupId, new GroupFieldsDto(title: 'Zeta Guild', subGroupIds: [$branchId]));

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
            $this->groupDataService()->update($groupId, new GroupFieldsDto(slogan: 'Pixels forever'));
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

        $groupId = $this->groupDataService()->create(
            new GroupFieldsDto(title: 'Goldmaster Guild'),
            members: [
                new AuthorshipInputDto($authorId, ['coder'], $this->date('1991-02-03'), $this->date('1995-06-07')),
                new AuthorshipInputDto($otherAuthorId),
            ],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '7001')],
        )->id;

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
        $groupId = $this->groupDataService()->create(
            new GroupFieldsDto(title: 'Goldmaster Guild'),
            members: [new AuthorshipInputDto($authorId, ['coder'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '7001')],
        )->id;

        $this->groupDataService()->update($groupId, new GroupFieldsDto(), members: [new AuthorshipInputDto($otherAuthorId, ['graphician'])]);

        self::assertSame([], $this->authorshipRecords($groupId, $authorId, 'group'));
        self::assertSame([['roles' => ['graphician'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($groupId, $otherAuthorId, 'group'));
        self::assertSame([['origin' => 'zxdb', 'importId' => '7001']], $this->storedImportOrigins($groupId));

        $this->groupDataService()->update($groupId, new GroupFieldsDto(), importOrigins: []);

        self::assertSame([], $this->storedImportOrigins($groupId));
        self::assertSame([['roles' => ['graphician'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($groupId, $otherAuthorId, 'group'));
    }

    public function testAWrongMemberRefusesTheWholeUpdate(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $otherGroupId = $this->createGroup('Goldmaster Union');
        $groupId = $this->groupDataService()->create(
            new GroupFieldsDto(title: 'Goldmaster Guild'),
            members: [new AuthorshipInputDto($authorId, ['coder'])],
        )->id;

        $this->assertRefused(400, GroupDataException::class, fn() => $this->groupDataService()->update(
            $groupId,
            new GroupFieldsDto(title: 'Zeta Guild'),
            members: [new AuthorshipInputDto($otherGroupId)],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '7001')],
        ));

        self::assertSame(['title' => 'Goldmaster Guild'], $this->storedRow(self::TABLE, $groupId, ['title']));
        self::assertSame([['roles' => ['coder'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($groupId, $authorId, 'group'));
        self::assertSame([], $this->storedImportOrigins($groupId));
    }

    public function testAWrongRoleRefusesTheCreateAndCreatesNothing(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $createdBefore = $this->newElementIds();

        $this->assertRefused(400, GroupDataException::class, fn() => $this->groupDataService()->create(
            new GroupFieldsDto(title: 'Goldmaster Guild'),
            members: [new AuthorshipInputDto($authorId, ['music'])],
        ));

        self::assertSame($createdBefore, $this->newElementIds());
    }

    public function testJoinMovesTheMembersAndAliasesAndDeletesTheJoinedGroup(): void
    {
        $this->signInAsJoiner();
        $mainId = $this->createGroup('Goldmaster Guild');
        $joinedId = $this->groupDataService()->create(new GroupFieldsDto(title: 'Goldmaster Union', website: 'https://union.example'))->id;
        $aliasId = $this->container->get(GroupAliasDataService::class)
            ->create(new GroupAliasFieldsDto(title: 'Union Alias', groupId: $joinedId))->id;
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $this->addAuthorship($authorId, $joinedId, 'group');

        $this->groupDataService()->join($mainId, $joinedId);

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
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $this->addAuthorship($authorId, $joinedId, 'group');
        $createdBefore = $this->newElementIds();

        $this->groupDataService()->join($mainId, $joinedId, true);

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
            $this->groupDataService()->join($mainId, $joinedId);
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
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $this->ageModificationTime($groupId);

        $this->groupDataService()->setMember($groupId, $authorId, ['coder'], $this->date('01.01.1995'));
        $this->groupDataService()->setMember($groupId, $authorId, ['musician'], endDate: $this->date('31.12.1999'));

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
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;

        try {
            $this->groupDataService()->setMember($groupId, $authorId, ['code']);
            self::fail('"code" is a production role');
        } catch (GroupDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame([], $this->authorshipRecords($groupId, $authorId, 'group'));
    }

    public function testSearchFindsTheGroupByAbbreviationAndAlias(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->groupDataService()->create(new GroupFieldsDto(title: 'Goldmaster Guild', abbreviation: 'GMGLD'))->id;
        $this->container->get(GroupAliasDataService::class)->create(new GroupAliasFieldsDto(title: 'Goldmaster Union', groupId: $groupId));
        $search = $this->container->get(GroupSearchRepository::class);

        self::assertContains($groupId, $search->findIds('gmgld', null, null, null, 0, 50));
        self::assertContains($groupId, $search->findIds('GOLDMASTER UNION', null, null, null, 0, 50));
    }

    public function testReadAnswersWhatCreateAndUpdateWrote(): void
    {
        $this->signInAsEditor();
        $subGroupIds = [$this->createGroup('Goldmaster Division'), $this->createGroup('Goldmaster Branch')];
        $groupId = $this->groupDataService()->create($this->everyField($subGroupIds))->id;
        $reader = $this->container->get(GroupFieldsReader::class);

        self::assertEquals($this->everyField($subGroupIds), $reader->read($this->element($groupId)));

        $this->groupDataService()->update($groupId, new GroupFieldsDto(type: 'studio', startDate: 0, subGroupIds: [$subGroupIds[1]]));

        self::assertEquals(
            new GroupFieldsDto(...array_replace(get_object_vars($this->everyField($subGroupIds)), ['type' => 'studio', 'startDate' => 0, 'subGroupIds' => [$subGroupIds[1]]])),
            $reader->read($this->element($groupId)),
        );
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
        return $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: $title))->id;
    }

    private function createGroup(string $title): int
    {
        return $this->groupDataService()->create(new GroupFieldsDto(title: $title))->id;
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

    private function element(int $elementId): groupElement
    {
        $element = $this->structureManager->getElementById($elementId);
        self::assertInstanceOf(groupElement::class, $element);

        return $element;
    }

    private function groupDataService(): GroupDataService
    {
        return $this->container->get(GroupDataService::class);
    }
}
