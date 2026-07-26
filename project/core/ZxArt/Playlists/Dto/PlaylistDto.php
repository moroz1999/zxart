<?php

declare(strict_types=1);

namespace ZxArt\Playlists\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Playlists\Rest\PlaylistRestDto;

#[Map(target: PlaylistRestDto::class)]
readonly class PlaylistDto
{
    public function __construct(
        public int $id,
        public string $title,
        public int $pictures,
        public int $tunes,
        public int $prods,
    ) {
    }
}
