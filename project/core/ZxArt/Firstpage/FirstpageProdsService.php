<?php

declare(strict_types=1);

namespace ZxArt\Firstpage;

use DateTimeImmutable;
use structureManager;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\ProdsTransformer;
use ZxArt\Prods\Dbal\ProdsDbal;
use ZxArt\ZxProdCategories\CategoryIds;
use ZxArt\ZxProdCategories\ProdCategoryTreeService;
use zxProdElement;

readonly class FirstpageProdsService
{
    public function __construct(
        private structureManager $structureManager,
        private ProdsDbal $prodsDbal,
        private ProdsTransformer $prodsTransformer,
        private ProdCategoryTreeService $prodCategoryTreeService,
    ) {
    }

    /**
     * @return ProdDto[]
     */
    public function getNewProds(int $limit, float $minRating, int $daysAgo = 30, ?int $startYear = null): array
    {
        $ids = $this->prodsDbal->getNewProdIds($limit, $minRating, $daysAgo, $startYear);
        return $this->loadAndTransform($ids);
    }

    /**
     * @return ProdDto[]
     */
    public function getLatestAdded(int $limit): array
    {
        $ids = $this->prodsDbal->getLatestAddedIds($limit);
        return $this->loadAndTransform($ids);
    }

    /**
     * @return ProdDto[]
     */
    public function getBestNewDemos(int $limit, float $minRating): array
    {
        $ids = $this->prodsDbal->getBestNewByCategoryIds(
            $this->prodCategoryTreeService->getTreeIds(CategoryIds::DEMOSCENE->value),
            $limit,
            $minRating,
            BestNewProdsPeriod::firstYear(new DateTimeImmutable()),
        );
        return $this->loadAndTransform($ids);
    }

    /**
     * @return ProdDto[]
     */
    public function getBestNewGames(int $limit, float $minRating): array
    {
        $ids = $this->prodsDbal->getBestNewByCategoryIds(
            $this->prodCategoryTreeService->getTreeIds(CategoryIds::GAMES->value),
            $limit,
            $minRating,
            BestNewProdsPeriod::firstYear(new DateTimeImmutable()),
        );
        return $this->loadAndTransform($ids);
    }

    /**
     * @return ProdDto[]
     */
    public function getForSaleOrDonation(int $limit): array
    {
        $ids = $this->prodsDbal->getForSaleOrDonationIds($limit);
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
