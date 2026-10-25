<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Shared\Elements\ElementFields;
use zxProdElement;

/**
 * Reads the editable fields of a production into the DTO
 * {@see ProdFieldsWriter} writes: every field set, a list or a reference left
 * empty as `[]` or 0. Checks no privilege.
 *
 * @psalm-api
 */
final readonly class ProdFieldsReader
{
    public function read(zxProdElement $prod): ProdFieldsDto
    {
        return new ProdFieldsDto(
            title: ElementFields::text($prod, 'title'),
            altTitle: ElementFields::text($prod, 'altTitle'),
            year: ElementFields::number($prod, 'year'),
            description: ElementFields::text($prod, 'description'),
            htmlDescription: $prod->isHtmlDescription(),
            instructions: ElementFields::text($prod, 'instructions'),
            htmlInstructions: $prod->isHtmlInstructions(),
            legalStatus: LegalStatus::tryFromName(ElementFields::text($prod, 'legalStatus')),
            externalLink: ElementFields::text($prod, 'externalLink'),
            youtubeId: ElementFields::text($prod, 'youtubeId'),
            partyId: ElementFields::number($prod, 'party'),
            partyPlace: ElementFields::number($prod, 'partyplace'),
            compo: ElementFields::text($prod, 'compo'),
            denyVoting: ElementFields::flag($prod, 'denyVoting'),
            denyComments: ElementFields::flag($prod, 'denyComments'),
            languages: ElementFields::strings($prod, 'language'),
            hardware: ElementFields::strings($prod, 'hardwareRequired'),
            categoryIds: array_values(array_map('intval', (array)$prod->getConnectedCategoriesIds())),
            groupIds: ElementFields::ids($prod, 'groups'),
            publisherIds: ElementFields::ids($prod, 'publishers'),
            tags: $prod->getTagsTexts(),
            compilationItemIds: ElementFields::ids($prod, 'compilationItems'),
            seriesProdIds: ElementFields::ids($prod, 'seriesProds'),
        );
    }
}
