<?php

declare(strict_types=1);

namespace ZxArt\Radio\Services;

use ZxArt\Radio\Dto\RadioCriteriaDto;

readonly class RadioCriteriaFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public function fromArray(array $data): RadioCriteriaDto
    {
        return new RadioCriteriaDto(
            minRating: $this->normalizeOptionalFloat($data['minRating'] ?? null),
            maxRating: $this->normalizeOptionalFloat($data['maxRating'] ?? null),
            yearsInclude: $this->normalizeIntList($data['yearsInclude'] ?? []),
            yearsExclude: $this->normalizeIntList($data['yearsExclude'] ?? []),
            countriesInclude: $this->normalizeIntList($data['countriesInclude'] ?? []),
            countriesExclude: $this->normalizeIntList($data['countriesExclude'] ?? []),
            formatGroupsInclude: $this->normalizeStringList($data['formatGroupsInclude'] ?? []),
            formatGroupsExclude: $this->normalizeStringList($data['formatGroupsExclude'] ?? []),
            formatsInclude: $this->normalizeStringList($data['formatsInclude'] ?? []),
            formatsExclude: $this->normalizeStringList($data['formatsExclude'] ?? []),
            bestVotesLimit: $this->normalizeOptionalInt($data['bestVotesLimit'] ?? null),
            maxPlays: $this->normalizeOptionalInt($data['maxPlays'] ?? null),
            minPartyPlace: $this->normalizeOptionalInt($data['minPartyPlace'] ?? null),
            requireGame: $this->normalizeOptionalBool($data['requireGame'] ?? null),
            hasParty: $this->normalizeOptionalBool($data['hasParty'] ?? null),
            notVotedByUserId: $this->normalizeOptionalInt($data['notVotedByUserId'] ?? null),
        );
    }

    /**
     * @return int[]
     */
    private function normalizeIntList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            $intValue = filter_var($item, FILTER_VALIDATE_INT);
            if ($intValue !== false) {
                $result[] = (int)$intValue;
            }
        }
        return $result;
    }

    /**
     * @return string[]
     */
    private function normalizeStringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (!is_string($item)) {
                continue;
            }
            $trimmed = trim($item);
            if ($trimmed !== '') {
                $result[] = $trimmed;
            }
        }
        return $result;
    }

    private function normalizeOptionalInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }
        $intValue = filter_var($value, FILTER_VALIDATE_INT);
        return $intValue === false ? null : (int)$intValue;
    }

    private function normalizeOptionalFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }
        $floatValue = filter_var($value, FILTER_VALIDATE_FLOAT);
        return $floatValue === false ? null : (float)$floatValue;
    }

    private function normalizeOptionalBool(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
