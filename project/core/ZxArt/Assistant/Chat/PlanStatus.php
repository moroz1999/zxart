<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat;

enum PlanStatus: string
{
    case Proposed = 'proposed';
    case Executing = 'executing';
    case Done = 'done';
    case Failed = 'failed';
    /** The user sent another message instead of approving it. */
    case Superseded = 'superseded';
}
