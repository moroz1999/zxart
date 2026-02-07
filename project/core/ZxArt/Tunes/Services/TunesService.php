<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Services;

use App\Users\CurrentUser;
use structureManager;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Repositories\TunesRepository;
use ZxArt\Tunes\TunesTransformer;
use zxMusicElement;

readonly class TunesService
{
    public function __construct(
        private structureManager $structureManager,
        private TunesRepository $tunesRepository,
        private TunesTransformer $tunesTransformer,
        private CurrentUser $currentUser,
    ) {
    }

    /**
     * @return TuneDto[]
     */
    public function getNew(int $limit): array
    {
        $ids = $this->tunesRepository->getNewIds($limit);
        return $this->loadAndTransform($ids);
    }

    /**
     * @return TuneDto[]
     */
    public function getUnvotedByCurrentUser(int $limit, int $topN = 500): array
    {
        if (!$this->currentUser->isAuthorized()) {
            return [];
        }
        $ids = $this->tunesRepository->getUnvotedByUserIds(
            (int)$this->currentUser->id,
            $limit,
            $topN,
        );
        return $this->loadAndTransform($ids);
    }

    /**
     * @return TuneDto[]
     */
    public function getRandomGood(int $limit, int $topN = 2000): array
    {
        $ids = $this->tunesRepository->getRandomGoodIds($limit, $topN);
        return $this->loadAndTransform($ids);
    }

    /**
     * @param int[] $ids
     * @return TuneDto[]
     */
    private function loadAndTransform(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $element = $this->structureManager->getElementById($id);
            if ($element instanceof zxMusicElement) {
                $result[] = $this->tunesTransformer->toDto($element);
            }
        }
        return $result;
    }
}
