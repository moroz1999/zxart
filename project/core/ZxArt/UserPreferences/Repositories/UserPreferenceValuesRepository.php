<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Repositories;

use Illuminate\Database\Connection;
use ZxArt\UserPreferences\Domain\UserPreferenceValue;

final readonly class UserPreferenceValuesRepository
{
    private const TABLE = 'user_preference_values';

    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @return UserPreferenceValue[]
     */
    public function findByUserId(int $userId): array
    {
        $rows = $this->db->table(self::TABLE)
            ->where('user_id', $userId)
            ->get();

        $values = [];
        foreach ($rows as $row) {
            $rowArray = (array)$row;
            $values[] = new UserPreferenceValue(
                userId: $rowArray['user_id'],
                preferenceId: $rowArray['preference_id'],
                value: $rowArray['value'],
            );
        }

        return $values;
    }

    public function upsert(int $userId, int $preferenceId, string $value): void
    {
        $this->db->table(self::TABLE)->updateOrInsert(
            ['user_id' => $userId, 'preference_id' => $preferenceId],
            ['value' => $value]
        );
    }
}
