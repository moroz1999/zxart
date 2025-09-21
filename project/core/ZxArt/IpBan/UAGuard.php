<?php
declare(strict_types=1);

namespace ZxArt\IpBan;

/**
 * User-Agent guard: bans IP if UA is empty or matches blacklist.
 */
final class UAGuard
{
    private IpBanService $banService;

    /** Case-insensitive substrings that cause a ban. */
    private array $blocked;

    /** When true, empty UA is banned. */
    private bool $blockEmpty;

    public function __construct(
        IpBanService $banService,
        array $blocked = [],
        bool $blockEmpty = false
    )
    {
        $this->banService = $banService;
        $this->blocked = $blocked !== [] ? $blocked : $this->defaultList();
        $this->blockEmpty = $blockEmpty;
    }

    /** Returns true if ban applied. */
    public function handle(string $ip, ?string $userAgent, ?string $path): bool
    {
        $userAgent = trim((string)$userAgent);

        if ($userAgent === '' && $this->blockEmpty) {
            $this->banService->ban($ip, 'ua', $userAgent, $path);
            return true;
        }

        foreach ($this->blocked as $needle) {
            if ($needle !== '' && stripos($userAgent, $needle) !== false) {
                $this->banService->ban($ip, 'ua', $userAgent, $path);
                return true;
            }
        }

        return false;
    }

    private function defaultList(): array
    {
        return [
            'AI2Bot',
            'CCBot',
            'DVbot',
            'Leikibot',
            'CriteoBot',
            'CyotekWebCopy',
            'BLEXBot',
            'MJ12bot',
            'AhrefsBot',
            'SemrushBot',
            'serpstatbot',
            'MauiBot',
            'MegaIndex.ru/2.0',
            'MegaIndex.ru',
            'megaIndex.ru',
            'dotbot',
            'Amazonbot',
            'ClaudeBot',
            'grapeshot',
            'TerraCotta',
            'AwarioRssBot',
            'AwarioSmartBot',
            'proximic',
            'Bytespider',
            'keys-so-bot',
            'compatible; crawler',
            'UptimeRobot',
            'IbouBot',
            'AliyunSecBot',
            'Brightbot',
	    'Applebot',
	    'LinkupBot',
	    'PetalBot',
        ];
    }
}
