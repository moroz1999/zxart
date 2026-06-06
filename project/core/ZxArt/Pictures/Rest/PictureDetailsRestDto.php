<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;
use ZxArt\Shared\Dto\AuthorDto;
use ZxArt\Shared\Dto\PartyInfoDto;
use ZxArt\Shared\Dto\ReleaseInfoDto;

readonly class PictureDetailsRestDto
{
    /**
     * @param AuthorDto[]                                      $authors
     * @param AuthorDto[]                                      $originalAuthors
     * @param PictureTagRestDto[]                              $tags
     * @param array<int, array{label: string, value: string}> $techInfo
     * @param PictureDownloadRestDto[]                         $downloads
     * @param PictureMaterialRestDto[]                         $materials
     * @param PictureMentionRestDto[]                          $mentions
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public string $imageUrl,
        public string $largeImageUrl,
        public int $fileId,
        public string $type,
        public int $pictureBorder,
        public string $palette,
        public ?int $rotation,
        public ?string $year,
        public array $authors,
        #[Map]
        public ?PartyInfoDto $party,
        #[Map]
        public ?ReleaseInfoDto $release,
        public bool $isRealtime,
        public bool $isFlickering,
        public ?string $compo,
        public float $votes,
        public int $votesAmount,
        public ?int $userVote,
        public bool $denyVoting,
        public int $commentsAmount,
        public ?string $description,
        public array $originalAuthors,
        #[Map(transform: MapCollection::class)]
        public array $tags,
        #[Map]
        public ?PicturePartyContextRestDto $partyContext,
        #[Map]
        public ?PictureProdContextRestDto $prodContext,
        public string $formatLabel,
        public string $paletteLabel,
        public ?string $resolution,
        public ?string $originalName,
        public int $views,
        #[Map]
        public ?PictureSubmitterRestDto $submitter,
        public ?string $dateCreated,
        #[Map(transform: MapCollection::class)]
        public array $downloads,
        #[Map(transform: MapCollection::class)]
        public array $materials,
        public array $techInfo,
        public ?string $sequenceUrl,
        #[Map(transform: MapCollection::class)]
        public array $mentions,
    ) {
    }
}
