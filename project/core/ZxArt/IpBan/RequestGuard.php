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

        if (array_key_exists('mdrv', $_GET)) {
            $this->ipBanService->ban($ip, 'abuse', $userAgent, $path);
            return false;
        }

        if (str_starts_with($ip, '34.174.')) {
            return false;
        }

        if ($this->isIpInRange($ip, '47.79.192.0/19')){
            return false;
        }

        return true;
    }

    protected function isIpInRange(string $ip, string $cidr): bool
    {
        // convert IP to long
        $ipAsLong = ip2long($ip);

        list($range, $prefixLength) = explode('/', $cidr);

        $rangeAsLong = ip2long($range);

        // create mask
        $mask = -1 << (32 - (int)$prefixLength);
        $mask &= 0xFFFFFFFF;

        return ($ipAsLong & $mask) === ($rangeAsLong & $mask);
    }

}