<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat;

enum RunType: string
{
    /** The agent answers a message. */
    case Chat = 'chat';
    /** An approved plan is executed. */
    case Execute = 'execute';
}
