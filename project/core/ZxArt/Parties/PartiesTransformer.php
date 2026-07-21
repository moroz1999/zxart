<?php

declare(strict_types=1);

namespace ZxArt\Parties;

use partyElement;
use structureElement;
use ZxArt\Parties\Dto\PartyDto;
use ZxArt\Parties\Dto\PartyLocationItemDto;

readonly class PartiesTransformer
{
    public function __construct(
        private \ZxArt\Urls\EntityUrlResolver $entityUrlResolver,
    ) {
    }

    public function toDto(partyElement $element): PartyDto
    {
        return new PartyDto(
            id: (int)$element->id,
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            url: $this->entityUrlResolver->urlFor($element),
            year: $element->getYear(),
            imageUrl: $element->getImageUrl('partyShort'),
            country: $this->toLocationItem($element->getCountryElement()),
            city: $this->toLocationItem($element->getCityElement()),
        );
    }

    private function toLocationItem(?structureElement $element): ?PartyLocationItemDto
    {
        if ($element === null) {
            return null;
        }

        return new PartyLocationItemDto(
            title: html_entity_decode((string)$element->title, ENT_QUOTES),
            url: $this->entityUrlResolver->urlFor($element),
        );
    }
}
