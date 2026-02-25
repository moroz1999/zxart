<?php

declare(strict_types=1);

namespace ZxArt\Tunes;

use controller;
use ZxArt\Shared\Dto\AuthorDto;
use ZxArt\Shared\Dto\PartyInfoDto;
use ZxArt\Shared\Dto\ReleaseInfoDto;
use ZxArt\Tunes\Dto\TuneDto;
use zxMusicElement;

readonly class TunesTransformer
{
    public function toDto(zxMusicElement $element): TuneDto
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
                place: (int)$element->partyplace ?: null,
            );
        }

        $release = null;
        $releaseElement = $element->getReleaseElement();
        if ($releaseElement) {
            $release = new ReleaseInfoDto(
                title: html_entity_decode((string)$releaseElement->getTitle(), ENT_QUOTES),
                url: $releaseElement->getUrl(),
            );
        }

        $userVote = $element->getUserVote();
        $mp3Path = $element->getMp3FilePath();

        $originalFileUrl = null;
        $originalFileName = $element->getFileName('original');
        if ($originalFileName && !empty($element->file)) {
            $baseUrl = $element->getService(controller::class)->baseURL;
            $originalFileUrl = $baseUrl . 'file/id:' . $element->file . '/filename:' . $originalFileName;
        }

        $trackerFileUrl = null;
        $trackerFileName = $element->getFileName('tracker');
        if ($trackerFileName && !empty($element->trackerFile)) {
            $baseUrl = $element->getService(controller::class)->baseURL;
            $trackerFileUrl = $baseUrl . 'file/id:' . $element->trackerFile . '/filename:' . $trackerFileName;
        }

        return new TuneDto(
            id: (int)$element->id,
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            url: $element->getUrl(),
            authors: $authors,
            format: (string)$element->type,
            year: $element->year ? (string)$element->year : null,
            votes: (float)$element->votes,
            votesAmount: (int)$element->votesAmount,
            userVote: $userVote !== null && $userVote !== false ? (int)$userVote : null,
            denyVoting: $element->isVotingDenied(),
            commentsAmount: (int)$element->commentsAmount,
            plays: (int)$element->plays,
            party: $party,
            release: $release,
            isPlayable: $element->isPlayable(),
            isRealtime: $element->isRealtime(),
            compo: $element->compo ? html_entity_decode((string)$element->compo, ENT_QUOTES) : null,
            mp3Url: $mp3Path !== false ? $mp3Path : null,
            originalFileUrl: $originalFileUrl,
            trackerFileUrl: $trackerFileUrl,
        );
    }
}
