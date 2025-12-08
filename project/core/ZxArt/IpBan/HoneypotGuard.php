<?php

namespace ZxArt\IpBan;

final class HoneypotGuard
{
    private IpBanService $banService;
    private array $trapPaths;
    private array $whitelistAgents = [
        'google',
        'bing',
        'facebook',
        'yandex',
    ];

    public function __construct(IpBanService $banService, array $trapPaths = [
        '/project/images/public/disk.png', //botnet
        '/about/contact-us/', //honeypot
        '/wp-login.php',
        '/phpmyadmin/',
        '/phpmy/',
        '/pma/',
        '/server-status',
        '/backup.zip',
        '/config.php',
        '/admin.php',
        '/wp-admin/',
    ])
    {
        $this->banService = $banService;
        $this->trapPaths = $trapPaths;
    }

    public function isTrapPath(string $path): bool
    {
        foreach ($this->trapPaths as $trap) {
            if ($trap === $path) {
                return true;
            }
            if (str_ends_with($trap, '/') && str_starts_with($path, rtrim($trap, '/'))) {
                return true;
            }
        }
        return false;
    }

    private function isWhitelisted(?string $userAgent): bool
    {
        if ($userAgent === null || $userAgent === '') {
            return false;
        }

        $uaLower = strtolower($userAgent);
        return array_any($this->whitelistAgents, static fn($allowedAgent) => str_contains($uaLower, $allowedAgent));
    }

    /** If trap hit: ban and return true. */
    public function handle(string $path, string $ip, ?string $userAgent): bool
    {
        if (!$this->isTrapPath($path)) {
            return false;
        }

        if ($this->isWhitelisted($userAgent)) {
            return false;
        }

        $this->banService->ban($ip, 'honeypot', $userAgent, $path);
        return true;
    }
}
