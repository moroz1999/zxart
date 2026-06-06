<?php

declare(strict_types=1);

namespace ZxArt\MusicList;

use structureManager;
use ZxArt\Shared\SortingParams;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Repositories\TunesRepository;
use ZxArt\Tunes\TunesTransformer;
use zxMusicElement;

readonly class MusicListService
{
    public const array ALLOWED_SORT_COLUMNS = ['title', 'date', 'year', 'votes'];

    public function __construct(
        private structureManager $structureManager,
        private TunesTransformer $tunesTransformer,
        private TunesRepository $tunesRepository,
    ) {
    }

    /**
     * @return TuneDto[]
     */
    public function getTunes(int $elementId, ?string $compoType = null): array
    {
        $element = $this->structureManager->getElementById($elementId)
            ?? $this->structureManager->getElementById($elementId, null, true);
        if ($element === null) {
            return [];
        }

        $musicElements = $this->resolveMusicElements($element, $compoType);

        $result = [];
        foreach ($musicElements as $musicElement) {
            if ($musicElement instanceof zxMusicElement) {
                $result[] = $this->tunesTransformer->toDto($musicElement);
            }
        }
        return $result;
    }

    /**
     * Returns a paginated+sorted page of tunes linked to an element via a given link type.
     *
     * @return array{total: int, items: TuneDto[]}
     */
    public function getPagedByLinkedElement(
        int $elementId,
        string $linkType,
        SortingParams $sorting,
        int $start,
        int $limit,
    ): array {
        $total = $this->tunesRepository->countByLinkedElement($elementId, $linkType);
        $ids = $this->tunesRepository->findPagedByLinkedElement($elementId, $linkType, $sorting, $start, $limit);

        $items = [];
        foreach ($ids as $id) {
            $element = $this->structureManager->getElementById($id)
                ?? $this->structureManager->getElementById($id, null, true);
            if ($element instanceof zxMusicElement) {
                $items[] = $this->tunesTransformer->toDto($element);
            }
        }

        return ['total' => $total, 'items' => $items];
    }

    /**
     * Tunes from the same authors (excluding the tune itself).
     *
     * @return TuneDto[]
     */
    public function getRelatedByAuthors(int $tuneId, int $limit = 6): array
    {
        $element = $this->structureManager->getElementById($tuneId);
        if (!$element instanceof zxMusicElement) {
            return [];
        }
        $authorIds = $element->getAuthorIds() ?: [];
        if ($authorIds === []) {
            return [];
        }
        $ids = $this->tunesRepository->findIdsByAuthorId((int)reset($authorIds));
        return $this->collectTunes($ids, $tuneId, $limit);
    }

    /**
     * Tunes sharing the most tags with this one.
     *
     * @return TuneDto[]
     */
    public function getRelatedByTags(int $tuneId, int $limit = 6): array
    {
        $element = $this->structureManager->getElementById($tuneId);
        if (!$element instanceof zxMusicElement) {
            return [];
        }
        $tagIds = [];
        foreach (($element->getTagsList() ?: []) as $tag) {
            $tagIds[] = (int)$tag->getId();
        }
        if ($tagIds === []) {
            return [];
        }
        $ids = $this->tunesRepository->findSimilarByTags($tuneId, $tagIds, $limit);
        return $this->collectTunes($ids, $tuneId, $limit);
    }

    /**
     * Tunes made with the same tracker/program.
     *
     * @return TuneDto[]
     */
    public function getRelatedByTracker(int $tuneId, int $limit = 6): array
    {
        $element = $this->structureManager->getElementById($tuneId);
        if (!$element instanceof zxMusicElement) {
            return [];
        }
        $program = (string)$element->program;
        if ($program === '') {
            return [];
        }
        $ids = $this->tunesRepository->findByProgram($program, $tuneId, $limit + 1);
        return $this->collectTunes($ids, $tuneId, $limit);
    }

    /**
     * @param int[] $ids
     * @return TuneDto[]
     */
    private function collectTunes(array $ids, int $excludeId, int $limit): array
    {
        $items = [];
        foreach ($ids as $id) {
            if ((int)$id === $excludeId) {
                continue;
            }
            $element = $this->structureManager->getElementById($id)
                ?? $this->structureManager->getElementById($id, null, true);
            if ($element instanceof zxMusicElement) {
                $items[] = $this->tunesTransformer->toDto($element);
            }
            if (count($items) >= $limit) {
                break;
            }
        }
        return $items;
    }

    private function resolveMusicElements(mixed $element, ?string $compoType): array
    {
        if ($compoType !== null && method_exists($element, 'getTunesCompos')) {
            $compos = $element->getTunesCompos();
            return $compos[$compoType] ?? [];
        }
        if (method_exists($element, 'getMusicList')) {
            return (array)($element->getMusicList() ?? []);
        }
        if (method_exists($element, 'getItemsList')) {
            return (array)($element->getItemsList() ?? []);
        }
        if (method_exists($element, 'getTunes')) {
            return (array)($element->getTunes() ?? []);
        }
        if (method_exists($element, 'getItems')) {
            return (array)($element->getItems() ?? []);
        }
        return [];
    }
}
