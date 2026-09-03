<?php

declare(strict_types=1);

namespace ZxArt\Groups;

/**
 * Roles an author can hold inside a group. Shared by the group form, which
 * edits the roster of one group, and by the author form, which edits the group
 * memberships of one author.
 */
final class GroupMemberRoles
{
    /** @var list<string> */
    public const array LIST = [
        'coder',
        'cracker',
        'graphician',
        'hardware',
        'musician',
        'organizer',
        'support',
        'tester',
        'gamedesigner',
        'unknown',
    ];
}
