<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdVotingRestDto;

#[Map(target: ProdVotingRestDto::class)]
readonly class ProdVotingDto
{
    public function __construct(
        public float $votes,
        public int $votesAmount,
        public ?int $userVote,
        public bool $denyVoting,
        public ?float $votePercent,
    ) {
    }
}
