<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use ZxArt\Shared\Dto\AuthorDto;
use ZxArt\Shared\Dto\PartyInfoDto;

readonly class ProdDto
{
    /**
     * @param AuthorDto[] $authors
     * @param string[] $categories
     * @param array<array{id: string, title: string}> $hardwareInfo
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?string $year,
        public ?string $imageUrl,
        public float $votes,
        public int $votesAmount,
        public ?int $userVote,
        public bool $denyVoting,
        public array $authors,
        public array $categories,
        public array $hardwareInfo,
        public ?PartyInfoDto $party,
        public ?string $legalStatus,
    ) {
    }
}
