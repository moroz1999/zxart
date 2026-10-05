<?php
declare(strict_types=1);

namespace ZxArt\Comments;

use Cache;
use LanguagesManager;

/**
 * Shared cache of the latest-comments widget list, one entry per public language.
 * Unknown language codes are never cached, so request input cannot multiply keys.
 */
readonly class LatestCommentsCache
{
    private const string KEY_PREFIX = 'latest_comments_';
    private const int TTL = 300;

    public function __construct(
        private Cache $cache,
        private LanguagesManager $languagesManager,
    ) {
    }

    /**
     * @return CommentDto[]|null
     */
    public function get(string $languageCode): ?array
    {
        if (!$this->isKnownLanguage($languageCode)) {
            return null;
        }
        /** @var CommentDto[]|null $comments */
        $comments = $this->cache->get(self::KEY_PREFIX . $languageCode);
        return is_array($comments) ? $comments : null;
    }

    /**
     * @param CommentDto[] $comments
     */
    public function set(string $languageCode, array $comments): void
    {
        if (!$this->isKnownLanguage($languageCode)) {
            return;
        }
        $this->cache->set(self::KEY_PREFIX . $languageCode, $comments, self::TTL);
    }

    public function clear(): void
    {
        foreach ($this->getLanguageCodes() as $languageCode) {
            $this->cache->delete(self::KEY_PREFIX . $languageCode);
        }
    }

    private function isKnownLanguage(string $languageCode): bool
    {
        return in_array($languageCode, $this->getLanguageCodes(), true);
    }

    /**
     * @return string[]
     */
    private function getLanguageCodes(): array
    {
        /** @var array<string, int> $map */
        $map = $this->languagesManager->getLanguagesIdsMap();
        return array_map('strval', array_keys($map));
    }
}
