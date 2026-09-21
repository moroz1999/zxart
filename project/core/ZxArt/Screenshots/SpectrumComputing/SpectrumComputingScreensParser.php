<?php

declare(strict_types=1);

namespace ZxArt\Screenshots\SpectrumComputing;

/**
 * Finds the loading, opening and running screens on a Spectrum Computing entry page.
 * The page lists every additional file as a striped row: a link to the file
 * followed by its caption.
 */
final readonly class SpectrumComputingScreensParser
{
    public const string SITE_URL = 'https://spectrumcomputing.co.uk';
    private const string ROW_MARKER = 'zxdb_stripes';

    /**
     * The same screen is often published both as a native `scr` and as a
     * picture; the native file is kept then.
     *
     * @return list<SpectrumComputingScreen> ordered by kind, then by page order
     */
    public function parse(string $html): array
    {
        /** @var array<string, array<string, SpectrumComputingScreen>> $screens kind => file name => screen */
        $screens = [];
        foreach (array_slice(explode(self::ROW_MARKER, $html), 1) as $row) {
            $kind = $this->findKind($row);
            $url = $this->findLink($row);
            if ($kind === null || $url === null) {
                continue;
            }
            $path = (string)parse_url($url, PHP_URL_PATH);
            $format = SpectrumComputingScreenFormat::tryFrom(strtolower(pathinfo($path, PATHINFO_EXTENSION)));
            if ($format === null) {
                continue;
            }
            $name = pathinfo($path, PATHINFO_FILENAME);
            if (!isset($screens[$kind->value][$name]) || $format->isNative()) {
                $screens[$kind->value][$name] = new SpectrumComputingScreen($url, $format);
            }
        }
        $ordered = [];
        foreach (SpectrumComputingScreenKind::cases() as $kind) {
            foreach ($screens[$kind->value] ?? [] as $screen) {
                $ordered[] = $screen;
            }
        }
        return $ordered;
    }

    private function findKind(string $row): ?SpectrumComputingScreenKind
    {
        if (preg_match('#<small>\s*([^<]+?)\s*</small>#i', $row, $matches) !== 1) {
            return null;
        }
        return SpectrumComputingScreenKind::tryFrom($matches[1]);
    }

    private function findLink(string $row): ?string
    {
        if (preg_match('#<a\s[^>]*href="([^"]+)"#i', $row, $matches) !== 1) {
            return null;
        }
        $url = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return str_starts_with($url, 'http') ? $url : self::SITE_URL . '/' . ltrim($url, '/');
    }
}
