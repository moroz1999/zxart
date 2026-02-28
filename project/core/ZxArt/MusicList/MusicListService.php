<?php

declare(strict_types=1);

namespace ZxArt\MusicList;

use structureManager;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\TunesTransformer;
use zxMusicElement;

readonly class MusicListService
{
    public function __construct(
        private structureManager $structureManager,
        private TunesTransformer $tunesTransformer,
    ) {
    }

    /**
     * @return TuneDto[]
     */
    public function getTunes(int $elementId, ?string $compoType = null): array
    {
        $element = $this->structureManager->getElementById($elementId);
        if ($element === null) {
            return [];
        }

        $musicElements = $this->resolveMusicElements($element, $compoType);

        $result = [];
        foreach ($musicElements as $musicElement) {
            if ($musicElement instanceof zxMusicElement) {
                $result[] = $this->tunesTransformer->toDto($musicElement);
            }
        }
        return $result;
    }

    private function resolveMusicElements(mixed $element, ?string $compoType): array
    {
        if ($compoType !== null && method_exists($element, 'getTunesCompos')) {
            $compos = $element->getTunesCompos();
            return $compos[$compoType] ?? [];
        }
        if (method_exists($element, 'getMusicList')) {
            return (array)($element->getMusicList() ?? []);
        }
        if (method_exists($element, 'getItemsList')) {
            return (array)($element->getItemsList() ?? []);
        }
        if (method_exists($element, 'getTunes')) {
            return (array)($element->getTunes() ?? []);
        }
        if (method_exists($element, 'getItems')) {
            return (array)($element->getItems() ?? []);
        }
        return [];
    }
}
