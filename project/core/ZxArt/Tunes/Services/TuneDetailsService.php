<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Services;

use structureManager;
use translationsManager;
use ZxArt\Prods\ProdInfoBuilder;
use ZxArt\Tunes\Dto\TuneDetailsDto;
use ZxArt\Tunes\Dto\TuneDownloadDto;
use ZxArt\Tunes\Dto\TunePartyContextDto;
use ZxArt\Tunes\Dto\TuneSubmitterDto;
use ZxArt\Tunes\Dto\TuneTagDto;
use ZxArt\Tunes\Exception\TuneDetailsException;
use ZxArt\Tunes\TunesTransformer;
use zxMusicElement;

/**
 * Builds the rich {@see TuneDetailsDto} consumed by the Angular
 * <zx-tune-details> page. The controller maps it to its REST DTO.
 *
 * Mirrors {@see \ZxArt\Pictures\Services\PictureDetailsService}.
 */
readonly class TuneDetailsService
{
    public function __construct(
        private structureManager $structureManager,
        private translationsManager $translationsManager,
        private TunesTransformer $tunesTransformer,
        private ProdInfoBuilder $infoBuilder,
    ) {
    }

    public function getDetails(int $tuneId): TuneDetailsDto
    {
        $element = $this->structureManager->getElementById($tuneId);
        if (!$element instanceof zxMusicElement) {
            throw new TuneDetailsException('Tune not found', 404);
        }

        $tune = $this->tunesTransformer->toDto($element);
        $hasChip = $element->hasChipChannelsType();

        return new TuneDetailsDto(
            id: $tune->id,
            title: $tune->title,
            url: $tune->url,
            authors: $tune->authors,
            format: $tune->format,
            year: $tune->year,
            votes: $tune->votes,
            votesAmount: $tune->votesAmount,
            userVote: $tune->userVote,
            denyVoting: $tune->denyVoting,
            commentsAmount: $tune->commentsAmount,
            plays: $tune->plays,
            party: $tune->party,
            release: $tune->release,
            isPlayable: $tune->isPlayable,
            isRealtime: $tune->isRealtime,
            compo: $tune->compo,
            mp3Url: $tune->mp3Url,
            originalFileUrl: $tune->originalFileUrl,
            trackerFileUrl: $tune->trackerFileUrl,
            description: $element->description
                ? $this->infoBuilder->decodeText((string)$element->description)
                : null,
            tags: $this->buildTags($element),
            partyContext: $this->buildPartyContext($element, $tune->party?->place),
            chip: $hasChip
                ? $this->resolveLabel('zxmusic.chiptype_' . $element->getChipType(), (string)$element->getChipType())
                : null,
            channelsType: $hasChip
                ? $this->resolveLabel('zxmusic.channelstype_' . $element->getChannelsType(), (string)$element->getChannelsType())
                : null,
            channels: (int)$element->channels ?: null,
            duration: $this->nullableString($element->time),
            container: $this->nullableString($element->container),
            tracker: $this->nullableString($element->program),
            internalTitle: $this->decodeNullable($element->internalTitle),
            internalAuthor: $this->decodeNullable($element->internalAuthor),
            frequency: $hasChip
                ? $this->resolveLabel('zxmusic.frequency_' . $element->getFrequency(), (string)$element->getFrequency())
                : null,
            intFrequency: $hasChip
                ? $this->resolveLabel(
                    'zxmusic.intFrequency_' . str_replace('.', '', (string)$element->getIntFrequency()),
                    (string)$element->getIntFrequency(),
                )
                : null,
            fileName: $this->nullableString($element->getFileName('original', false, false)),
            converterVersion: $this->nullableString($element->converterVersion),
            dateCreated: $element->dateCreated ? (string)$element->dateCreated : null,
            submitter: $this->buildSubmitter($element),
            downloads: $this->buildDownloads($element, $tune->originalFileUrl, $tune->trackerFileUrl, $tune->mp3Url),
        );
    }

    /**
     * @return TuneTagDto[]
     */
    private function buildTags(zxMusicElement $element): array
    {
        $result = [];
        foreach (($element->getTagsList() ?: []) as $tag) {
            $result[] = new TuneTagDto(
                title: $this->infoBuilder->decodeText((string)$tag->getTitle()),
                url: (string)$tag->getUrl(),
            );
        }
        return $result;
    }

    private function buildPartyContext(zxMusicElement $element, ?int $place): ?TunePartyContextDto
    {
        $party = $element->getPartyElement();
        if ($party === null) {
            return null;
        }
        $compoLabel = null;
        $compo = $element->getCompoName();
        if ($compo !== null) {
            $compoLabel = $this->resolveLabel('musiccompo.compo_' . $compo, $compo);
        }
        return new TunePartyContextDto(
            title: $this->infoBuilder->decodeText((string)$party->getTitle()),
            url: (string)$party->getUrl(),
            place: $place,
            compoLabel: $compoLabel,
        );
    }

    private function buildSubmitter(zxMusicElement $element): ?TuneSubmitterDto
    {
        $user = $element->getUserElement();
        if (!$user) {
            return null;
        }
        return new TuneSubmitterDto(
            userName: $this->infoBuilder->decodeText((string)$user->userName),
            url: (string)$user->getUrl(),
        );
    }

    /**
     * @return TuneDownloadDto[]
     */
    private function buildDownloads(
        zxMusicElement $element,
        ?string $originalFileUrl,
        ?string $trackerFileUrl,
        ?string $mp3Url,
    ): array {
        $downloads = [];

        if ($originalFileUrl !== null) {
            $downloads[] = new TuneDownloadDto(
                id: 'original',
                ext: ltrim($element->getFileExtension('original'), '.') ?: 'bin',
                label: (string)$this->translationsManager->getTranslationByName('zxmusic.originalfile'),
                sub: $this->nullableString($element->getFileName('original', false, false)),
                size: null,
                url: $originalFileUrl,
            );
        }

        if ($trackerFileUrl !== null) {
            $downloads[] = new TuneDownloadDto(
                id: 'tracker',
                ext: ltrim($element->getFileExtension('tracker'), '.') ?: 'bin',
                label: (string)$this->translationsManager->getTranslationByName('zxmusic.trackerfile'),
                sub: $this->nullableString($element->getFileName('tracker', false, false)),
                size: null,
                url: $trackerFileUrl,
            );
        }

        if ($mp3Url !== null) {
            $extension = strtolower((string)pathinfo($mp3Url, PATHINFO_EXTENSION));
            $downloads[] = new TuneDownloadDto(
                id: 'ogg',
                ext: $extension !== '' ? $extension : 'ogg',
                label: (string)$this->translationsManager->getTranslationByName('zxmusic.oggfile'),
                sub: null,
                size: null,
                url: $mp3Url,
            );
        }

        return $downloads;
    }

    private function resolveLabel(string $translationName, ?string $fallback = null): ?string
    {
        $translation = (string)$this->translationsManager->getTranslationByName($translationName);
        if ($translation !== '') {
            return $translation;
        }
        return ($fallback !== null && $fallback !== '') ? $fallback : null;
    }

    private function nullableString(mixed $value): ?string
    {
        $string = (string)$value;
        return $string !== '' ? $string : null;
    }

    private function decodeNullable(mixed $value): ?string
    {
        $string = $this->nullableString($value);
        return $string !== null ? $this->infoBuilder->decodeText($string) : null;
    }
}
