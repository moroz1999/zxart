<?php

declare(strict_types=1);

namespace ZxArt\PictureList;

use structureManager;
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\PicturesTransformer;
use ZxArt\Pictures\Repositories\PicturesRepository;
use ZxArt\Shared\SortingParams;
use zxPictureElement;

readonly class PictureListService
{
    public const array ALLOWED_SORT_COLUMNS = ['title', 'date', 'year', 'votes'];

    public function __construct(
        private structureManager $structureManager,
        private PicturesTransformer $picturesTransformer,
        private PicturesRepository $picturesRepository,
    ) {
    }

    /**
     * @return PictureDto[]
     */
    public function getPictures(int $elementId, ?string $compoType = null): array
    {
        $element = $this->structureManager->getElementById($elementId)
            ?? $this->structureManager->getElementById($elementId, null, true);
        if ($element === null) {
            return [];
        }

        $pictureElements = $this->resolvePictureElements($element, $compoType);

        $result = [];
        foreach ($pictureElements as $pictureElement) {
            if ($pictureElement instanceof zxPictureElement) {
                $result[] = $this->picturesTransformer->toDto($pictureElement);
            }
        }
        return $result;
    }

    /**
     * Returns related pictures for a picture element.
     * If the picture belongs to a game (release), returns best pictures from that game.
     * Otherwise returns best pictures from the same authors.
     *
     * @return array{type: string, items: PictureDto[]}
     */
    public function getRelated(int $pictureId): array
    {
        $pictureElement = $this->structureManager->getElementById($pictureId);
        if (!$pictureElement instanceof zxPictureElement) {
            return ['type' => 'none', 'items' => []];
        }

        if (method_exists($pictureElement, 'getReleaseElement') && ($releaseElement = $pictureElement->getReleaseElement())) {
            if (method_exists($releaseElement, 'getBestPictures')) {
                $pictures = (array)($releaseElement->getBestPictures(3, $pictureId) ?? []);
                return ['type' => 'game', 'items' => $this->transformElements($pictures)];
            }
        }

        if (method_exists($pictureElement, 'getBestAuthorsPictures')) {
            $pictures = (array)($pictureElement->getBestAuthorsPictures(3) ?? []);
            return ['type' => 'authors', 'items' => $this->transformElements($pictures)];
        }

        return ['type' => 'none', 'items' => []];
    }

    /**
     * @param mixed[] $elements
     * @return PictureDto[]
     */
    private function transformElements(array $elements): array
    {
        $result = [];
        foreach ($elements as $element) {
            if ($element instanceof zxPictureElement) {
                $result[] = $this->picturesTransformer->toDto($element);
            }
        }
        return $result;
    }

    /**
     * Returns a paginated+sorted page of pictures linked to an element via a given link type.
     *
     * @return array{total: int, items: PictureDto[]}
     */
    public function getPagedByLinkedElement(
        int $elementId,
        string $linkType,
        SortingParams $sorting,
        int $start,
        int $limit,
    ): array {
        $total = $this->picturesRepository->countByLinkedElement($elementId, $linkType);
        $ids = $this->picturesRepository->findPagedByLinkedElement($elementId, $linkType, $sorting, $start, $limit);

        $items = [];
        foreach ($ids as $id) {
            $element = $this->structureManager->getElementById($id)
                ?? $this->structureManager->getElementById($id, null, true);
            if ($element instanceof zxPictureElement) {
                $items[] = $this->picturesTransformer->toDto($element);
            }
        }

        return ['total' => $total, 'items' => $items];
    }

    private function resolvePictureElements(mixed $element, ?string $compoType): array
    {
        if ($compoType !== null && method_exists($element, 'getPicturesCompos')) {
            $compos = $element->getPicturesCompos();
            return $compos[$compoType] ?? [];
        }
        if (method_exists($element, 'getPicturesList')) {
            return (array)($element->getPicturesList() ?? []);
        }
        if (method_exists($element, 'getPictures')) {
            return (array)($element->getPictures() ?? []);
        }
        if (method_exists($element, 'getItemsList')) {
            return (array)($element->getItemsList() ?? []);
        }
        if (method_exists($element, 'getItems')) {
            return (array)($element->getItems() ?? []);
        }
        return [];
    }
}
