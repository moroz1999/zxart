<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use groupAliasElement;
use groupElement;
use structureManager;
use zxProdElement;
use zxReleaseElement;
use ZxArt\Groups\GroupProdsScope;
use ZxArt\Groups\Repositories\GroupProdsRepository;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\Dto\ProdReleaseDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\ProdReleasesService;
use ZxArt\Prods\ProdsTransformer;

readonly class GroupProdsService
{
    public function __construct(
        private structureManager $structureManager,
        private GroupProdsRepository $groupProdsRepository,
        private ProdsTransformer $prodsTransformer,
        private ProdReleasesService $prodReleasesService,
    ) {
    }

    /**
     * @return array{items: list<array{type: 'prod', prod: ProdDto}|array{type: 'release', release: ProdReleaseDto}>, total: int, availableTypes: string[], availableCategories: list<array{id: int, title: string}>}
     */
    public function getProdsPaged(
        int $groupId,
        GroupProdsScope $scope,
        int $start,
        int $limit,
        string $sort,
        string $sortDir,
        string $type,
        int $categoryId,
    ): array {
        $group = $this->structureManager->getElementById($groupId);
        if (!($group instanceof groupElement) && !($group instanceof groupAliasElement)) {
            throw new ProdDetailsException('Group or alias not found', 404);
        }

        $page = $this->groupProdsRepository->findPaged($groupId, $scope, $start, $limit, $sort, $sortDir, $type, $categoryId);
        $availableTypes = $scope->isReleases()
            ? $this->groupProdsRepository->findAvailableReleaseTypes($groupId)
            : [];
        $availableCategories = !$scope->isReleases()
            ? $this->buildAvailableCategories($this->groupProdsRepository->findAvailableCategoryIds($groupId, $scope))
            : [];

        $items = [];
        foreach ($page['items'] as $item) {
            $element = $this->structureManager->getElementById($item['id']);
            if ($item['type'] === 'release' && $element instanceof zxReleaseElement) {
                $items[] = [
                    'type' => 'release',
                    'release' => $this->prodReleasesService->buildStandaloneRelease($element),
                ];
            } elseif ($item['type'] === 'prod' && $element instanceof zxProdElement) {
                $items[] = [
                    'type' => 'prod',
                    'prod' => $this->prodsTransformer->toDto($element),
                ];
            }
        }

        return [
            'items' => $items,
            'total' => $page['total'],
            'availableTypes' => $availableTypes,
            'availableCategories' => $availableCategories,
        ];
    }

    /**
     * @param int[] $categoryIds
     * @return list<array{id: int, title: string}>
     */
    private function buildAvailableCategories(array $categoryIds): array
    {
        $categories = [];
        foreach ($categoryIds as $categoryId) {
            $category = $this->structureManager->getElementById($categoryId);
            if ($category === null) {
                continue;
            }
            $categories[] = [
                'id' => $category->getId(),
                'title' => html_entity_decode((string)$category->getTitle(), ENT_QUOTES),
            ];
        }
        usort(
            $categories,
            static fn(array $a, array $b): int => strcasecmp((string)$a['title'], (string)$b['title']),
        );

        return $categories;
    }
}
