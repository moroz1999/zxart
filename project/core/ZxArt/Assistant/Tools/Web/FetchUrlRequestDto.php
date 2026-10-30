<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Web;

/**
 * @psalm-api
 */
final readonly class FetchUrlRequestDto
{
    /**
     * @param string $url an http or https address
     */
    public function __construct(
        public string $url,
    ) {
    }
}
