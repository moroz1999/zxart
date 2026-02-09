<?php

declare(strict_types=1);

namespace ZxArt\Parties;

use partyElement;
use ZxArt\Parties\Dto\PartyDto;

readonly class PartiesTransformer
{
    public function toDto(partyElement $element): PartyDto
    {
        return new PartyDto(
            id: (int)$element->id,
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            url: $element->getUrl(),
            year: $element->getYear(),
            imageUrl: $element->getImageUrl('partyShort'),
        );
    }
}
