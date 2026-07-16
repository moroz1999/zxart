<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use App\Users\CurrentUserService;
use CmsHttpResponse;
use controller;
use LanguagesManager;
use linksManager;
use Monolog\Logger;
use privilegesManager;
use structureElement;
use structureManager;
use Throwable;

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
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly CurrentUserService $currentUserService,
    ) {
        parent::__construct($controller, $logger);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    public function execute($controller): void
    {
        try {
            $user = $this->currentUserService->getCurrentUser();
            if (empty($user->id)) {
                $this->assignError('Unauthorized', 401);
                $this->renderer->display();
                return;
            }

            $body = json_decode((string)file_get_contents('php://input'), true);
            $body = is_array($body) ? $body : [];
            $userId = (int)$user->id;

            match ((string)$this->getParameter('action')) {
                'create' => $this->createPlaylist((string)($body['title'] ?? ''), $userId),
                'rename' => $this->renamePlaylist((int)($body['id'] ?? 0), (string)($body['title'] ?? ''), $userId),
                'delete' => $this->deletePlaylist((int)($body['id'] ?? 0), $userId),
                default => null,
            };

            $this->renderer->assign('body', ['playlists' => $this->listPlaylists()]);
        } catch (Throwable $e) {
            $this->logThrowable('PlaylistsData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    /** @return list<array{id: int, title: string}> */
    private function listPlaylists(): array
    {
        $container = $this->getUserPlaylistsElement();
        if ($container === null || !method_exists($container, 'getPlaylists')) {
            return [];
        }
        $playlists = [];
        foreach ($container->getPlaylists() as $playlist) {
            if ($playlist instanceof structureElement) {
                $playlists[] = [
                    'id' => (int)$playlist->getId(),
                    'title' => html_entity_decode((string)$playlist->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                ];
            }
        }
        return $playlists;
    }

    private function createPlaylist(string $title, int $userId): void
    {
        $title = trim($title);
        $container = $this->getUserPlaylistsElement();
        if ($title === '' || $container === null) {
            return;
        }
        $playlist = $this->structureManager->createElement('playlist', 'showForm', $container->getId(), false);
        if ($playlist === null) {
            return;
        }
        $playlist->title = $title;
        $playlist->structureName = $title;
        $playlist->userId = $userId;
        $playlist->persistElementData();

        // own the playlist: link it to the user and grant manage privileges (mirrors receivePlaylist)
        $linksManager = $this->getService(linksManager::class);
        if ($firstParent = $this->structureManager->getElementsFirstParent($playlist->getId())) {
            $linksManager->unLinkElements($firstParent->getId(), $playlist->getId(), 'structure');
        }
        $linksManager->linkElements($userId, $playlist->getId(), 'structure');

        $privilegesManager = $this->getService(privilegesManager::class);
        $privilegesManager->setPrivilege($userId, $playlist->getId(), 'playlist', 'delete', 'allow');
        $privilegesManager->setPrivilege($userId, $playlist->getId(), 'playlist', 'receive', 'allow');
    }

    private function renamePlaylist(int $id, string $title, int $userId): void
    {
        $title = trim($title);
        $playlist = $this->getOwnedPlaylist($id, $userId);
        if ($playlist === null || $title === '') {
            return;
        }
        $playlist->title = $title;
        $playlist->structureName = $title;
        $playlist->persistElementData();
    }

    private function deletePlaylist(int $id, int $userId): void
    {
        $playlist = $this->getOwnedPlaylist($id, $userId);
        $playlist?->deleteElementData();
    }

    private function getOwnedPlaylist(int $id, int $userId): ?structureElement
    {
        if ($id <= 0) {
            return null;
        }
        $playlist = $this->structureManager->getElementById($id);
        if (!$playlist instanceof structureElement || (string)$playlist->structureType !== 'playlist') {
            return null;
        }
        return (int)$playlist->userId === $userId ? $playlist : null;
    }

    private function getUserPlaylistsElement(): ?structureElement
    {
        $elements = $this->structureManager->getElementsByType(
            'userPlaylists',
            $this->languagesManager->getCurrentLanguageId(),
        );
        $element = $elements ? reset($elements) : null;
        return $element instanceof structureElement ? $element : null;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    public function getUrlName(): string
    {
        return '';
    }
}
