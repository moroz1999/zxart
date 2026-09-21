<?php

declare(strict_types=1);

namespace ZxArt\Screenshots\SpectrumComputing;

readonly class SpectrumComputingScreen
{
    public function __construct(
        public string $url,
        public SpectrumComputingScreenFormat $format,
    ) {
    }
}
