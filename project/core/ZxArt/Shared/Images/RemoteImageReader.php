<?php

declare(strict_types=1);

namespace ZxArt\Shared\Images;

use ZxArt\Shared\Http\GuardedHttpClient;
use ZxArt\Shared\Http\HttpRequestRefusedException;

/**
 * Reads an image from a URL given from outside, through
 * {@see GuardedHttpClient}.
 *
 * @psalm-api
 */
final readonly class RemoteImageReader
{
    public function __construct(
        private GuardedHttpClient $httpClient,
    ) {
    }

    /**
     * @throws ImageRefusedException
     */
    public function read(string $url, int $maxBytes = ImageBytes::MAX_BYTES): ImageDto
    {
        $bytes = '';
        try {
            $response = $this->httpClient->get($url, static function (string $chunk) use (&$bytes, $maxBytes): bool {
                $bytes .= $chunk;

                return strlen($bytes) <= $maxBytes;
            });
        } catch (HttpRequestRefusedException $exception) {
            throw new ImageRefusedException($exception->getMessage(), 0, $exception);
        }
        if ($response->stopped) {
            throw new ImageRefusedException("The image is larger than $maxBytes bytes: $url");
        }
        if ($response->status !== 200) {
            throw new ImageRefusedException("HTTP {$response->status}: $url");
        }

        return ImageBytes::toImage($bytes, $url);
    }
}
