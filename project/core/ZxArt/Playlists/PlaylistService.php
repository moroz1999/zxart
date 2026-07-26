<?php

declare(strict_types=1);

namespace ZxArt\Playlists;

use LanguagesManager;
use linksManager;
use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\Playlists\Dto\PlaylistDto;
use ZxArt\Playlists\Exception\PlaylistException;
use ZxArt\Playlists\Repositories\PlaylistContentsRepository;
use ZxArt\Shared\StructureType;

readonly class PlaylistService
{
    public function __construct(
        private structureManager $structureManager,
        private LanguagesManager $languagesManager,
        private PlaylistContentsRepository $contentsRepository,
        private linksManager $linksManager,
        private privilegesManager $privilegesManager,
    ) {
    }

    /** @return list<PlaylistDto> */
    public function getForUser(int $userId, int $excludedId = 0): array
    {
        if ($userId <= 0) {
            throw new PlaylistException('Unauthorized', 401);
        }
        $elements = array_values(array_filter(
            $this->getOwnedPlaylists(),
            static fn(structureElement $playlist): bool => $playlist->getId() !== $excludedId,
        ));
        $counts = $this->contentsRepository->getContentCounts(
            array_map(static fn(structureElement $playlist): int => $playlist->getId(), $elements),
        );

        return array_map(
            static function (structureElement $playlist) use ($counts): PlaylistDto {
                $id = $playlist->getId();
                $playlistCounts = $counts[$id] ?? [];
                return new PlaylistDto(
                    id: $id,
                    title: html_entity_decode((string)$playlist->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    pictures: $playlistCounts[StructureType::ZxPicture->value] ?? 0,
                    tunes: $playlistCounts[StructureType::ZxMusic->value] ?? 0,
                    prods: $playlistCounts[StructureType::ZxProd->value] ?? 0,
                );
            },
            $elements,
        );
    }

    public function create(string $title, int $userId): void
    {
        $title = trim($title);
        if ($title === '') {
            throw new PlaylistException('Playlist title is required', 422);
        }
        $container = $this->getUserPlaylistsElement();
        if ($container === null) {
            throw new PlaylistException('Playlist container not found', 500);
        }
        $playlist = $this->structureManager->createElement('playlist', 'showForm', $container->getId(), false);
        if (!$playlist instanceof structureElement) {
            throw new PlaylistException('Could not create playlist', 500);
        }
        $playlist->title = $title;
        $playlist->structureName = $title;
        $playlist->userId = $userId;
        $playlist->persistElementData();

        $firstParent = $this->structureManager->getElementsFirstParent($playlist->getId());
        if ($firstParent instanceof structureElement) {
            $this->linksManager->unLinkElements($firstParent->getId(), $playlist->getId(), 'structure');
        }
        $this->linksManager->linkElements($userId, $playlist->getId(), 'structure');
        $this->privilegesManager->setPrivilege($userId, $playlist->getId(), 'playlist', 'delete', 'allow');
        $this->privilegesManager->setPrivilege($userId, $playlist->getId(), 'playlist', 'receive', 'allow');
    }

    public function rename(int $id, string $title, int $userId): void
    {
        $title = trim($title);
        if ($title === '') {
            throw new PlaylistException('Playlist title is required', 422);
        }
        $playlist = $this->requireOwnedPlaylist($id, $userId);
        $playlist->title = $title;
        $playlist->structureName = $title;
        $playlist->persistElementData();
    }

    public function delete(int $id, int $userId): int
    {
        $this->requireOwnedPlaylist($id, $userId)->deleteElementData();

        return $id;
    }

    private function requireOwnedPlaylist(int $id, int $userId): structureElement
    {
        foreach ($this->getOwnedPlaylists() as $playlist) {
            if ($playlist->getId() === $id && (int)$playlist->userId === $userId) {
                return $playlist;
            }
        }
        throw new PlaylistException('Playlist not found', 404);
    }

    /** @return list<structureElement> */
    private function getOwnedPlaylists(): array
    {
        $container = $this->getUserPlaylistsElement();
        if ($container === null || !method_exists($container, 'getPlaylists')) {
            return [];
        }

        return array_values(array_filter(
            $container->getPlaylists(),
            static fn(mixed $playlist): bool => $playlist instanceof structureElement,
        ));
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
}
