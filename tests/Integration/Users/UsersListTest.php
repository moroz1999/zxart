<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Users;

require_once __DIR__ . '/UserManagementTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Shared\SortDirection;
use ZxArt\Users\Dto\UserListItemDto;
use ZxArt\Users\Dto\UsersListQueryDto;
use ZxArt\Users\UsersListService;
use ZxArt\Users\UsersListSort;
use ZxArt\Users\UserStatusFilter;

/**
 * The users list of the management screen: searched, filtered, sorted and
 * paged in the database.
 */
#[RunTestsInSeparateProcesses]
final class UsersListTest extends UserManagementTestCase
{
    private const string PREFIX = 'zxmgmt-';

    public function testTheNewestUsersComeFirstByDefault(): void
    {
        $older = $this->createUser(self::PREFIX . 'older', 'older@zxmgmt.example', time() + 1000);
        $newer = $this->createUser(self::PREFIX . 'newer', 'newer@zxmgmt.example', time() + 2000);

        $list = $this->service()->getList(new UsersListQueryDto(pageSize: 2));

        self::assertSame([$newer, $older], $this->ids($list->users));
    }

    public function testTheSearchMatchesPartOfTheNameOrOfTheEmail(): void
    {
        $byName = $this->createUser(self::PREFIX . 'goldfinch', 'first@zxmgmt.example', time() + 1000);
        $byEmail = $this->createUser(self::PREFIX . 'other', 'goldfinch@zxmgmt.example', time() + 2000);
        $this->createUser(self::PREFIX . 'nobody', 'nobody@zxmgmt.example', time() + 3000);

        $list = $this->service()->getList(new UsersListQueryDto(search: 'ldfin'));

        self::assertSame([$byEmail, $byName], $this->ids($list->users));
        self::assertSame(2, $list->total);
    }

    public function testTheSearchTakesPercentAndUnderscoreLiterally(): void
    {
        $this->createUser(self::PREFIX . 'plain', 'plain@zxmgmt.example', time() + 1000);
        $literal = $this->createUser(self::PREFIX . 'under_score', 'under@zxmgmt.example', time() + 2000);

        $list = $this->service()->getList(new UsersListQueryDto(search: self::PREFIX . '%_'));

        self::assertSame([], $this->ids($list->users));
        $list = $this->service()->getList(new UsersListQueryDto(search: 'r_s'));
        self::assertSame([$literal], $this->ids($list->users));
    }

    public function testTheGroupFilterKeepsOnlyItsMembers(): void
    {
        $groupId = $this->createGroup(self::PREFIX . 'group');
        $member = $this->createUser(self::PREFIX . 'member', 'member@zxmgmt.example', time() + 1000);
        $this->createUser(self::PREFIX . 'outsider', 'outsider@zxmgmt.example', time() + 2000);
        $this->addToGroup($member, $groupId);

        $list = $this->service()->getList(new UsersListQueryDto(groupId: $groupId));

        self::assertSame([$member], $this->ids($list->users));
        self::assertSame([$groupId], $list->users[0]->groupIds);
    }

    public function testTheStatusFilterSeparatesBannedUsers(): void
    {
        $banned = $this->createUser(self::PREFIX . 'banned', 'banned@zxmgmt.example', time() + 1000, banned: true);
        $active = $this->createUser(self::PREFIX . 'active', 'active@zxmgmt.example', time() + 2000);

        $bannedList = $this->service()->getList(new UsersListQueryDto(search: self::PREFIX, status: UserStatusFilter::Banned));
        $activeList = $this->service()->getList(new UsersListQueryDto(search: self::PREFIX, status: UserStatusFilter::Active));

        self::assertSame([$banned], $this->ids($bannedList->users));
        self::assertSame([$active], $this->ids($activeList->users));
    }

    public function testTheListSortsByNameInTheAskedOrder(): void
    {
        $bravo = $this->createUser(self::PREFIX . 'bravo', 'b@zxmgmt.example', time() + 1000);
        $alpha = $this->createUser(self::PREFIX . 'alpha', 'a@zxmgmt.example', time() + 2000);
        $charlie = $this->createUser(self::PREFIX . 'charlie', 'c@zxmgmt.example', time() + 3000);

        $ascending = $this->service()->getList(new UsersListQueryDto(search: self::PREFIX, sort: UsersListSort::UserName, direction: SortDirection::ASC));
        $descending = $this->service()->getList(new UsersListQueryDto(search: self::PREFIX, sort: UsersListSort::UserName, direction: SortDirection::DESC));

        self::assertSame([$alpha, $bravo, $charlie], $this->ids($ascending->users));
        self::assertSame([$charlie, $bravo, $alpha], $this->ids($descending->users));
    }

    public function testAPageHoldsItsShareAndTheTotalCountsEveryMatch(): void
    {
        $ids = [];
        for ($index = 1; $index <= 5; $index++) {
            $ids[] = $this->createUser(self::PREFIX . 'paged' . $index, 'paged' . $index . '@zxmgmt.example', time() + 1000 * $index);
        }

        $list = $this->service()->getList(new UsersListQueryDto(search: self::PREFIX . 'paged', page: 2, pageSize: 2));

        self::assertSame([$ids[2], $ids[1]], $this->ids($list->users));
        self::assertSame(5, $list->total);
    }

    public function testAnItemCarriesWhatTheListShows(): void
    {
        $dateCreated = time() + 1000;
        $userId = $this->createUser(self::PREFIX . 'shown', 'shown@zxmgmt.example', $dateCreated, banned: true, verified: false);

        $item = $this->service()->getList(new UsersListQueryDto(search: self::PREFIX . 'shown'))->users[0];

        self::assertEquals(
            new UserListItemDto(
                id: $userId,
                userName: self::PREFIX . 'shown',
                email: 'shown@zxmgmt.example',
                dateCreated: $dateCreated,
                verified: false,
                banned: true,
                groupIds: [],
            ),
            $item,
        );
    }

    private function service(): UsersListService
    {
        return $this->container->get(UsersListService::class);
    }

    /**
     * @param list<UserListItemDto> $users
     * @return list<int>
     */
    private function ids(array $users): array
    {
        return array_map(static fn(UserListItemDto $user): int => $user->id, $users);
    }
}
