<?php

declare(strict_types=1);

namespace ZxArt\Users;

enum UserStatusFilter: string
{
    case All = 'all';
    case Active = 'active';
    case Banned = 'banned';
}
