<?php

declare(strict_types=1);

namespace ZxArt\Search\Dbal;

enum TitleMatchMode
{
    /** the title's key holds the phrase's key */
    case Contains;
    /** the title's key is the phrase's key */
    case Exact;
}
