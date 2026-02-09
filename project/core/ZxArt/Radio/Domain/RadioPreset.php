<?php

declare(strict_types=1);

namespace ZxArt\Radio\Domain;

enum RadioPreset: string
{
    case DISCOVER = 'discover';
    case RANDOM_GOOD = 'randomgood';
    case GAMES = 'games';
    case DEMOSCENE = 'demoscene';
    case LAST_YEAR = 'lastyear';
    case AY = 'ay';
    case BEEPER = 'beeper';
    case EXOTIC = 'exotic';
    case UNDERGROUND = 'underground';
}
