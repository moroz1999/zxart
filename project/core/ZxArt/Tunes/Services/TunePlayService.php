<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Services;

use structureManager;
use ZxArt\Tunes\Exception\TuneNotFoundException;
use zxMusicElement;

readonly class TunePlayService
{
    public function __construct(
        private structureManager $structureManager,
    ) {
    }

    /**
     * @throws TuneNotFoundException
     */
    public function logPlay(int $tuneId): void
    {
        $element = $this->structureManager->getElementById($tuneId);
        if (!$element instanceof zxMusicElement) {
            throw TuneNotFoundException::forId($tuneId);
        }

        $element->logPlay();
    }
}
