<?php

declare(strict_types=1);

namespace ZxArt\Radio\Services;

use structureManager;
use ZxArt\Radio\Dto\RadioCriteriaDto;
use ZxArt\Radio\Exception\RadioTuneNotFoundException;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Repositories\TunesRepository;
use ZxArt\Tunes\TunesTransformer;
use zxMusicElement;

readonly class RadioService
{
    public function __construct(
        private structureManager $structureManager,
        private TunesRepository $tunesRepository,
        private TunesTransformer $tunesTransformer,
    ) {
    }

    /**
     * @throws RadioTuneNotFoundException
     */
    public function getNextTune(RadioCriteriaDto $criteria): TuneDto
    {
        $id = $this->tunesRepository->findRandomIdByCriteria($criteria);
        if ($id === null) {
            throw RadioTuneNotFoundException::forCriteria();
        }

        $element = $this->structureManager->getElementById($id);
        if (!$element instanceof zxMusicElement) {
            throw RadioTuneNotFoundException::forId($id);
        }

        return $this->tunesTransformer->toDto($element);
    }
}
