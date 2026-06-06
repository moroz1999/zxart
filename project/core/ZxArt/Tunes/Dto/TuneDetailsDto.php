<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Shared\Dto\AuthorDto;
use ZxArt\Shared\Dto\PartyInfoDto;
use ZxArt\Shared\Dto\ReleaseInfoDto;
use ZxArt\Tunes\Rest\TuneDetailsRestDto;

/**
 * Rich DTO consumed by the Angular <zx-tune-details> page. Mirrors the
 * {@see \ZxArt\Pictures\Dto\PictureDetailsDto} conventions. The controller maps it
 * to its REST DTO via the ObjectMapper.
 */
#[Map(target: TuneDetailsRestDto::class)]
readonly class TuneDetailsDto
{
    /**
     * @param AuthorDto[]      $authors
     * @param TuneTagDto[]     $tags
     * @param TuneDownloadDto[] $downloads
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public array $authors,
        public string $format,
        public ?string $year,
        public float $votes,
        public int $votesAmount,
        public ?int $userVote,
        public bool $denyVoting,
        public int $commentsAmount,
        public int $plays,
        public ?PartyInfoDto $party,
        public ?ReleaseInfoDto $release,
        public bool $isPlayable,
        public bool $isRealtime,
        public ?string $compo,
        public ?string $mp3Url,
        public ?string $originalFileUrl,
        public ?string $trackerFileUrl,
        public ?string $description,
        public array $tags,
        public ?TunePartyContextDto $partyContext,
        public ?string $chip,
        public ?string $channelsType,
        public ?int $channels,
        public ?string $duration,
        public ?string $container,
        public ?string $tracker,
        public ?string $internalTitle,
        public ?string $internalAuthor,
        public ?string $frequency,
        public ?string $intFrequency,
        public ?string $fileName,
        public ?string $converterVersion,
        public ?string $dateCreated,
        public ?TuneSubmitterDto $submitter,
        public array $downloads,
    ) {
    }
}
