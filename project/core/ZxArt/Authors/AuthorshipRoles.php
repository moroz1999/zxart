<?php

declare(strict_types=1);

namespace ZxArt\Authors;

/**
 * Roles an author can hold on a production or a release.
 */
final class AuthorshipRoles
{
    /** @var list<string> */
    public const array LIST = [
        'unknown',
        'code',
        'release',
        'adaptation',
        'restoring',
        'music',
        'sfx',
        'support',
        'testing',
        'graphics',
        'loading_screen',
        'intro_code',
        'intro_graphics',
        'intro_music',
        'organizing',
        'direction',
        '3dmodels',
        'design',
        'logo',
        'font',
        'ascii',
        'illustrating',
        'tools',
        'localization',
        'concept',
        'gamedesign',
        'leveldesign',
        'story',
        'text',
        'editing',
        'translation',
        'video',
        'guest',
    ];
}
