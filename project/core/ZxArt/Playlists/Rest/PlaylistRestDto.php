<?php

declare(strict_types=1);

namespace ZxArt\Playlists\Rest;

readonly class PlaylistRestDto
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
