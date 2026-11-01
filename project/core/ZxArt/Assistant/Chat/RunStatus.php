<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat;

enum RunStatus: string
{
    case Running = 'running';
    case Done = 'done';
    /** The agent proposed a plan and stopped. */
    case WaitingApproval = 'waitingApproval';
    case Failed = 'failed';
}
