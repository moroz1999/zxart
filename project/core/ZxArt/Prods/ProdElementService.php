<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use structureManager;
use ZxArt\Prods\Exception\ProdDetailsException;
use zxProdElement;

readonly class ProdElementService
{
    public function __construct(
        private structureManager $structureManager,
    ) {
    }

    public function get(int $elementId): zxProdElement
    {
        $element = $this->structureManager->getElementById($elementId);
        if (!$element instanceof zxProdElement) {
            throw new ProdDetailsException('Prod not found', 404);
        }
        return $element;
    }
}