<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdVotingRestDto
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
