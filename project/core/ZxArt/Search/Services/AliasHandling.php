<?php

declare(strict_types=1);

namespace ZxArt\Search\Services;

enum AliasHandling
{
    /** An author's or a group's alias is found and answered under its owner's type. */
    case MergeIntoOwner;
    /** An alias is a type of its own, found only when asked for. */
    case Separate;
}
