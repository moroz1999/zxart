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
     * @return array{items: list<array{type: 'prod', prod: ProdDto}|array{type: 'release', release: ProdReleaseDto}>, total: int, availableTypes: string[]}
     */
    public function getProdsPaged(
        int $groupId,
        GroupProdsScope $scope,
        int $start,
        int $limit,
        string $sort,
        string $sortDir,
        string $type,
    ): array {
        $group = $this->structureManager->getElementById($groupId);
        if (!($group instanceof groupElement) && !($group instanceof groupAliasElement)) {
            throw new ProdDetailsException('Group or alias not found', 404);
        }

        $page = $this->groupProdsRepository->findPaged($groupId, $scope, $start, $limit, $sort, $sortDir, $type);
        $availableTypes = $scope->isReleases()
            ? $this->groupProdsRepository->findAvailableReleaseTypes($groupId)
            : [];

        $items = [];
        foreach ($page['items'] as $item) {
            $element = $this->structureManager->getElementById($item['id']);
            if ($scope->isReleases()) {
                if ($element instanceof zxReleaseElement) {
                    $items[] = [
                        'type' => 'release',
                        'release' => $this->prodReleasesService->buildStandaloneRelease($element),
                    ];
                }
            } elseif ($element instanceof zxProdElement) {
                $items[] = [
                    'type' => 'prod',
                    'prod' => $this->prodsTransformer->toDto($element),
                ];
            }
        }

        return ['items' => $items, 'total' => $page['total'], 'availableTypes' => $availableTypes];
    }
}
