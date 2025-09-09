<?php
declare(strict_types=1);

namespace ZxArt\IpBan;

use Illuminate\Database\Connection;

/** DB repository with no business logic. */
final readonly class IpBanRepository
{
    private string $table;

    public function __construct(private Connection $db)
    {
        $this->table = 'ip_bans';
    }

    /** True if row exists for given IP string. */
    public function isBanned(string $ip): bool
    {
        return $this->db->table($this->table)
            ->where('ip', $ip)
            ->exists();
    }

    /**
     * Insert ban; ignore if already exists.
     * Stores user agent and requested path (both trimmed).
     */
    public function ban(string $ip, string $reason, ?string $userAgent, ?string $path): void
    {
        $ua = $this->trimUserAgent($userAgent);
        $p  = $this->trimPath($path);

        $tableName = $this->db->getTablePrefix() . $this->table;
        $sql = "INSERT IGNORE INTO $tableName (ip, reason, user_agent, path) VALUES (?, ?, ?, ?)";
        $this->db->insert($sql, [$ip, $reason, $ua, $p]);
    }

    /** Delete ban row. */
    public function unban(string $ip): void
    {
        $this->db->table($this->table)->where('ip', $ip)->delete();
    }

    /** Normalize UA to sane length. */
    private function trimUserAgent(?string $ua): ?string
    {
        if ($ua === null) {
            return null;
        }
        $ua = trim($ua);
        if ($ua === '') {
            return null;
        }
        return function_exists('mb_substr') ? mb_substr($ua, 0, 512) : substr($ua, 0, 512);
    }

    /** Normalize path to sane length and basic shape. */
    private function trimPath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }
        $path = trim($path);
        if ($path === '') {
            return null;
        }
        // Keep path only; strip scheme/host if someone fed a full URL.
        $parsed = parse_url($path);
        $onlyPath = $parsed['path'] ?? $path;
        if (isset($parsed['query']) && $parsed['query'] !== '') {
            $onlyPath .= '?' . $parsed['query'];
        }
        return function_exists('mb_substr') ? mb_substr($onlyPath, 0, 1024) : substr($onlyPath, 0, 1024);
    }
}
