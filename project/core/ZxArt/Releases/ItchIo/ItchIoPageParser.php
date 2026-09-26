<?php

declare(strict_types=1);

namespace ZxArt\Releases\ItchIo;

/**
 * Reads the parts of an itch.io game or download page the download needs.
 * A free game lists its files with download buttons right on the game page. A
 * game that asks for a donation only names them there; the buttons are on a
 * download page, which the "No thanks, just take me to the downloads" link of
 * the donation prompt opens.
 */
final readonly class ItchIoPageParser
{
    private const string UPLOAD_MARKER = '<div class="upload">';
    private const string PLATFORMS_MARKER = '<span class="download_platforms">';

    public function findCsrfToken(string $html): ?string
    {
        if (preg_match('#name="csrf_token" value="([^"]+)"#', $html, $matches) !== 1) {
            return null;
        }
        return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Files with a download button, in page order.
     *
     * @return list<ItchIoUpload>
     */
    public function findUploads(string $html): array
    {
        $uploads = [];
        foreach (array_slice(explode(self::UPLOAD_MARKER, $html), 1) as $block) {
            $hasId = preg_match('#data-upload_id="(\d+)"#', $block, $idMatches) === 1;
            $hasName = preg_match('#<strong title="([^"]+)" class="name">#', $block, $nameMatches) === 1;
            if ($hasId && $hasName) {
                $uploads[] = new ItchIoUpload(
                    (int)$idMatches[1],
                    html_entity_decode($nameMatches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    $this->isNativeBuild($block),
                );
            }
        }
        return $uploads;
    }

    /**
     * The file name the storage sends the file under, which is the only place a
     * relabelled upload's real name shows.
     */
    public function findFileName(string $contentDisposition): ?string
    {
        if (preg_match("#filename\\*=UTF-8''([^;]+)#i", $contentDisposition, $matches) === 1) {
            return rawurldecode(trim($matches[1]));
        }
        if (preg_match('#filename="([^"]+)"#i', $contentDisposition, $matches) === 1) {
            return $matches[1];
        }
        return null;
    }

    /**
     * A native build carries a platform icon ("Download for Windows") beside
     * its name.
     */
    private function isNativeBuild(string $block): bool
    {
        $platformsStart = strpos($block, self::PLATFORMS_MARKER);
        if ($platformsStart === false) {
            return false;
        }
        $platforms = substr($block, $platformsStart + strlen(self::PLATFORMS_MARKER));
        return str_contains(explode('</div>', $platforms)[0], 'class="icon ');
    }

    /**
     * Endpoint that answers with the address of the download page, which the
     * donation prompt's "No thanks" link asks for.
     */
    public function findDownloadPageEndpoint(string $html): ?string
    {
        if (preg_match('#"generate_download_url":"([^"]+)"#', $html, $matches) !== 1) {
            return null;
        }
        // a JSON string in a script: only its slashes are escaped
        return str_replace('\\/', '/', $matches[1]);
    }
}
