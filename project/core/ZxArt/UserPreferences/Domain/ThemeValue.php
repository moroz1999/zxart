<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Domain;

enum ThemeValue: string
{
    case LIGHT = 'light';
    case DARK = 'dark';
}
