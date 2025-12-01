<?php
declare(strict_types=1);


namespace ZxArt\Import\Services;

final class VtrdosHardwareProvider
{
    /** @var array<string,string> */
    protected array $hwIndex = [
        '(DMA UltraSound Card)' => 'dmausc',
        '(km)' => 'kempstonmouse',
        '(kemp8bit)' => 'kempston8b',
        '(cvx)' => 'covoxfb',
        '(dma)' => 'dmausc',
        '(gs)' => 'gs',
        '(sd)' => 'soundrive',
        '(for 48k)' => 'zx48',
        '(48k only)' => 'zx48',
        '(48/128k)' => 'zx128',
        '(128k only)' => 'zx128',
        '(1024k)' => 'pentagon1024',
        '(256k)' => 'scorpion',
        '(ts)' => 'ts',
        '48k' => 'zx48',
        'Pentagon 512k' => 'pentagon512',
        'Pentagon 1024k' => 'pentagon1024',
        'Pentagon 1024SL' => 'pentagon1024',
        'Scorpion ZS 256' => 'scorpion',
        'Byte' => 'byte',
        'smuc' => 'smuc',
        'SMUC' => 'smuc',
        'Sprinter' => 'sprinter',
        'Covox' => 'covoxfb',
        'General Sound' => 'gs',
        'Cache' => 'Cache',
        'SounDrive' => 'soundrive',
        'Turbo Sound' => 'ts',
        'TurboSound FM' => 'tsfm',
        'ZXM-MoonSound' => 'zxm',
        'AY' => 'ay',
        'DMA UltraSound Card' => 'dmausc',
        'DMA USC' => 'dmausc',
        'Beeper' => 'beeper',
        'AY Mouse' => 'aymouse',
        'CP/M' => 'cpm',
        '(for Profi)' => 'profi',
        '(Base Conf)' => 'baseconf',
        '(TS Conf)' => 'tsconf',
        'ATM Turbo 2' => 'atm2',
        'neoGS-SD' => 'sdneogs',
        'NEMO-HDD' => 'nemoide',
        'Nemo HDD' => 'nemoide',
        'ZSD' => 'zcontroller',
        'iS-DOS' => 'isdos',
        'TASiS' => 'tasis',
        'CD-ROM' => 'cd',
        'CD' => 'cd',
        'GMX' => 'gmx',
    ];

    public function match(string $raw): array
    {
        $matches = [];
        foreach ($this->hwIndex as $key => $value) {
            if (stripos($raw, $key) !== false) {
                $matches[] = $value;
            }
        }
        return $matches;
    }

    public function removeMatches(string $raw): array
    {
        foreach ($this->hwIndex as $marker => $hardwareCode) {
            if ((stripos($raw, $marker) !== false) && str_contains($marker, '(')) {
                $raw = str_ireplace($marker, '', $raw);
            }
        }
        return $raw;
    }

}