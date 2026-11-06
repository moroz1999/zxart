<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

use CurlHandle;

/**
 * Makes a GET request to a URL given from outside. Every address — the first
 * and each redirect — passes the guard, and the request goes to the address
 * the guard checked, so a second DNS answer cannot lead it elsewhere. The body
 * of the last response goes to a consumer chunk by chunk; the bodies of the
 * redirects are dropped.
 *
 * @psalm-api
 */
final readonly class GuardedHttpClient
{
    private const int MAX_REDIRECTS = 5;
    private const int TIMEOUT_SECONDS = 60;
    private const int CONNECT_TIMEOUT_SECONDS = 10;

    public function __construct(
        private UrlGuard $urlGuard,
    ) {
    }

    /**
     * @param callable(string): bool $consumeChunk false stops the transfer
     * @throws HttpRequestRefusedException
     */
    public function get(string $url, callable $consumeChunk): HttpResponseDto
    {
        $currentUrl = $url;
        for ($redirects = 0; $redirects <= self::MAX_REDIRECTS; $redirects++) {
            try {
                $guardedUrl = $this->urlGuard->check($currentUrl);
            } catch (UnsafeUrlException $exception) {
                throw new HttpRequestRefusedException($exception->getMessage(), 0, $exception);
            }
            [$response, $redirectUrl] = $this->request($guardedUrl, $consumeChunk);
            if ($redirectUrl === null) {
                return $response;
            }
            $currentUrl = $redirectUrl;
        }

        throw new HttpRequestRefusedException("Too many redirects: $url");
    }

    /**
     * @param callable(string): bool $consumeChunk
     * @return array{HttpResponseDto, string|null} the response, and where it redirects to
     * @throws HttpRequestRefusedException
     */
    private function request(GuardedUrlDto $guardedUrl, callable $consumeChunk): array
    {
        $handle = curl_init();
        if ($handle === false) {
            throw new HttpRequestRefusedException('curl could not start');
        }
        $stopped = false;
        $contentDisposition = '';
        curl_setopt_array($handle, [
            CURLOPT_URL => $guardedUrl->url,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT_SECONDS,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => ['Accept-Encoding: identity'],
            CURLOPT_HEADERFUNCTION => static function (CurlHandle $handle, string $header) use (&$contentDisposition): int {
                // each response of the redirects sends its headers; the last one's are kept
                if (str_starts_with($header, 'HTTP/')) {
                    $contentDisposition = '';
                }
                [$name, $value] = array_pad(explode(':', $header, 2), 2, '');
                if (strcasecmp(trim($name), 'Content-Disposition') === 0) {
                    $contentDisposition = trim($value);
                }

                return strlen($header);
            },
            CURLOPT_WRITEFUNCTION => static function (CurlHandle $handle, string $chunk) use ($consumeChunk, &$stopped): int {
                $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
                if ($status >= 300 && $status < 400) {
                    return strlen($chunk);
                }
                if (!$consumeChunk($chunk)) {
                    $stopped = true;
                    // a length other than the chunk's makes curl stop
                    return -1;
                }

                return strlen($chunk);
            },
        ]);
        if (filter_var(trim($guardedUrl->host, '[]'), FILTER_VALIDATE_IP) === false) {
            $address = str_contains($guardedUrl->address, ':') ? '[' . $guardedUrl->address . ']' : $guardedUrl->address;
            curl_setopt($handle, CURLOPT_RESOLVE, [$guardedUrl->host . ':' . $guardedUrl->port . ':' . $address]);
        }
        $succeeded = curl_exec($handle);
        $error = curl_error($handle);
        $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $contentType = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
        $redirectUrl = (string)curl_getinfo($handle, CURLINFO_REDIRECT_URL);

        if ($succeeded === false && !$stopped) {
            throw new HttpRequestRefusedException("Request failed: $error ({$guardedUrl->url})");
        }
        $response = new HttpResponseDto(
            finalUrl: $guardedUrl->url,
            status: $status,
            contentType: is_string($contentType) ? $contentType : '',
            stopped: $stopped,
            contentDisposition: $contentDisposition,
        );
        $redirects = $status >= 300 && $status < 400 && $redirectUrl !== '';

        return [$response, $redirects ? $redirectUrl : null];
    }
}
