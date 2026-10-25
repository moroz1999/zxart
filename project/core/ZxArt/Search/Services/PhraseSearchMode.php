<?php

declare(strict_types=1);

namespace ZxArt\Search\Services;

enum PhraseSearchMode
{
    /** One page of every type: the closest titles of each type on top. */
    case SinglePage;
    /** A page cut from one alphabetical list of the types one after another. */
    case Paged;
}
