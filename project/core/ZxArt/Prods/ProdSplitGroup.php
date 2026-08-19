<?php

declare(strict_types=1);

namespace ZxArt\Prods;

/**
 * Groups of items a production can be split into a new production by.
 *
 * The value is the key the `split` action reads the checked items under.
 */
enum ProdSplitGroup: string
{
    case Properties = 'properties';
    case Authors = 'authors';
    case Publishers = 'publishers';
    case Groups = 'groups';
    case Releases = 'releases';
    case Screenshots = 'screenshots';
    case Links = 'links';
}
