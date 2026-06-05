<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Pictures\Rest\PictureRestDto;
use ZxArt\Shared\Dto\AuthorDto;
use ZxArt\Shared\Dto\PartyInfoDto;
use ZxArt\Shared\Dto\ReleaseInfoDto;

#[Map(target: PictureRestDto::class)]
readonly class PictureDto
{
    /**
     * @param AuthorDto[] $authors
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
    ) {
    }
}
