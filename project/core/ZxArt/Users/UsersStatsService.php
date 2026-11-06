<?php

declare(strict_types=1);

namespace ZxArt\Users;

use DateTimeImmutable;
use ZxArt\Users\Dbal\UsersStatsDbal;
use ZxArt\Users\Dto\MonthlyCountDto;
use ZxArt\Users\Dto\UsersStatsDto;

/**
 * The figures of the users dashboard.
 *
 * @psalm-api
 */
final readonly class UsersStatsService
{
    private const int MONTHS = 12;

    public function __construct(
        private UsersStatsDbal $usersStatsDbal,
    ) {
    }

    public function getStats(): UsersStatsDto
    {
        $now = new DateTimeImmutable();

        return new UsersStatsDto(
            total: $this->usersStatsDbal->countAll(),
            newLastWeek: $this->usersStatsDbal->countRegisteredSince($now->modify('-7 days')->getTimestamp()),
            newLastMonth: $this->usersStatsDbal->countRegisteredSince($now->modify('-30 days')->getTimestamp()),
            banned: $this->usersStatsDbal->countBanned(),
            unverified: $this->usersStatsDbal->countUnverified(),
            registrationsByMonth: $this->registrationsByMonth($now),
        );
    }

    /**
     * Every one of the last twelve months, the current one included, oldest
     * first; a month nobody registered in counts 0.
     *
     * @return list<MonthlyCountDto>
     */
    private function registrationsByMonth(DateTimeImmutable $now): array
    {
        $firstMonth = $now->modify('first day of this month')->setTime(0, 0)->modify('-' . (self::MONTHS - 1) . ' months');
        $counts = $this->usersStatsDbal->countRegistrationsByMonthSince($firstMonth->getTimestamp());

        $months = [];
        for ($index = 0; $index < self::MONTHS; $index++) {
            $month = $firstMonth->modify('+' . $index . ' months')->format('Y-m');
            $months[] = new MonthlyCountDto(month: $month, count: $counts[$month] ?? 0);
        }

        return $months;
    }
}
