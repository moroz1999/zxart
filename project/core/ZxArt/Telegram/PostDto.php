<?php

declare(strict_types=1);

namespace ZxArt\Telegram;

final readonly class PostDto
{
    public function __construct(
        public string $title,
        public string $link,
        public ?string $image = null,
        public ?string $description = null,
        public ?string $audio = null,
    ) {
    }
}
