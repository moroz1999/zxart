<?php

declare(strict_types=1);

namespace ZxArt\PictureList;

use structureManager;
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\PicturesTransformer;
use zxPictureElement;

readonly class PictureListService
{
    public function __construct(
        private structureManager $structureManager,
        private PicturesTransformer $picturesTransformer,
    ) {
    }

    /**
     * @return PictureDto[]
     */
    public function getPictures(int $elementId, ?string $compoType = null): array
    {
        $element = $this->structureManager->getElementById($elementId);
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
        if (method_exists($element, 'getItems')) {
            return (array)($element->getItems() ?? []);
        }
        return [];
    }
}
