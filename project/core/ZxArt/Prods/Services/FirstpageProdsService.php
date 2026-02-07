<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use structureManager;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\ProdsTransformer;
use ZxArt\Prods\Repositories\ProdsRepository;
use ZxArt\ZxProdCategories\CategoryIds;
use zxProdElement;

readonly class FirstpageProdsService
{
    public function __construct(
        private structureManager $structureManager,
        private ProdsRepository $prodsRepository,
        private ProdsTransformer $prodsTransformer,
    ) {
    }

    /**
     * @return ProdDto[]
     */
    public function getNewProds(int $limit, float $minRating, int $daysAgo = 30): array
    {
        $ids = $this->prodsRepository->getNewProdIds($limit, $minRating, $daysAgo);
        return $this->loadAndTransform($ids);
    }

    /**
     * @return ProdDto[]
     */
    public function getLatestAdded(int $limit): array
    {
        $ids = $this->prodsRepository->getLatestAddedIds($limit);
        return $this->loadAndTransform($ids);
    }

    /**
     * @return ProdDto[]
     */
    public function getBestNewDemos(int $limit, float $minRating): array
    {
        $ids = $this->prodsRepository->getBestNewByCategoryIds(
            CategoryIds::DEMOS->value,
            $limit,
            $minRating,
            (int)date('Y'),
        );
        return $this->loadAndTransform($ids);
    }

    /**
     * @return ProdDto[]
     */
    public function getBestNewGames(int $limit, float $minRating): array
    {
        $ids = $this->prodsRepository->getBestNewByCategoryIds(
            CategoryIds::GAMES->value,
            $limit,
            $minRating,
            (int)date('Y'),
        );
        return $this->loadAndTransform($ids);
    }

    /**
     * @return ProdDto[]
     */
    public function getForSaleOrDonation(int $limit): array
    {
        $ids = $this->prodsRepository->getForSaleOrDonationIds($limit);
        return $this->loadAndTransform($ids);
    }

    /**
     * @param int[] $ids
     * @return ProdDto[]
     */
    private function loadAndTransform(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $element = $this->structureManager->getElementById($id);
            if ($element instanceof zxProdElement) {
                $result[] = $this->prodsTransformer->toDto($element);
            }
        }
        return $result;
    }
}
