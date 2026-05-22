<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use ZxArt\Prods\Dto\ProdDescriptionDto;

readonly class ProdDescriptionService
{
    public function __construct(
        private ProdElementService $prodElementService,
        private ProdInfoBuilder $infoBuilder,
    ) {
    }

    public function getDescription(int $elementId): ProdDescriptionDto
    {
        $element = $this->prodElementService->get($elementId);

        return new ProdDescriptionDto(
            description: $this->infoBuilder->decodeText($element->getDescription()),
            htmlDescription: $element->isHtmlDescription(),
            instructions: $this->infoBuilder->decodeText($element->instructions),
        );
    }
}
