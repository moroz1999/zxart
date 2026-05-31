<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use structureManager;
use ZxArt\Releases\Dto\ReleaseDto;
use ZxArt\Releases\ReleasesTransformer;
use ZxArt\Releases\Repositories\ReleasesRepository;
use zxReleaseElement;

readonly class ReleasesService
{
    public function __construct(
        private structureManager $structureManager,
        private ReleasesRepository $releasesRepository,
        private ReleasesTransformer $releasesTransformer,
    ) {
    }

    /**
     * @return ReleaseDto[]
     */
    public function getLatestAdded(int $limit): array
    {
        $ids = $this->releasesRepository->getLatestAddedIds($limit);
        return $this->loadAndTransform($ids);
    }

    /**
     * @param int[] $ids
     * @return ReleaseDto[]
     */
    private function loadAndTransform(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $element = $this->structureManager->getElementById($id);
            if ($element instanceof zxReleaseElement) {
                $result[] = $this->releasesTransformer->toDto($element);
            }
        }
        return $result;
    }
}
