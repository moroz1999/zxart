<?php

declare(strict_types=1);

namespace ZxArt\Pictures;

use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Shared\Dto\AuthorDto;
use ZxArt\Shared\Dto\PartyInfoDto;
use zxPictureElement;

readonly class PicturesTransformer
{
    public function toDto(zxPictureElement $element): PictureDto
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

        $userVote = $element->getUserVote();

        return new PictureDto(
            id: (int)$element->id,
            title: (string)$element->getTitle(),
            url: $element->getUrl(),
            imageUrl: $element->getImageUrl(1),
            year: $element->year ? (string)$element->year : null,
            authors: $authors,
            party: $party,
            isRealtime: $element->isRealtime(),
            isFlickering: $element->isFlickering(),
            compo: $element->compo ? (string)$element->compo : null,
            votes: (float)$element->votes,
            votesAmount: (int)$element->votesAmount,
            userVote: $userVote !== null && $userVote !== false ? (int)$userVote : null,
            denyVoting: $element->isVotingDenied(),
            commentsAmount: (int)$element->commentsAmount,
        );
    }
}
