<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

use CurlHandle;

/**
 * Downloads a file from a URL given from outside. Every address — the first
 * and each redirect — passes the guard, and the request goes to the address
 * the guard checked, so a second DNS answer cannot lead it elsewhere. A file
 * over the size limit is dropped as soon as it grows past it.
 *
 * @psalm-api
 */
final readonly class GuardedDownloader
{
    private const int MAX_REDIRECTS = 5;
    private const int TIMEOUT_SECONDS = 60;
    private const int CONNECT_TIMEOUT_SECONDS = 10;

    public function __construct(
        private UrlGuard $urlGuard,
    ) {
    }

    /**
     * @throws DownloadRefusedException
     */
    public function download(string $url, string $destination, int $maxBytes): DownloadedFileDto
    {
        $currentUrl = $url;
        for ($redirects = 0; $redirects <= self::MAX_REDIRECTS; $redirects++) {
            try {
                $guardedUrl = $this->urlGuard->check($currentUrl);
            } catch (UnsafeUrlException $exception) {
                $this->discard($destination);
                throw new DownloadRefusedException($exception->getMessage(), 0, $exception);
            }
            $redirectUrl = $this->fetch($guardedUrl, $destination, $maxBytes);
            if ($redirectUrl === null) {
                $size = filesize($destination);

                return new DownloadedFileDto(
                    finalUrl: $currentUrl,
                    contentType: (string)mime_content_type($destination),
                    size: $size === false ? 0 : $size,
                );
            }
            $currentUrl = $redirectUrl;
        }
        $this->discard($destination);

        throw new DownloadRefusedException("Too many redirects: $url");
    }

    /**
     * @return string|null where the response redirects to, null once the file is stored
     * @throws DownloadRefusedException
     */
    private function fetch(GuardedUrlDto $guardedUrl, string $destination, int $maxBytes): ?string
    {
        $file = fopen($destination, 'wb');
        if ($file === false) {
            throw new DownloadRefusedException("Cannot write $destination");
        }
        $received = 0;
        $handle = curl_init();
        if ($handle === false) {
            fclose($file);
            throw new DownloadRefusedException('curl could not start');
        }
        curl_setopt_array($handle, [
            CURLOPT_URL => $guardedUrl->url,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT_SECONDS,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => ['Accept-Encoding: identity'],
            CURLOPT_WRITEFUNCTION => static function (CurlHandle $handle, string $chunk) use ($file, &$received, $maxBytes): int {
                $received += strlen($chunk);
                if ($received > $maxBytes) {
                    // a length other than the chunk's makes curl stop
                    return -1;
                }

                return (int)fwrite($file, $chunk);
            },
        ]);
        if (filter_var(trim($guardedUrl->host, '[]'), FILTER_VALIDATE_IP) === false) {
            $address = str_contains($guardedUrl->address, ':') ? '[' . $guardedUrl->address . ']' : $guardedUrl->address;
            curl_setopt($handle, CURLOPT_RESOLVE, [$guardedUrl->host . ':' . $guardedUrl->port . ':' . $address]);
        }
        $succeeded = curl_exec($handle);
        $error = curl_error($handle);
        $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $redirectUrl = (string)curl_getinfo($handle, CURLINFO_REDIRECT_URL);
        curl_close($handle);
        fclose($file);

        if ($received > $maxBytes) {
            $this->discard($destination);
            throw new DownloadRefusedException("The file is larger than $maxBytes bytes: {$guardedUrl->url}");
        }
        if ($succeeded === false) {
            $this->discard($destination);
            throw new DownloadRefusedException("Download failed: $error ({$guardedUrl->url})");
        }
        if ($status >= 300 && $status < 400 && $redirectUrl !== '') {
            return $redirectUrl;
        }
        if ($status !== 200) {
            $this->discard($destination);
            throw new DownloadRefusedException("Download failed with HTTP $status: {$guardedUrl->url}");
        }

        return null;
    }

    private function discard(string $destination): void
    {
        if (is_file($destination)) {
            unlink($destination);
        }
    }
}
