<?php

declare(strict_types=1);

namespace ZxArt\Import;

/**
 * External portal an entity was imported from. Together with the portal's own
 * id it is stored per element and turned into the outgoing link on the entity
 * page.
 */
enum ImportOrigin: string
{
    case Zxdb = 'zxdb';
    case Vtrdos = 'vt';
    case Pouet = 'pouet';
    case Demozoo = 'dzoo';
    case Demotopia = 'zxd';
    case Zxaaa = '3a';
    case SpeccyMaps = 'maps';
    case Spectrum4Ever = 's4e';
    case WorldOfSam = 'worldofsam';
    case TsLabs = 'tsl';
    case ZxPress = 'zxp';
    case RzxArchive = 'rzx';

    /** Portal name, as it is offered in the editing form. */
    public function title(): string
    {
        return match ($this) {
            self::Zxdb => 'Spectrum Computing',
            self::Vtrdos => 'Virtual TR-DOS',
            self::Pouet => 'Pouet.net',
            self::Demozoo => 'Demozoo',
            self::Demotopia => 'Demotopia',
            self::Zxaaa => 'zxaaa.net',
            self::SpeccyMaps => 'Speccy Screenshot Maps',
            self::Spectrum4Ever => 'spectrum4ever.org',
            self::WorldOfSam => 'World Of Sam',
            self::TsLabs => 'TS-Labs',
            self::ZxPress => 'ZX Press',
            self::RzxArchive => 'The RZX Archive',
        };
    }
}
