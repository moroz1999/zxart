<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Web;

/**
 * @psalm-api
 */
final readonly class ViewImageRequestDto
{
    /**
     * @param string|null $url an http or https address of the image; give this or fileId
     * @param int|null $fileId the id of a file on the site, as the get tools list it; give this or url
     */
    public function __construct(
        public ?string $url = null,
        public ?int $fileId = null,
    ) {
    }
}
