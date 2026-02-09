<?php

declare(strict_types=1);

namespace ZxArt\Radio\Services;

use App\Users\CurrentUser;
use ConfigManager;
use DateTimeImmutable;
use ZxArt\Radio\Domain\RadioPreset;
use ZxArt\Radio\Dto\RadioCriteriaDto;

readonly class RadioCriteriaFactory
{
    private const float MIN_RATING_OFFSET = 0.2;
    private const int DEMOSCENE_MAX_PARTY_PLACE = 1000;
    private const int DISCOVER_BEST_VOTES = 100;
    private const int UNDERGROUND_BEST_VOTES = 500;
    private const int UNDERGROUND_MAX_PLAYS = 10;

    public function __construct(
        private ConfigManager $configManager,
        private CurrentUser $currentUser,
    ) {
    }

    public function fromPreset(RadioPreset $preset): RadioCriteriaDto
    {
        $minRating = $this->getAboveAverageRating();
        $userId = (int)$this->currentUser->id;
        $currentDate = new DateTimeImmutable();
        $currentYear = (int)$currentDate->format('Y');
        $currentMonth = (int)$currentDate->format('m');

        return match ($preset) {
            RadioPreset::RANDOM_GOOD => $this->createCriteria(minRating: $minRating),
            RadioPreset::GAMES => $this->createCriteria(minRating: $minRating, requireGame: true),
            RadioPreset::DEMOSCENE => $this->createCriteria(
                minRating: $minRating,
                minPartyPlace: self::DEMOSCENE_MAX_PARTY_PLACE,
            ),
            RadioPreset::AY => $this->createCriteria(
                minRating: $minRating,
                formatGroupsInclude: ['ay', 'aycovox', 'aydigitalay', 'ts'],
            ),
            RadioPreset::BEEPER => $this->createCriteria(
                minRating: $minRating,
                formatGroupsInclude: ['beeper', 'aybeeper'],
            ),
            RadioPreset::EXOTIC => $this->createCriteria(
                minRating: $minRating,
                formatGroupsInclude: ['digitalbeeper', 'tsfm', 'fm', 'digitalay', 'saa'],
            ),
            RadioPreset::DISCOVER => $this->createCriteria(
                notVotedByUserId: $userId,
                bestVotesLimit: self::DISCOVER_BEST_VOTES,
            ),
            RadioPreset::UNDERGROUND => $this->createCriteria(
                maxPlays: self::UNDERGROUND_MAX_PLAYS,
                bestVotesLimit: self::UNDERGROUND_BEST_VOTES,
            ),
            RadioPreset::LAST_YEAR => $this->createCriteria(
                minRating: $minRating,
                yearsInclude: $currentMonth < 3 ? [$currentYear - 1, $currentYear] : [$currentYear],
            ),
        };
    }

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
            notVotedByUserId: $this->normalizeOptionalInt($data['notVotedByUserId'] ?? null),
        );
    }

    private function getAboveAverageRating(): float
    {
        $averageVote = (float)$this->configManager->get('zx.averageVote');
        return $averageVote + self::MIN_RATING_OFFSET;
    }

    private function createCriteria(
        ?float $minRating = null,
        ?float $maxRating = null,
        array $yearsInclude = [],
        array $yearsExclude = [],
        array $countriesInclude = [],
        array $countriesExclude = [],
        array $formatGroupsInclude = [],
        array $formatGroupsExclude = [],
        array $formatsInclude = [],
        array $formatsExclude = [],
        ?int $bestVotesLimit = null,
        ?int $maxPlays = null,
        ?int $minPartyPlace = null,
        ?bool $requireGame = null,
        ?int $notVotedByUserId = null,
    ): RadioCriteriaDto {
        return new RadioCriteriaDto(
            minRating: $minRating,
            maxRating: $maxRating,
            yearsInclude: $yearsInclude,
            yearsExclude: $yearsExclude,
            countriesInclude: $countriesInclude,
            countriesExclude: $countriesExclude,
            formatGroupsInclude: $formatGroupsInclude,
            formatGroupsExclude: $formatGroupsExclude,
            formatsInclude: $formatsInclude,
            formatsExclude: $formatsExclude,
            bestVotesLimit: $bestVotesLimit,
            maxPlays: $maxPlays,
            minPartyPlace: $minPartyPlace,
            requireGame: $requireGame,
            notVotedByUserId: $notVotedByUserId,
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
