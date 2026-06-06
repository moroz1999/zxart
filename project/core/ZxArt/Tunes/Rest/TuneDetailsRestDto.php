<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;
use ZxArt\Shared\Dto\AuthorDto;
use ZxArt\Shared\Dto\PartyInfoDto;
use ZxArt\Shared\Dto\ReleaseInfoDto;

readonly class TuneDetailsRestDto
{
    /**
     * @param AuthorDto[]       $authors
     * @param TuneTagRestDto[]  $tags
     * @param TuneDownloadRestDto[] $downloads
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
        #[Map]
        public ?PartyInfoDto $party,
        #[Map]
        public ?ReleaseInfoDto $release,
        public bool $isPlayable,
        public bool $isRealtime,
        public ?string $compo,
        public ?string $mp3Url,
        public ?string $originalFileUrl,
        public ?string $trackerFileUrl,
        public ?string $description,
        #[Map(transform: MapCollection::class)]
        public array $tags,
        #[Map]
        public ?TunePartyContextRestDto $partyContext,
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
        #[Map]
        public ?TuneSubmitterRestDto $submitter,
        #[Map(transform: MapCollection::class)]
        public array $downloads,
    ) {
    }
}
