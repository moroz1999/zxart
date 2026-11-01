<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Agent;

enum PlanActionStatus: string
{
    case Pending = 'pending';
    case Done = 'done';
    case Failed = 'failed';
    /** An earlier action failed, so this one did not run. */
    case Skipped = 'skipped';
}
