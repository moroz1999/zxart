<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use ZxArt\Prods\Dto\ProdDto;
use zxProdElement;

readonly class ProdsTransformer
{
    public function toDto(zxProdElement $element): ProdDto
    {
        $authorsInfoShort = [];
        foreach ($element->getAuthorsList() as $author) {
            $authorsInfoShort[] = [
                'title' => html_entity_decode((string)$author->getTitle(), ENT_QUOTES),
                'url' => $author->getUrl(),
                'roles' => [],
            ];
        }

        $partyInfo = null;
        $partyPlace = 0;
        $partyElement = $element->getPartyElement();
        if ($partyElement) {
            $partyInfo = [
                'id' => (int)$partyElement->id,
                'title' => html_entity_decode((string)$partyElement->getTitle(), ENT_QUOTES),
                'url' => $partyElement->getUrl(),
            ];
            $partyPlace = (int)$element->partyplace;
        }

        $imageUrls = $element->getImagesUrls('prodImage');
        if (empty($imageUrls)) {
            $fallback = $element->getImageUrl(0, 'prodImage');
            if ($fallback) {
                $imageUrls = [(string)$fallback];
            }
        }

        $userVote = $element->getUserVote();

        return new ProdDto(
            id: (int)$element->id,
            url: $element->getUrl(),
            structureType: 'zxProd',
            dateCreated: (int)$element->dateAdded,
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            year: $element->year ? (string)$element->year : null,
            listImagesUrls: $imageUrls,
            votes: (float)$element->votes,
            votesAmount: (int)$element->votesAmount,
            userVote: $userVote !== null && $userVote !== false ? (int)$userVote : null,
            denyVoting: $element->isVotingDenied(),
            hardwareInfo: $element->getHardwareInfo(),
            authorsInfoShort: $authorsInfoShort,
            categoriesInfo: $element->getCategoriesInfo(),
            partyInfo: $partyInfo,
            partyPlace: $partyPlace,
            legalStatus: $element->legalStatus ? (string)$element->legalStatus : null,
        );
    }
}
