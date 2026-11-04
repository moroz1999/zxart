<?php

declare(strict_types=1);

namespace ZxArt\Releases\ItchIo;

use GuzzleHttp\Cookie\CookieJar;

/**
 * The page of a game that offers its files for download, with the session and
 * the csrf token itch.io ties the download permission to.
 */
final readonly class ItchIoDownloadsPage
{
    /**
     * @param list<ItchIoUpload> $uploads in page order
     */
    public function __construct(
        public ItchIoGameUrl $game,
        public array $uploads,
        public string $csrfToken,
        public CookieJar $session,
    ) {
    }
}
