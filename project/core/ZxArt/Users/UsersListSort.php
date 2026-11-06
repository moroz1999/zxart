<?php

declare(strict_types=1);

namespace ZxArt\Users;

/**
 * The columns the management users list can be sorted by.
 */
enum UsersListSort: string
{
    case DateCreated = 'dateCreated';
    case UserName = 'userName';
    case Email = 'email';
}
