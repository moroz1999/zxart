<?php

declare(strict_types=1);

namespace ZxArt\Releases;

enum ReleaseTypes: string
{
    case unknown = 'unknown';
    case original = 'original';
    case rerelease = 'rerelease';
    case adaptation = 'adaptation';
    case localization = 'localization';
    case bugfix = 'bugfix';
    case mod = 'mod';
    case crack = 'crack';
    case mia = 'mia';
    case corrupted = 'corrupted';
    case compilation = 'compilation';
    case incomplete = 'incomplete';
    case demoversion = 'demoversion';

    /**
     * @return array<string>
     */
    public static function getAllValues(): array
    {
        return array_map(static fn(self $case) => $case->value, self::cases());
    }

    /**
     * Group "releases & cracks" is a hacker-release list, not a generic list of
     * published releases.
     *
     * @return string[]
     */
    public static function getGroupHackerValues(): array
    {
        return [
            self::adaptation->value,
            self::localization->value,
            self::bugfix->value,
            self::mod->value,
            self::crack->value,
        ];
    }

    /**
     * Non-hacker release publisher links are publication events, so group pages
     * show them in "Published" instead of "Releases & cracks".
     *
     * @return string[]
     */
    public static function getGroupPublishedValues(): array
    {
        return array_values(array_filter(
            self::getAllValues(),
            static fn(string $value): bool => !in_array($value, self::getGroupHackerValues(), true),
        ));
    }
}
