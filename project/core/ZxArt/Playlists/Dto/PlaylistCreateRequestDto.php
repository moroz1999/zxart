<?php

declare(strict_types=1);

namespace ZxArt\Playlists\Dto;

final readonly class PlaylistCreateRequestDto
{
    public function __construct(public string $title)
    {
    }
}
