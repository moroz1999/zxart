<?php

declare(strict_types=1);

namespace ZxArt\EntityConversion;

/**
 * What an author, a group or one of their aliases can be turned into.
 */
enum ConversionTarget: string
{
    case Author = 'author';
    case Group = 'group';

    /**
     * Name of the action privilege the source element must grant.
     */
    public function getPrivilege(): string
    {
        return match ($this) {
            self::Author => 'convertToAuthor',
            self::Group => 'convertToGroup',
        };
    }
}
