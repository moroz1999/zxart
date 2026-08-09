<?php

declare(strict_types=1);

namespace ZxArt\Playlists\Dto;

final readonly class PlaylistRenameRequestDto
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
