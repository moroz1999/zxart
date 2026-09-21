<?php

declare(strict_types=1);

namespace ZxArt\Screenshots\SpectrumComputing;

/**
 * File formats of Spectrum Computing screens the gallery accepts.
 */
enum SpectrumComputingScreenFormat: string
{
    case Scr = 'scr';
    case Png = 'png';
    case Gif = 'gif';

    public function isNative(): bool
    {
        return $this === self::Scr;
    }
}
