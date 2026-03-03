<?php

declare(strict_types=1);

namespace ZxArt\Releases;

use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Releases\Dto\ReleaseDto;
use ZxArt\Shared\Dto\AuthorDto;
use zxReleaseElement;

readonly class ReleasesTransformer
{
    public function toDto(zxReleaseElement $element): ReleaseDto
    {
        $authors = [];
        foreach ($element->getAuthorsList() as $author) {
            $authors[] = new AuthorDto(
                name: html_entity_decode((string)$author->getTitle(), ENT_QUOTES),
                url: $author->getUrl(),
            );
        }

        return new ReleaseDto(
            id: (int)$element->id,
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            url: $element->getUrl(),
            year: $element->year ? (string)$element->year : null,
            votes: (float)$element->votes,
            authors: $authors,
        );
    }

    public function toProdDto(zxReleaseElement $element): ProdDto
    {
        $partyInfo = null;
        $partyPlace = 0;
        $partyElement = $element->getPartyElement();
        if ($partyElement) {
            $partyInfo = [
                'id' => (int)$partyElement->id,
                'title' => html_entity_decode((string)$partyElement->getTitle(), ENT_QUOTES),
                'url' => $partyElement->getUrl(),
            ];
        }

        $imageUrls = $element->getImagesUrls('prodListImage');
        if (empty($imageUrls)) {
            $fallback = $element->getImageUrl(0, 'prodListImage');
            if ($fallback) {
                $imageUrls = [(string)$fallback];
            }
        }

        $userVote = $element->getUserVote();

        return new ProdDto(
            id: (int)$element->id,
            url: $element->getUrl(),
            structureType: 'zxRelease',
            dateCreated: (int)$element->dateAdded,
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            year: $element->year ? (int)$element->year : null,
            listImagesUrls: $imageUrls,
            votes: (float)$element->votes,
            votesAmount: (int)$element->votesAmount,
            userVote: $userVote !== null && $userVote !== false ? (int)$userVote : null,
            denyVoting: $element->isVotingDenied(),
            hardwareInfo: $element->getHardwareInfo(),
            authorsInfoShort: $element->getShortAuthorship('prod'),
            categoriesInfo: [],
            partyInfo: $partyInfo,
            partyPlace: $partyPlace,
            legalStatus: $element->getLegalStatus(),
            languagesInfo: [],
            groupsInfo: [],
            youtubeId: null,
        );
    }
}
