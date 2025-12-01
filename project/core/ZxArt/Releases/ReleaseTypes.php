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
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
