<?php
declare(strict_types=1);

namespace ZxArt\Ratings;

use Cache;
use Illuminate\Database\Connection;
use structureManager;
use userElement;
use ZxArt\Comments\CommentAuthorDto;
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
        private Cache $cache,
        private Connection $db,
    ) {
    }

    public function getRecentRatings(int $limit = self::DEFAULT_LIMIT): RecentRatingsListDto
    {
        /** @var RecentRatingsListDto|null $cached */
        $cached = $this->cache->get(self::CACHE_KEY);
        if ($cached instanceof RecentRatingsListDto) {
            return $cached;
        }

        $votes = $this->db->table('votes_history')
            ->select('*')
            ->orderBy('date', 'desc')
            ->limit($limit)
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

        $result = new RecentRatingsListDto($items);
        $this->cache->set(self::CACHE_KEY, $result, self::CACHE_TTL);

        return $result;
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
