<?php

declare(strict_types=1);

namespace ZxArt\Pictures;

/**
 * What a downloadable file of a picture is; the interface labels each kind.
 */
enum PictureDownloadKind: string
{
    case Original = 'original';
    case Pc = 'pc';
    case Print = 'print';
    case Executable = 'executable';
    case Stages = 'stages';
}
