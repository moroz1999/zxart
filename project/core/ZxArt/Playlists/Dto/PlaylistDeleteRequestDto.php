<?php

declare(strict_types=1);

namespace ZxArt\Playlists\Dto;

final readonly class PlaylistDeleteRequestDto
{
    public function __construct(public int $id)
    {
    }
}
