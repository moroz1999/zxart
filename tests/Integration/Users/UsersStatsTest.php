<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Users;

require_once __DIR__ . '/UserManagementTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Users\Dto\MonthlyCountDto;
use ZxArt\Users\Dto\UsersStatsDto;
use ZxArt\Users\UsersStatsService;

/**
 * The figures of the users dashboard. The local database holds real users,
 * so each test compares the figures before and after the users it adds.
 */
#[RunTestsInSeparateProcesses]
final class UsersStatsTest extends UserManagementTestCase
{
    private const int DAY = 86400;

    public function testEveryUserIsCountedAndTheRecentOnesByPeriod(): void
    {
        $before = $this->stats();
        $this->createUser('zxmgmt-today', 'today@zxmgmt.example', time() - 60);
        $this->createUser('zxmgmt-fortnight', 'fortnight@zxmgmt.example', time() - 14 * self::DAY);
        $this->createUser('zxmgmt-old', 'old@zxmgmt.example', time() - 400 * self::DAY);

        $after = $this->stats();

        self::assertSame($before->total + 3, $after->total);
        self::assertSame($before->newLastWeek + 1, $after->newLastWeek);
        self::assertSame($before->newLastMonth + 2, $after->newLastMonth);
    }

    public function testBannedAndUnverifiedUsersAreCounted(): void
    {
        $before = $this->stats();
        $this->createUser('zxmgmt-banned', 'banned@zxmgmt.example', time() - 60, banned: true);
        $this->createUser('zxmgmt-unverified', 'unverified@zxmgmt.example', time() - 60, verified: false);

        $after = $this->stats();

        self::assertSame($before->banned + 1, $after->banned);
        self::assertSame($before->unverified + 1, $after->unverified);
    }

    public function testRegistrationsAreCountedPerMonthForTheLastYearOldestFirst(): void
    {
        $before = $this->stats();
        $this->createUser('zxmgmt-now', 'now@zxmgmt.example', time() - 60);

        $after = $this->stats();

        self::assertCount(12, $after->registrationsByMonth);
        $currentMonth = date('Y-m');
        self::assertSame($currentMonth, $after->registrationsByMonth[11]->month);
        self::assertSame(date('Y-m', strtotime('first day of -11 months')), $after->registrationsByMonth[0]->month);
        self::assertSame(
            $this->monthCount($before->registrationsByMonth, $currentMonth) + 1,
            $this->monthCount($after->registrationsByMonth, $currentMonth),
        );
    }

    private function stats(): UsersStatsDto
    {
        return $this->container->get(UsersStatsService::class)->getStats();
    }

    /**
     * @param list<MonthlyCountDto> $months
     */
    private function monthCount(array $months, string $month): int
    {
        foreach ($months as $monthlyCount) {
            if ($monthlyCount->month === $month) {
                return $monthlyCount->count;
            }
        }
        self::fail('No count for ' . $month);
    }
}
