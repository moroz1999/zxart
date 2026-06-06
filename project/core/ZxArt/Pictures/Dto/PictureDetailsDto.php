<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Pictures\Rest\PictureDetailsRestDto;
use ZxArt\Shared\Dto\AuthorDto;
use ZxArt\Shared\Dto\PartyInfoDto;
use ZxArt\Shared\Dto\ReleaseInfoDto;

#[Map(target: PictureDetailsRestDto::class)]
readonly class PictureDetailsDto
{
    /**
     * @param AuthorDto[]              $authors
     * @param AuthorDto[]              $originalAuthors
     * @param PictureTagDto[]          $tags
     * @param array<int, array{label: string, value: string}> $techInfo
     * @param PictureDownloadDto[]     $downloads
     * @param PictureMaterialDto[]     $materials
     * @param PictureMentionDto[]      $mentions
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
        public ?PartyInfoDto $party,
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
        public array $tags,
        public ?PicturePartyContextDto $partyContext,
        public ?PictureProdContextDto $prodContext,
        public string $formatLabel,
        public string $paletteLabel,
        public ?string $resolution,
        public ?string $originalName,
        public int $views,
        public ?PictureSubmitterDto $submitter,
        public ?string $dateCreated,
        public array $downloads,
        public array $materials,
        public array $techInfo,
        public ?string $sequenceUrl,
        public array $mentions,
    ) {
    }
}
