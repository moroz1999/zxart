<?php

declare(strict_types=1);

namespace ZxArt\Shared\Rest;

final readonly class MentionedEntityRestDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
