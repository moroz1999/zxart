<?php

declare(strict_types=1);

namespace ZxArt\Releases\ItchIo;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Opens the downloads of an itch.io game and asks the address of one of its
 * files. A free game lists its files with download buttons right on the game
 * page; a game that asks for a donation lists them on a download page, which
 * the "No thanks, just take me to the downloads" link of its prompt opens.
 *
 * @psalm-api
 */
final readonly class ItchIoDownloads
{
    private const float TIMEOUT_SECONDS = 60.0;
    private const string USER_AGENT = 'zxart.ee releases import';

    public function __construct(
        private ItchIoPageParser $parser,
        private Client $httpClient,
    ) {
    }

    /**
     * Refuses downloads that list no file: what itch.io sent then is named,
     * so a page it cut down shows in the error.
     *
     * @throws ItchIoUnavailableException
     */
    public function open(ItchIoGameUrl $game): ItchIoDownloadsPage
    {
        // itch.io ties the download permission and the csrf token to the visitor's session
        $session = new CookieJar();
        $pageUrl = $game->gameUrl;
        $response = $this->get($pageUrl, $session);
        $page = (string)$response->getBody();
        $uploads = $this->parser->findUploads($page);
        if ($uploads === []) {
            $pageUrl = $this->downloadPageUrl($game, $page, $session);
            $response = $this->get($pageUrl, $session);
            $page = (string)$response->getBody();
            $uploads = $this->parser->findUploads($page);
        }
        if ($uploads === []) {
            $title = $this->parser->findTitle($page) ?? '';
            throw new ItchIoUnavailableException("$pageUrl lists no files (HTTP {$response->getStatusCode()}, \"$title\")");
        }
        $csrfToken = $this->parser->findCsrfToken($page);
        if ($csrfToken === null) {
            throw new ItchIoUnavailableException("The downloads of {$game->gameUrl} carry no csrf token");
        }

        return new ItchIoDownloadsPage($game, $uploads, $csrfToken, $session);
    }

    /**
     * A short-lived storage address of the file. A game of a new creator is
     * quarantined by itch.io until reviewed; its visitors are warned and may
     * download anyway, which is what `bypass_quarantine` says.
     *
     * @throws ItchIoUnavailableException
     */
    public function fileUrl(ItchIoDownloadsPage $page, int $uploadId): string
    {
        $endpoint = $page->game->getFileEndpoint($uploadId) . '?source=game_download&bypass_quarantine=true';

        return $this->requestUrl($endpoint, $page->csrfToken, $page->session);
    }

    /**
     * @throws ItchIoUnavailableException
     */
    private function downloadPageUrl(ItchIoGameUrl $game, string $gamePage, CookieJar $session): string
    {
        $endpoint = $this->parser->findDownloadPageEndpoint($gamePage);
        $csrfToken = $this->parser->findCsrfToken($gamePage);
        if ($endpoint === null || $csrfToken === null) {
            throw new ItchIoUnavailableException("{$game->gameUrl} offers no files for download");
        }

        return $this->requestUrl($endpoint, $csrfToken, $session);
    }

    /**
     * @throws ItchIoUnavailableException
     */
    private function get(string $url, CookieJar $session): ResponseInterface
    {
        try {
            $response = $this->httpClient->get($url, [
                'timeout' => self::TIMEOUT_SECONDS,
                'headers' => ['User-Agent' => self::USER_AGENT],
                'cookies' => $session,
            ]);
        } catch (GuzzleException $exception) {
            throw new ItchIoUnavailableException("$url did not open: {$exception->getMessage()}", 0, $exception);
        }

        return $response;
    }

    /**
     * Both endpoints the download needs answer a POST with `{url}`, or with
     * `{errors}` / a lightbox when they refuse.
     *
     * @throws ItchIoUnavailableException
     */
    private function requestUrl(string $url, string $csrfToken, CookieJar $session): string
    {
        try {
            $response = $this->httpClient->post($url, [
                'timeout' => self::TIMEOUT_SECONDS,
                'headers' => ['User-Agent' => self::USER_AGENT],
                'cookies' => $session,
                'form_params' => ['csrf_token' => $csrfToken],
            ]);
        } catch (GuzzleException $exception) {
            throw new ItchIoUnavailableException("$url did not answer: {$exception->getMessage()}", 0, $exception);
        }
        $body = (string)$response->getBody();
        /** @var array{url?: string}|null $decoded */
        $decoded = json_decode($body, true);
        $answeredUrl = $decoded['url'] ?? null;
        if ($answeredUrl === null) {
            throw new ItchIoUnavailableException("$url gave no address: " . mb_substr($body, 0, 200));
        }

        return $answeredUrl;
    }
}
