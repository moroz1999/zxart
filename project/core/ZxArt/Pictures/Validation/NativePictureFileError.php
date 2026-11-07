<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Validation;

enum NativePictureFileError: string
{
    case UnsupportedFormat = 'unsupportedFormat';
    case Extension = 'extension';
    case Size = 'size';
    case PcImage = 'pcImage';
    case Content = 'content';
}
