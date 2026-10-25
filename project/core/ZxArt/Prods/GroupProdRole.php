<?php

declare(strict_types=1);

namespace ZxArt\Prods;

/**
 * What a group did for a production: made it or published it.
 */
enum GroupProdRole: string
{
    case Developer = 'developer';
    case Publisher = 'publisher';
}
