<?php

declare(strict_types=1);

namespace ZxArt\Shared\Images;

/**
 * An image in a form a model takes as image input.
 */
final readonly class ImageDto
{
    public function __construct(
        public string $mediaType,
        /** The bytes in base64. */
        public string $data,
    ) {
    }
}
