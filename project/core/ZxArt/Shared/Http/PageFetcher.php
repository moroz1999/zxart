<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

use ValueError;

/**
 * Fetches a page for reading: any status, the body as it came, in UTF-8, cut at
 * a size limit. A binary response gives only its type and size.
 *
 * @psalm-api
 */
final readonly class PageFetcher
{
    public const int MAX_BYTES = 200_000;
    private const int CHARSET_SNIFF_BYTES = 2048;
    private const array TEXT_TYPES = [
        'application/json',
        'application/xml',
        'application/xhtml+xml',
        'application/rss+xml',
        'application/atom+xml',
        'application/javascript',
    ];

    public function __construct(
        private GuardedHttpClient $httpClient,
    ) {
    }

    /**
     * @throws HttpRequestRefusedException
     */
    public function fetch(string $url, int $maxBytes = self::MAX_BYTES): FetchedPageDto
    {
        $body = '';
        $response = $this->httpClient->get($url, static function (string $chunk) use (&$body, $maxBytes): bool {
            $body .= $chunk;
            if (strlen($body) > $maxBytes) {
                $body = substr($body, 0, $maxBytes);

                return false;
            }

            return true;
        });
        $mediaType = strtolower(trim(explode(';', $response->contentType)[0]));

        return new FetchedPageDto(
            finalUrl: $response->finalUrl,
            status: $response->status,
            contentType: $response->contentType,
            body: $this->isText($mediaType) ? $this->toUtf8($body, $this->charset($response->contentType, $mediaType, $body), $response->stopped) : null,
            truncated: $response->stopped,
            size: strlen($body),
        );
    }

    private function isText(string $mediaType): bool
    {
        return $mediaType === ''
            || str_starts_with($mediaType, 'text/')
            || in_array($mediaType, self::TEXT_TYPES, true);
    }

    private function charset(string $contentType, string $mediaType, string $body): string
    {
        if (preg_match('/charset\s*=\s*["\']?([\w.:-]+)/i', $contentType, $matches) === 1) {
            return $matches[1];
        }
        $isHtml = $mediaType === 'text/html' || $mediaType === 'application/xhtml+xml';
        $head = substr($body, 0, self::CHARSET_SNIFF_BYTES);
        if ($isHtml && preg_match('/<meta[^>]+charset\s*=\s*["\']?([\w.:-]+)/i', $head, $matches) === 1) {
            return $matches[1];
        }

        return 'UTF-8';
    }

    private function toUtf8(string $body, string $charset, bool $truncated): string
    {
        try {
            // the cut may fall inside a character, which is at most four bytes long
            for ($dropped = 0; $truncated && $dropped < 3 && !mb_check_encoding($body, $charset); $dropped++) {
                $body = substr($body, 0, -1);
            }
            $body = mb_convert_encoding($body, 'UTF-8', $charset);
        } catch (ValueError) {
            // a charset mbstring does not know: the bytes stay as they came
        }

        return mb_scrub($body, 'UTF-8');
    }
}
