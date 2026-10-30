<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

/**
 * Downloads a file from a URL given from outside through
 * {@see GuardedHttpClient}. A file over the size limit is dropped as soon as it
 * grows past it.
 *
 * @psalm-api
 */
final readonly class GuardedDownloader
{
    public function __construct(
        private GuardedHttpClient $httpClient,
    ) {
    }

    /**
     * @throws DownloadRefusedException
     */
    public function download(string $url, string $destination, int $maxBytes): DownloadedFileDto
    {
        $file = fopen($destination, 'wb');
        if ($file === false) {
            throw new DownloadRefusedException("Cannot write $destination");
        }
        $received = 0;
        try {
            $response = $this->httpClient->get($url, static function (string $chunk) use ($file, &$received, $maxBytes): bool {
                $received += strlen($chunk);

                return $received <= $maxBytes && fwrite($file, $chunk) === strlen($chunk);
            });
        } catch (HttpRequestRefusedException $exception) {
            fclose($file);
            $this->discard($destination);
            throw new DownloadRefusedException($exception->getMessage(), 0, $exception);
        }
        fclose($file);

        if ($received > $maxBytes) {
            $this->discard($destination);
            throw new DownloadRefusedException("The file is larger than $maxBytes bytes: {$response->finalUrl}");
        }
        if ($response->stopped) {
            $this->discard($destination);
            throw new DownloadRefusedException("Cannot write $destination");
        }
        if ($response->status !== 200) {
            $this->discard($destination);
            throw new DownloadRefusedException("Download failed with HTTP {$response->status}: {$response->finalUrl}");
        }
        $size = filesize($destination);

        return new DownloadedFileDto(
            finalUrl: $response->finalUrl,
            contentType: (string)mime_content_type($destination),
            size: $size === false ? 0 : $size,
        );
    }

    private function discard(string $destination): void
    {
        if (is_file($destination)) {
            unlink($destination);
        }
    }
}
