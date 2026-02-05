<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Repositories;

use Illuminate\Database\Connection;
use ZxArt\UserPreferences\Domain\Preference;
use ZxArt\UserPreferences\Domain\PreferenceCode;

final readonly class PreferencesRepository
{
    private const TABLE = 'preferences';

    public function __construct(
        private Connection $db,
    ) {
    }

    public function findByCode(PreferenceCode $code): ?Preference
    {
        $row = $this->db->table(self::TABLE)
            ->where('code', $code->value)
            ->first();

        if ($row === null || $row === []) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * @return Preference[]
     */
    public function findAll(): array
    {
        $rows = $this->db->table(self::TABLE)->get();

        $preferences = [];
        foreach ($rows as $row) {
            $preferenceCode = PreferenceCode::tryFrom($row['code'] ?? '');
            if ($preferenceCode !== null) {
                $preferences[] = $this->hydrate($row);
            }
        }

        return $preferences;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Preference
    {
        return new Preference(
            id: (int)$row['id'],
            code: PreferenceCode::from($row['code']),
            type: $row['type'],
        );
    }
}
