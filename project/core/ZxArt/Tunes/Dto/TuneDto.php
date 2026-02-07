<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Dto;

use ZxArt\Shared\Dto\AuthorDto;
use ZxArt\Shared\Dto\PartyInfoDto;

readonly class TuneDto
{
    /**
     * @param AuthorDto[] $authors
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
        public bool $isPlayable,
        public bool $isRealtime,
        public ?string $compo,
        public ?string $mp3Url,
    ) {
    }
}
