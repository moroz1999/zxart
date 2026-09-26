<?php

declare(strict_types=1);

namespace ZxArt\Releases\ItchIo;

/**
 * Address of a game on itch.io: `https://{creator}.itch.io/{game}`. Anything
 * after the game slug is dropped, the endpoints hang off the game address.
 */
final readonly class ItchIoGameUrl
{
    private const string HOST_SUFFIX = '.itch.io';

    private function __construct(public string $gameUrl)
    {
    }

    public static function tryFrom(string $url): ?self
    {
        $host = strtolower((string)parse_url(trim($url), PHP_URL_HOST));
        $slug = explode('/', trim((string)parse_url(trim($url), PHP_URL_PATH), '/'))[0];
        $hasCreator = strlen($host) > strlen(self::HOST_SUFFIX) && str_ends_with($host, self::HOST_SUFFIX);
        if (!$hasCreator || $slug === '') {
            return null;
        }
        return new self('https://' . $host . '/' . $slug);
    }

    public function getFileEndpoint(int $uploadId): string
    {
        return $this->gameUrl . '/file/' . $uploadId;
    }
}
