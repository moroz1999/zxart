<?php

declare(strict_types=1);

namespace ZxArt\Prods;

/**
 * How an author or a group is linked to a production: made it (a credited
 * author, a developer group) or published it.
 */
enum ProdLink: string
{
    case Made = 'made';
    case Published = 'published';
}
