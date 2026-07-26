<?php

declare(strict_types=1);

namespace ZxArt\Forms;

enum FormCreateType: string
{
    case Author = 'author';
    case Group = 'group';
    case Party = 'party';
    case ProdBatch = 'prodBatch';
}
