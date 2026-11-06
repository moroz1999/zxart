<?php

declare(strict_types=1);

namespace ZxArt\Releases\ItchIo;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Opens the downloads of an itch.io game and asks the address of one of its
 * files. A free game lists its files with download buttons right on the game
 * page; a game that asks for a donation lists them on a download page, which
 * the "No thanks, just take me to the downloads" link of its prompt opens.
 *
 * itch.io limits how fast it may be asked, so the downloads of a game are
 * opened once per process and every request goes through the throttle.
 *
 * @psalm-api
 */
final class ItchIoDownloads
{
    private const float TIMEOUT_SECONDS = 60.0;
    private const string USER_AGENT = 'zxart.ee releases import';
    private const int TOO_MANY_REQUESTS = 429;

    /** @var array<string, ItchIoDownloadsPage> by game address */
    private array $opened = [];

    public function __construct(
        private readonly ItchIoPageParser $parser,
        private readonly Client $httpClient,
        private readonly ItchIoThrottle $throttle,
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
        return $this->opened[$game->gameUrl] ??= $this->openAnew($game);
    }

    /**
     * @throws ItchIoUnavailableException
     */
    private function openAnew(ItchIoGameUrl $game): ItchIoDownloadsPage
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
     * download anyway, which is what `bypass_quarantine` says. A refusal
     * forgets the opened downloads, as their session may be what failed.
     *
     * @throws ItchIoUnavailableException
     */
    public function fileUrl(ItchIoDownloadsPage $page, int $uploadId): string
    {
        $endpoint = $page->game->getFileEndpoint($uploadId) . '?source=game_download&bypass_quarantine=true';
        try {
            return $this->requestUrl($endpoint, $page->csrfToken, $page->session);
        } catch (ItchIoUnavailableException $exception) {
            unset($this->opened[$page->game->gameUrl]);
            throw $exception;
        }
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
            $response = $this->send('GET', $url, [
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
            $response = $this->send('POST', $url, [
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

    /**
     * A "429 Too Many Requests" is waited out and the request sent once more.
     *
     * @param array<string, mixed> $options
     * @throws GuzzleException
     */
    private function send(string $method, string $url, array $options): ResponseInterface
    {
        $this->throttle->beforeRequest();
        try {
            return $this->httpClient->request($method, $url, $options);
        } catch (ClientException $exception) {
            $refusal = $exception->getResponse();
            if ($refusal->getStatusCode() !== self::TOO_MANY_REQUESTS) {
                throw $exception;
            }
            $this->throttle->afterRefusal($refusal->getHeaderLine('Retry-After'));
        }
        $this->throttle->beforeRequest();

        return $this->httpClient->request($method, $url, $options);
    }
}
