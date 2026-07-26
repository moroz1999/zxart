<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use App\Users\CurrentUserService;
use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Playlists\Dto\PlaylistDto;
use ZxArt\Playlists\Exception\PlaylistException;
use ZxArt\Playlists\PlaylistService;
use ZxArt\Playlists\Rest\PlaylistRestDto;

/**
 * Self-service playlists endpoint for the current user (`/playlists-data/`).
 * GET lists the user's playlists; POST `?action=create|rename|delete` manages
 * them. Anonymous users get 401. Named *PlaylistsData* so `/playlists` stays
 * free for the SPA page.
 */
class PlaylistsData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly CurrentUserService $currentUserService,
        private readonly PlaylistService $playlistService,
        private readonly ObjectMapper $objectMapper,
    ) {
        parent::__construct($controller, $logger);
    }

    #[Override]
    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    #[Override]
    public function execute($controller): void
    {
        try {
            $user = $this->currentUserService->getCurrentUser();
            if (empty($user->id) || $user->userName === 'anonymous') {
                $this->assignError('Unauthorized', 401);
                $this->renderer->display();
                return;
            }

            $body = json_decode((string)file_get_contents('php://input'), true);
            $body = is_array($body) ? $body : [];
            $userId = (int)$user->id;

            $action = (string)$this->getParameter('action');
            $removedId = 0;
            match ($action) {
                '' => null,
                'create' => $this->playlistService->create((string)($body['title'] ?? ''), $userId),
                'rename' => $this->playlistService->rename(
                    (int)($body['id'] ?? 0),
                    (string)($body['title'] ?? ''),
                    $userId,
                ),
                'delete' => $removedId = $this->playlistService->delete((int)($body['id'] ?? 0), $userId),
                default => throw new PlaylistException('Unsupported playlist action', 400),
            };

            $this->renderer->assign('body', [
                'playlists' => array_map(
                    fn(PlaylistDto $playlist): PlaylistRestDto => $this->objectMapper->map(
                        $playlist,
                        PlaylistRestDto::class,
                    ),
                    $this->playlistService->getForUser($userId, $removedId),
                ),
            ]);
        } catch (PlaylistException $exception) {
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('PlaylistsData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
