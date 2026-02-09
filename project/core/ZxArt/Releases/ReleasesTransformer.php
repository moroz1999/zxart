<?php

declare(strict_types=1);

namespace ZxArt\Releases;

use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Releases\Dto\ReleaseDto;
use ZxArt\Shared\Dto\AuthorDto;
use ZxArt\Shared\Dto\PartyInfoDto;
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
        $authors = [];
        foreach ($element->getAuthorsList() as $author) {
            $authors[] = new AuthorDto(
                name: html_entity_decode((string)$author->getTitle(), ENT_QUOTES),
                url: $author->getUrl(),
            );
        }

        $party = null;
        $partyElement = $element->getPartyElement();
        if ($partyElement) {
            $party = new PartyInfoDto(
                title: html_entity_decode((string)$partyElement->getTitle(), ENT_QUOTES),
                url: $partyElement->getUrl(),
                place: null,
            );
        }

        $userVote = $element->getUserVote();

        return new ProdDto(
            id: (int)$element->id,
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            url: $element->getUrl(),
            year: $element->year ? (string)$element->year : null,
            imageUrl: $element->getImageUrl(0, 'prodImage') ?: null,
            votes: (float)$element->votes,
            votesAmount: (int)$element->votesAmount,
            userVote: $userVote !== null && $userVote !== false ? (int)$userVote : null,
            denyVoting: $element->isVotingDenied(),
            authors: $authors,
            categories: [],
            party: $party,
            legalStatus: $element->getLegalStatus(),
        );
    }
}
