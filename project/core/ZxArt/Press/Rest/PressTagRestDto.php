<?php

declare(strict_types=1);

namespace ZxArt\Press\Rest;

/** A tag link shown on a press article. */
readonly class PressTagRestDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
