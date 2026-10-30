<?php

declare(strict_types=1);

namespace ZxArt\Shared\Images;

use finfo;

/**
 * Turns bytes into an {@see ImageDto}, if they are an image of a type models
 * take. The type comes from the bytes, not from what their source declares.
 */
final class ImageBytes
{
    public const int MAX_BYTES = 5_000_000;
    private const array MEDIA_TYPES = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];

    /**
     * @throws ImageRefusedException
     */
    public static function toImage(string $bytes, string $source): ImageDto
    {
        $mediaType = (string)(new finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        if (!in_array($mediaType, self::MEDIA_TYPES, true)) {
            throw new ImageRefusedException("Not a PNG, JPEG, GIF or WebP image ($mediaType): $source");
        }

        return new ImageDto($mediaType, base64_encode($bytes));
    }
}
