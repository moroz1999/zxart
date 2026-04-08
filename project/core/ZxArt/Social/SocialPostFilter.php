<?php

declare(strict_types=1);

namespace ZxArt\Social;

use structureElement;
use zxProdElement;
use zxReleaseElement;

readonly class SocialPostFilter
{
    public function shouldSkip(structureElement $element): bool
    {
        if ($element instanceof zxProdElement) {
            return $element->getImage(0) === false;
        }
        if ($element instanceof zxReleaseElement) {
            $releaseHasImages = $element->getImage(0) !== false;
            $prod = $element->getProd();
            $prodHasImages = $prod !== null && $prod->getImage(0) !== false;
            return !$releaseHasImages && !$prodHasImages;
        }
        return false;
    }
}
