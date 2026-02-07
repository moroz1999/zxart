<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Shared\Dto\AuthorDto;
use ZxArt\Shared\Dto\PartyInfoDto;

readonly class ProdRestDto
{
    /**
     * @param AuthorDto[] $authors
     * @param string[] $categories
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
        #[Map]
        public ?PartyInfoDto $party,
        public ?string $legalStatus,
    ) {
    }
}
