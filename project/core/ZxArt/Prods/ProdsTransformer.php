<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Shared\Dto\AuthorDto;
use ZxArt\Shared\Dto\PartyInfoDto;
use zxProdElement;

readonly class ProdsTransformer
{
    /**
     * @return ProdDto
     */
    public function toDto(zxProdElement $element): ProdDto
    {
        $authors = [];
        foreach ($element->getAuthorsList() as $author) {
            $authors[] = new AuthorDto(
                name: (string)$author->getTitle(),
                url: $author->getUrl(),
            );
        }

        $party = null;
        $partyElement = $element->getPartyElement();
        if ($partyElement) {
            $party = new PartyInfoDto(
                title: (string)$partyElement->getTitle(),
                url: $partyElement->getUrl(),
                place: (int)$element->partyplace ?: null,
            );
        }

        $categories = [];
        foreach ($element->getCategoriesInfo() as $categoryInfo) {
            $categories[] = $categoryInfo['title'];
        }

        $userVote = $element->getUserVote();

        return new ProdDto(
            id: (int)$element->id,
            title: (string)$element->getTitle(),
            url: $element->getUrl(),
            year: $element->year ? (string)$element->year : null,
            imageUrl: $element->getImageUrl(0, 'prodImage') ?: null,
            votes: (float)$element->votes,
            votesAmount: (int)$element->votesAmount,
            userVote: $userVote !== null && $userVote !== false ? (int)$userVote : null,
            denyVoting: $element->isVotingDenied(),
            authors: $authors,
            categories: $categories,
            party: $party,
            legalStatus: $element->legalStatus ? (string)$element->legalStatus : null,
        );
    }
}
