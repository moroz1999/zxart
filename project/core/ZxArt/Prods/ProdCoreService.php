<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use structureManager;
use ZxArt\Prods\Dto\ProdCoreDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use zxProdElement;

readonly class ProdCoreService
{
    public function __construct(
        private structureManager $structureManager,
    ) {
    }

    public function getCore(int $elementId): ProdCoreDto
    {
        $element = $this->structureManager->getElementById($elementId);
        if (!$element instanceof zxProdElement) {
            throw new ProdDetailsException('Prod not found', 404);
        }

        return new ProdCoreDto(
            elementId: $element->id,
            title: $element->title,
            prodUrl: (string)$element->getUrl(),
        );
    }
}
