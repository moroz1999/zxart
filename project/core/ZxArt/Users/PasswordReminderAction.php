<?php

declare(strict_types=1);

namespace ZxArt\Users;

enum PasswordReminderAction: string
{
    case Request = 'request';
    case Reset = 'reset';
}
