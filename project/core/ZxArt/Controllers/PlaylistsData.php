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
use ZxArt\Playlists\Repositories\PlaylistContentsRepository;
use ZxArt\Shared\StructureType;

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
        private readonly PlaylistContentsRepository $contentsRepository,
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

            $removedId = 0;
            match ((string)$this->getParameter('action')) {
                'create' => $this->createPlaylist((string)($body['title'] ?? ''), $userId),
                'rename' => $this->renamePlaylist((int)($body['id'] ?? 0), (string)($body['title'] ?? ''), $userId),
                'delete' => $removedId = $this->deletePlaylist((int)($body['id'] ?? 0), $userId),
                default => null,
            };

            $this->renderer->assign('body', ['playlists' => $this->listPlaylists($removedId)]);
        } catch (Throwable $e) {
            $this->logThrowable('PlaylistsData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    /**
     * @param int $removedId playlist deleted in this request — the owner's loaded
     *                       list still holds it, so it is skipped here
     * @return list<array{id: int, title: string, pictures: int, tunes: int, prods: int}>
     */
    private function listPlaylists(int $removedId = 0): array
    {
        $elements = [];
        foreach ($this->getOwnedPlaylists() as $playlist) {
            if ($playlist->getId() !== $removedId) {
                $elements[] = $playlist;
            }
        }

        $counts = $this->contentsRepository->getContentCounts(
            array_map(static fn(structureElement $playlist): int => $playlist->getId(), $elements),
        );

        $playlists = [];
        foreach ($elements as $playlist) {
            $id = $playlist->getId();
            $playlistCounts = $counts[$id] ?? [];
            $playlists[] = [
                'id' => $id,
                'title' => html_entity_decode((string)$playlist->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'pictures' => $playlistCounts[StructureType::ZxPicture->value] ?? 0,
                'tunes' => $playlistCounts[StructureType::ZxMusic->value] ?? 0,
                'prods' => $playlistCounts[StructureType::ZxProd->value] ?? 0,
            ];
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

    /** @return int id of the deleted playlist, 0 when nothing was deleted */
    private function deletePlaylist(int $id, int $userId): int
    {
        $playlist = $this->getOwnedPlaylist($id, $userId);
        if ($playlist === null) {
            return 0;
        }
        $playlist->deleteElementData();

        return $id;
    }

    private function getOwnedPlaylist(int $id, int $userId): ?structureElement
    {
        if ($id <= 0) {
            return null;
        }
        foreach ($this->getOwnedPlaylists() as $playlist) {
            if ($playlist->getId() === $id && (int)$playlist->userId === $userId) {
                return $playlist;
            }
        }
        return null;
    }

    /**
     * A playlist hangs off its owner's user element, which is not reachable by a
     * path walk from the site root, so `getElementById()` cannot resolve one.
     * The owner's catalogue loads them directly under itself instead.
     *
     * @return list<structureElement>
     */
    private function getOwnedPlaylists(): array
    {
        $container = $this->getUserPlaylistsElement();
        if ($container === null || !method_exists($container, 'getPlaylists')) {
            return [];
        }

        $playlists = [];
        foreach ($container->getPlaylists() as $playlist) {
            if ($playlist instanceof structureElement) {
                $playlists[] = $playlist;
            }
        }
        return $playlists;
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
