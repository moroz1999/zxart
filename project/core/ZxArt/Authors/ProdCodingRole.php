<?php

declare(strict_types=1);

namespace ZxArt\Authors;

enum ProdCodingRole: string
{
    case Code = 'code';
    case IntroCode = 'intro_code';
    case Tools = 'tools';
    case Adaptation = 'adaptation';
    case Restoring = 'restoring';
    case Release = 'release';
}
