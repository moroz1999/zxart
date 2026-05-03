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
                url: (string)$author->getUrl(),
            );
        }

        $party = null;
        $partyElement = $element->getPartyElement();
        if ($partyElement !== null) {
            $party = new PartyInfoDto(
                title: html_entity_decode((string)$partyElement->getTitle(), ENT_QUOTES),
                url: $partyElement->getUrl() ?? '',
                place: $element->getPartyPlace(),
            );
        }

        $release = null;
        $releaseElement = $element->getReleaseElement();
        if ($releaseElement !== null) {
            $release = new ReleaseInfoDto(
                title: html_entity_decode((string)$releaseElement->getTitle(), ENT_QUOTES),
                url: (string)$releaseElement->getUrl(),
            );
        }

        $userVote = $element->getUserVote();
        $mp3Path = $element->getMp3FilePath();
        $compoName = $element->getCompoName();

        $originalFileUrl = null;
        $originalFileName = $element->getFileName('original');
        $originalFileId = $element->getOriginalFileId();
        if ($originalFileName !== '' && $originalFileId !== null) {
            $baseUrl = $element->getService(controller::class)->baseURL;
            $originalFileUrl = $baseUrl . 'file/id:' . $originalFileId . '/filename:' . $originalFileName;
        }

        $trackerFileUrl = null;
        $trackerFileName = $element->getFileName('tracker');
        $trackerFileId = $element->getTrackerFileId();
        if ($trackerFileName !== '' && $trackerFileId !== null) {
            $baseUrl = $element->getService(controller::class)->baseURL;
            $trackerFileUrl = $baseUrl . 'file/id:' . $trackerFileId . '/filename:' . $trackerFileName;
        }

        return new TuneDto(
            id: (int)$element->id,
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            url: (string)$element->getUrl(),
            authors: $authors,
            format: $element->getFormat(),
            year: $element->getDisplayYear(),
            votes: $element->getVotes(),
            votesAmount: $element->getVotesAmount(),
            userVote: $userVote,
            denyVoting: $element->isVotingDenied(),
            commentsAmount: $element->getCommentsAmount(),
            plays: $element->getPlaysCount(),
            party: $party,
            release: $release,
            isPlayable: $element->isPlayable(),
            isRealtime: $element->isRealtime(),
            compo: $compoName !== null ? html_entity_decode($compoName, ENT_QUOTES) : null,
            mp3Url: $mp3Path !== false ? $mp3Path : null,
            originalFileUrl: $originalFileUrl,
            trackerFileUrl: $trackerFileUrl,
        );
    }
}
