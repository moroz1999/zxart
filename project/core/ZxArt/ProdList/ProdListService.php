<?php

declare(strict_types=1);

namespace ZxArt\ProdList;

use structureManager;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\ProdsTransformer;
use ZxArt\Prods\Repositories\ProdsRepository;
use ZxArt\Shared\SortingParams;
use zxProdElement;

readonly class ProdListService
{
    public const array ALLOWED_SORT_COLUMNS = ['title', 'date', 'year', 'votes'];

    /** Link types a prod list may be requested for. */
    public const array ALLOWED_LINK_TYPES = ['tagLink', 'playlist'];

    public function __construct(
        private structureManager $structureManager,
        private ProdsTransformer $prodsTransformer,
        private ProdsRepository $prodsRepository,
    ) {
    }

    /**
     * Returns a paginated+sorted page of prods linked to an element via a given link type.
     *
     * @return array{total: int, items: ProdDto[]}
     */
    public function getPagedByLinkedElement(
        int $elementId,
        string $linkType,
        SortingParams $sorting,
        int $start,
        int $limit,
    ): array {
        $total = $this->prodsRepository->countByLinkedElement($elementId, $linkType);
        $ids = $this->prodsRepository->findPagedByLinkedElement($elementId, $linkType, $sorting, $start, $limit);

        $items = [];
        foreach ($ids as $id) {
            $element = $this->structureManager->getElementById($id)
                ?? $this->structureManager->getElementById($id, null, true);
            if ($element instanceof zxProdElement) {
                $items[] = $this->prodsTransformer->toDto($element);
            }
        }

        return ['total' => $total, 'items' => $items];
    }

    /**
     * Every prod linked to an element, for lists that are shown in full (a user
     * playlist holds a handful of entries and has no pagination).
     *
     * @return array{total: int, items: ProdDto[]}
     */
    public function getAllByLinkedElement(int $elementId, string $linkType, SortingParams $sorting): array
    {
        $total = $this->prodsRepository->countByLinkedElement($elementId, $linkType);
        if ($total === 0) {
            return ['total' => 0, 'items' => []];
        }

        return $this->getPagedByLinkedElement($elementId, $linkType, $sorting, 0, $total);
    }
}
