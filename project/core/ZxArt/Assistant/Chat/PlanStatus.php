<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat;

enum PlanStatus: string
{
    case Proposed = 'proposed';
    case Executing = 'executing';
    case Done = 'done';
    case Failed = 'failed';
    /** The user stopped it; the actions left were skipped. */
    case Stopped = 'stopped';
    /** The user sent another message instead of approving it. */
    case Superseded = 'superseded';
    /** The user cancelled it instead of approving it. */
    case Cancelled = 'cancelled';
}
