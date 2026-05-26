<?php
declare(strict_types=1);

namespace ZxArt\Ratings;

use Cache;
use Illuminate\Database\Connection;
use LanguagesManager;
use structureManager;
use userElement;
use ZxArt\Comments\CommentAuthorDto;
use ZxArt\Ratings\Dto\AuthorRatingsListDto;
use ZxArt\Ratings\Dto\ElementRatingDto;
use ZxArt\Ratings\Dto\ElementRatingsListDto;
use ZxArt\Ratings\Dto\RecentRatingDto;
use ZxArt\Ratings\Dto\RecentRatingsListDto;
use ZxArtItem;

readonly class RatingsService
{
    private const int DEFAULT_LIMIT = 20;
    private const string CACHE_KEY = 'recent_ratings';
    private const int CACHE_TTL = 300;

    public function __construct(
        private structureManager $structureManager,
        private LanguagesManager $languagesManager,
        private Cache $cache,
        private Connection $db,
    ) {
    }

    public function getRecentRatings(int $limit = self::DEFAULT_LIMIT, int $offset = 0): RecentRatingsListDto
    {
        $cacheKey = self::CACHE_KEY . '_' . $this->languagesManager->getCurrentLanguageId();

        if ($offset === 0) {
            /** @var RecentRatingsListDto|null $cached */
            $cached = $this->cache->get($cacheKey);
            if ($cached instanceof RecentRatingsListDto) {
                return $cached;
            }
        }

        $votes = $this->db->table('votes_history')
            ->select('*')
            ->orderBy('date', 'desc')
            ->limit($limit + 1)
            ->offset($offset)
            ->get();

        $items = [];
        /** @var object $vote */
        foreach ($votes as $vote) {
            $voteArray = (array)$vote;
            $elementId = (int)($voteArray['elementId'] ?? 0);
            $userId = (int)($voteArray['userId'] ?? 0);
            $value = (int)($voteArray['value'] ?? 0);

            $targetElement = $this->structureManager->getElementById($elementId);
            if (!$targetElement instanceof ZxArtItem || $targetElement->isVotingDenied()) {
                continue;
            }

            // directlyToParent=true bypasses path resolution —
            // user elements are not reachable via loadFromShortestPath
            // because they sit outside the current language's URL tree.
            $userElement = $this->structureManager->getElementById($userId, null, true);
            if (!$userElement instanceof userElement) {
                continue;
            }

            $rating = $value === 0 ? 'x' : (string)$value;

            $items[] = new RecentRatingDto(
                user: new CommentAuthorDto(
                    name: html_entity_decode((string)$userElement->getTitle(), ENT_QUOTES),
                    url: (string)$userElement->getUrl(),
                    badges: $userElement->getBadgetTypes(),
                ),
                rating: $rating,
                targetTitle: html_entity_decode((string)$targetElement->getTitle(), ENT_QUOTES),
                targetUrl: (string)$targetElement->getUrl(),
            );
        }

        $hasMore = count($items) > $limit;
        if ($hasMore) {
            $items = array_slice($items, 0, $limit);
        }

        $result = new RecentRatingsListDto($items, $hasMore);
        if ($offset === 0) {
            $this->cache->set($cacheKey, $result, self::CACHE_TTL);
        }

        return $result;
    }

    public function getAuthorRatings(int $authorId, int $page, int $perPage): AuthorRatingsListDto
    {
        $authorIds = $this->getAuthorAndAliasIds($authorId);
        $workIds = $this->getAuthorWorkIds($authorIds);

        if (empty($workIds)) {
            return new AuthorRatingsListDto([], 1, 0, 0);
        }

        $total = $this->db->table('votes_history')
            ->whereIn('elementId', $workIds)
            ->where('value', '!=', 0)
            ->count();

        $pagesAmount = $total > 0 ? (int)ceil($total / $perPage) : 1;
        $page = max(1, min($page, $pagesAmount));
        $offset = ($page - 1) * $perPage;

        $votes = $this->db->table('votes_history')
            ->whereIn('elementId', $workIds)
            ->where('value', '!=', 0)
            ->orderBy('date', 'desc')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        $items = [];
        /** @var object $vote */
        foreach ($votes as $vote) {
            $voteArray = (array)$vote;
            $elementId = (int)($voteArray['elementId'] ?? 0);
            $userId = (int)($voteArray['userId'] ?? 0);
            $value = (int)($voteArray['value'] ?? 0);

            $targetElement = $this->structureManager->getElementById($elementId);
            if (!$targetElement instanceof ZxArtItem || $targetElement->isVotingDenied()) {
                continue;
            }

            $userElement = $this->structureManager->getElementById($userId, null, true);
            if (!$userElement instanceof userElement) {
                continue;
            }

            $items[] = new RecentRatingDto(
                user: new CommentAuthorDto(
                    name: html_entity_decode((string)$userElement->getTitle(), ENT_QUOTES),
                    url: (string)$userElement->getUrl(),
                    badges: $userElement->getBadgetTypes(),
                ),
                rating: (string)$value,
                targetTitle: html_entity_decode((string)$targetElement->getTitle(), ENT_QUOTES),
                targetUrl: (string)$targetElement->getUrl(),
            );
        }

        return new AuthorRatingsListDto($items, $page, $pagesAmount, $total);
    }

    public function invalidateRecentRatingsCache(): void
    {
        foreach ($this->languagesManager->getLanguagesIdList() as $languageId) {
            $this->cache->delete(self::CACHE_KEY . '_' . $languageId);
        }
    }

    /**
     * @return int[]
     */
    private function getAuthorAndAliasIds(int $authorId): array
    {
        /** @var int[] $aliasIds */
        $aliasIds = $this->db->table('module_authoralias')
            ->where('authorId', '=', $authorId)
            ->pluck('id');
        return [$authorId, ...$aliasIds];
    }

    /**
     * @param int[] $authorIds
     * @return int[]
     */
    private function getAuthorWorkIds(array $authorIds): array
    {
        /** @var int[] $pictureAndTuneIds */
        $pictureAndTuneIds = $this->db->table('structure_links')
            ->whereIn('parentStructureId', $authorIds)
            ->whereIn('type', ['authorPicture', 'authorMusic'])
            ->pluck('childStructureId');

        /** @var int[] $prodIds */
        $prodIds = $this->db->table('authorship')
            ->whereIn('authorId', $authorIds)
            ->distinct()
            ->pluck('elementId');

        return array_values(array_unique(array_merge($pictureAndTuneIds, $prodIds)));
    }

    public function getElementRatings(int $elementId): ElementRatingsListDto
    {
        $votes = $this->db->table('votes_history')
            ->select('*')
            ->where('elementId', '=', $elementId)
            ->where('value', '!=', 0)
            ->orderBy('date', 'desc')
            ->get();

        $items = [];
        /** @var object $vote */
        foreach ($votes as $vote) {
            $voteArray = (array)$vote;
            $userId = (int)($voteArray['userId'] ?? 0);
            $value = (int)($voteArray['value'] ?? 0);
            $date = (int)($voteArray['date'] ?? 0);

            $userElement = $this->structureManager->getElementById($userId, null, true);
            if (!$userElement instanceof userElement) {
                continue;
            }

            $items[] = new ElementRatingDto(
                user: new CommentAuthorDto(
                    name: html_entity_decode((string)$userElement->getTitle(), ENT_QUOTES),
                    url: (string)$userElement->getUrl(),
                    badges: $userElement->getBadgetTypes(),
                ),
                rating: (string)$value,
                date: date('Y-m-d H:i', $date),
            );
        }

        return new ElementRatingsListDto($items);
    }
}
