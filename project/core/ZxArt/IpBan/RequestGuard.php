<?php
declare(strict_types=1);


namespace ZxArt\IpBan;

final readonly class RequestGuard
{
    public function __construct(
        private IpBanService  $ipBanService,
        private HoneypotGuard $honeypotGuard,
        private UAGuard       $UAGuard,
    )
    {

    }

    public function isAllowed(array $requestInfo): bool
    {
        $path = parse_url($requestInfo['REQUEST_URI'], PHP_URL_PATH) ?: '/';
        $ip = $requestInfo['REMOTE_ADDR'] ?? '';
        $userAgent = $requestInfo['HTTP_USER_AGENT'] ?? '';

        if ($this->ipBanService->isBanned($ip)) {
            return false;
        }

        if ($this->honeypotGuard->handle($path, $ip, $userAgent)) {
            return false;
        }

        if ($this->UAGuard->handle($ip, $userAgent, $path)) {
            return false;
        }

        if (array_key_exists('mdrv', $_GET)){
            $this->ipBanService->ban($ip, 'abuse', $userAgent, $path);
            return false;
        }

        if (str_starts_with($ip, '34.174.')) {
            $this->ipBanService->ban($ip, 'abuse', $userAgent, $path);
            return false;
        }
        if (str_starts_with($ip, '52.167.144.')) {
            $this->ipBanService->ban($ip, 'abuse', $userAgent, $path);
            return false;
        }

        return true;
    }
}