<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Services;

use controller;
use structureManager;
use tagElement;
use translationsManager;
use ZxArt\Prods\ProdInfoBuilder;
use ZxArt\Shared\DescriptionFormatter;
use ZxArt\Tunes\Dto\TuneDetailsDto;
use ZxArt\Tunes\Dto\TunePartyContextDto;
use ZxArt\Tunes\Dto\TuneSubmitterDto;
use ZxArt\Tunes\Dto\TuneTagDto;
use ZxArt\Tunes\Exception\TuneDetailsException;
use ZxArt\Tunes\TunesTransformer;
use zxMusicElement;
use ZxArt\PageMetadata\PageMetadataService;

/**
 * Builds the rich {@see TuneDetailsDto} consumed by the Angular
 * <zx-tune-details> page. The controller maps it to its REST DTO.
 *
 * Mirrors {@see \ZxArt\Pictures\Services\PictureDetailsService}.
 */
readonly class TuneDetailsService
{
    public function __construct(
        private \ZxArt\Urls\EntityUrlResolver $entityUrlResolver,
        private structureManager $structureManager,
        private translationsManager $translationsManager,
        private TunesTransformer $tunesTransformer,
        private ProdInfoBuilder $infoBuilder,
        private DescriptionFormatter $descriptionFormatter,
        private PageMetadataService $pageMetadataService,
        private TuneDownloadsBuilder $downloadsBuilder,
        private controller $controller,
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
            formatGroup: $this->nullableString($element->formatGroup),
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
                ? $this->descriptionFormatter->decode((string)$element->description)
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
            downloads: $this->downloadsBuilder->build(
                $element,
                $this->controller->baseURL,
                $tune->originalFileUrl,
                $tune->trackerFileUrl,
                $tune->mp3Url,
            ),
            metadata: $this->pageMetadataService->getForPath('/tune/' . $tuneId),
        );
    }

    /**
     * @return TuneTagDto[]
     */
    private function buildTags(zxMusicElement $element): array
    {
        $result = [];
        foreach (($element->getTagsList() ?: []) as $tag) {
            if (!$tag instanceof tagElement) {
                continue;
            }
            $result[] = new TuneTagDto(
                id: $tag->getId(),
                title: $this->infoBuilder->decodeText((string)$tag->getTitle()),
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
            url: $this->entityUrlResolver->urlFor($party),
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
            url: $this->entityUrlResolver->urlForUser($user),
        );
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
