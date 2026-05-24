<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Services;

use App\Users\CurrentUserService;
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
        private CurrentUserService $currentUserService,
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
        $user = $this->currentUserService->getCurrentUser();
        $isAuthorized = $user->isAuthorized();
        if ($isAuthorized === false) {
            return [];
        }
        $ids = $this->picturesRepository->getUnvotedByUserIds(
            (int)$user->id,
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
     * @return PictureDto[]
     */
    public function getByAuthor(int $authorId): array
    {
        $ids = $this->picturesRepository->findIdsByAuthorId($authorId);
        return $this->loadAndTransform($ids);
    }

    /**
     * @return array{items: PictureDto[], total: int, availableFormats: string[]}
     */
    public function getByAuthorPaged(int $authorId, int $start, int $limit, string $sortColumn, string $sortDir, string $typeFilter = ''): array
    {
        $total = $this->picturesRepository->countByAuthorId($authorId, $typeFilter);
        $ids = $this->picturesRepository->findPagedIdsByAuthorId($authorId, $start, $limit, $sortColumn, $sortDir, $typeFilter);
        $availableFormats = $this->picturesRepository->getDistinctTypesByAuthorId($authorId);
        return [
            'items' => $this->loadAndTransform($ids),
            'total' => $total,
            'availableFormats' => $availableFormats,
        ];
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
