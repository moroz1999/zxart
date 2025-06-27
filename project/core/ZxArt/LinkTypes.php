<?php
declare(strict_types=1);

namespace ZxArt;

enum LinkTypes: string
{
    case AUTHOR_PICTURE = 'authorPicture';
    case ZX_PROD_CATEGORY = 'zxProdCategory';
    case PRESS_AUTHOR = 'pressAuthor';
    case PRESS_PEOPLE = 'pressPeople';
    case PRESS_GROUPS = 'pressGroups';
    case GROUP_SUBGROUP = 'groupSub';
    case PRESS_SOFTWARE = 'pressSoftware';
    case PRESS_PARTIES = 'pressParties';
    case PRESS_TUNES = 'pressTunes';
    case PRESS_PICTURES = 'pressPictures';
    case SERIES = 'series';
    case COMPILATION = 'compilation';
}