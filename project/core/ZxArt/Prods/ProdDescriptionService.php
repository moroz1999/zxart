<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use ZxArt\Prods\Dto\ProdDescriptionDto;
use ZxArt\Shared\DescriptionFormatter;

readonly class ProdDescriptionService
{
    public function __construct(
        private ProdElementService $prodElementService,
        private DescriptionFormatter $descriptionFormatter,
    ) {
    }

    public function getDescription(int $elementId): ProdDescriptionDto
    {
        $element = $this->prodElementService->get($elementId);

        return new ProdDescriptionDto(
            description: $this->descriptionFormatter->decode($element->getDescription()),
            htmlDescription: $element->isHtmlDescription(),
            htmlInstructions: $element->isHtmlInstructions(),
            instructions: $this->descriptionFormatter->decode($element->getInstructions()),
        );
    }
}
