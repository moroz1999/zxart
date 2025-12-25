<?php
declare(strict_types=1);


namespace ZxArt\IpBan;

use Cache;

/**
 * Permanent IP ban service with Redis-backed cache (your Cache class).
 * Positive bans are cached forever (ttl=0), negative results are cached shortly to reduce DB reads.
 */
final readonly class IpBanService
{
    private string $cachePrefix;
    private int $ttl;

    public function __construct(
        private IpBanRepository $repository,
        private Cache           $cache
    )
    {
        $this->cache->enable(true, true, true);
        $this->cachePrefix = 'ipban:';
        $this->ttl = 60 * 5;
    }

    public function isBanned(string $ip): bool
    {
        $key = $this->cacheKey($ip);

        $cached = $this->cache->get($key);
        if ($cached !== null) {
            return (bool)$cached;
        }

        $banned = $this->repository->isBanned($ip);
        $this->cache->set($key, $banned, $banned ? 60 * 60 * 24 : $this->ttl);

        return $banned;
    }

    public function ban(string $ip, string $reason = 'honeypot', ?string $userAgent = null, ?string $path = null): void
    {
        $this->repository->ban($ip, $reason, $userAgent, $path);
        $this->cache->set($this->cacheKey($ip), true, $this->ttl);
    }

    public function unban(string $ip): void
    {
        $this->repository->unban($ip);
        $this->cache->delete($this->cacheKey($ip));
    }

    private function cacheKey(string $ip): string
    {
        return $this->cachePrefix . $ip;
    }
}
