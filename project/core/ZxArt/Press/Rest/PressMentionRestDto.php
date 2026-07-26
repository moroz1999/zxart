<?php

declare(strict_types=1);

namespace ZxArt\Press\Rest;

/** A single linked entity mentioned by a press article (author, group, prod, …). */
readonly class PressMentionRestDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
    ) {
    }
}
