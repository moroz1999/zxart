<?php

declare(strict_types=1);

namespace ZxArt\Users;

/** Outcome of a self-service password change. */
enum PasswordChangeResult: string
{
    case Changed = 'changed';
    case Unauthorized = 'unauthorized';
    case WrongCurrentPassword = 'wrong-current-password';
    case NewPasswordMismatch = 'new-password-mismatch';
}
