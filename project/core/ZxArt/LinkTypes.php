<?php
declare(strict_types=1);

namespace ZxArt;

enum LinkTypes: string
{
    case ZX_PROD_CATEGORY = 'zxProdCategory';
    case PRESS_AUTHOR = 'pressAuthor';
    case PRESS_PEOPLE = 'pressPeople';
    case PRESS_GROUPS = 'pressGroups';
    case PRESS_SOFTWARE = 'pressSoftware';
    case PRESS_PARTIES = 'pressParties';
}