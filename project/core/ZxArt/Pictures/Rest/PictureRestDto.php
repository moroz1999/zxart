<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Shared\Dto\AuthorDto;
use ZxArt\Shared\Dto\PartyInfoDto;

readonly class PictureRestDto
{
    /**
     * @param AuthorDto[] $authors
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public string $imageUrl,
        public ?string $year,
        public array $authors,
        #[Map]
        public ?PartyInfoDto $party,
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
