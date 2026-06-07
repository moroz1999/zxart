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
     * Release types that must not appear in a group's "releases & cracks" listing:
     * an "original" release is the prod's own first release and a "demoversion" is a
     * demo, neither is a group re-release or crack.
     *
     * @return string[]
     */
    public static function getGroupExcludedValues(): array
    {
        return [self::original->value, self::demoversion->value];
    }
}
