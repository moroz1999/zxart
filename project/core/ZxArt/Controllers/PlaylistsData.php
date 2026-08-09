<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use App\Users\CurrentUserService;
use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use ZxArt\Playlists\Dto\PlaylistCreateRequestDto;
use ZxArt\Playlists\Dto\PlaylistDeleteRequestDto;
use ZxArt\Playlists\Dto\PlaylistDto;
use ZxArt\Playlists\Dto\PlaylistRenameRequestDto;
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
        private readonly SerializerInterface $serializer,
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

            $userId = (int)$user->id;

            $action = (string)$this->getParameter('action');
            $removedId = 0;
            match ($action) {
                '' => null,
                'create' => $this->createPlaylist($userId),
                'rename' => $this->renamePlaylist($userId),
                'delete' => $removedId = $this->deletePlaylist($userId),
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
        } catch (SerializerException $exception) {
            $this->assignError($exception->getMessage(), 400);
        } catch (PlaylistException $exception) {
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('PlaylistsData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function createPlaylist(int $userId): void
    {
        $request = $this->deserialize(PlaylistCreateRequestDto::class);
        $this->playlistService->create($request->title, $userId);
    }

    private function renamePlaylist(int $userId): void
    {
        $request = $this->deserialize(PlaylistRenameRequestDto::class);
        $this->playlistService->rename($request->id, $request->title, $userId);
    }

    private function deletePlaylist(int $userId): int
    {
        $request = $this->deserialize(PlaylistDeleteRequestDto::class);
        return $this->playlistService->delete($request->id, $userId);
    }

    /**
     * @template T of object
     * @param class-string<T> $requestClass
     * @return T
     */
    private function deserialize(string $requestClass): object
    {
        return $this->serializer->deserialize(file_get_contents('php://input'), $requestClass, 'json');
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
