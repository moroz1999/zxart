<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

/**
 * The Content-Disposition header of a response, which names the file when its
 * address does not.
 */
final readonly class ContentDisposition
{
    /**
     * The encoded name (`filename*`) when the header has one, else the quoted.
     */
    public static function fileName(string $header): ?string
    {
        if (preg_match("#filename\\*=UTF-8''([^;]+)#i", $header, $matches) === 1) {
            return rawurldecode(trim($matches[1]));
        }
        if (preg_match('#filename="([^"]+)"#i', $header, $matches) === 1) {
            return $matches[1];
        }
        return null;
    }
}
