<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Services;

use App\Users\CurrentUser;
use structureManager;
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\PicturesTransformer;
use ZxArt\Pictures\Repositories\PicturesRepository;
use zxPictureElement;

readonly class PicturesService
{
    public function __construct(
        private structureManager $structureManager,
        private PicturesRepository $picturesRepository,
        private PicturesTransformer $picturesTransformer,
        private CurrentUser $currentUser,
    ) {
    }

    /**
     * @return PictureDto[]
     */
    public function getNew(int $limit): array
    {
        $ids = $this->picturesRepository->getNewIds($limit);
        return $this->loadAndTransform($ids);
    }

    /**
     * @return PictureDto[]
     */
    public function getBestOfMonth(int $limit): array
    {
        $ids = $this->picturesRepository->getBestOfMonthIds($limit, (int)date('Y'));
        return $this->loadAndTransform($ids);
    }

    /**
     * @return PictureDto[]
     */
    public function getUnvotedByCurrentUser(int $limit, int $topN = 500): array
    {
        if (!$this->currentUser->isAuthorized()) {
            return [];
        }
        $ids = $this->picturesRepository->getUnvotedByUserIds(
            (int)$this->currentUser->id,
            $limit,
            $topN,
        );
        return $this->loadAndTransform($ids);
    }

    /**
     * @return PictureDto[]
     */
    public function getRandomGood(int $limit, int $topN = 2000): array
    {
        $ids = $this->picturesRepository->getRandomGoodIds($limit, $topN);
        return $this->loadAndTransform($ids);
    }

    /**
     * @param int[] $ids
     * @return PictureDto[]
     */
    private function loadAndTransform(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $element = $this->structureManager->getElementById($id);
            if ($element instanceof zxPictureElement) {
                $result[] = $this->picturesTransformer->toDto($element);
            }
        }
        return $result;
    }
}
