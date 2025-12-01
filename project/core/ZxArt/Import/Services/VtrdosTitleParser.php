<?php
declare(strict_types=1);

namespace ZxArt\Import\Services;

final class VtrdosTitleParser
{
    /**
     * @param array<string,string> $hardwareIndex
     */
    public function __construct(
        private readonly array $hardwareIndex,
    )
    {
    }

    public function parse(string $rawTitle): VtrdosTitleParseResult
    {
        $workTitle = $rawTitle;
        $hardwareRequired = [];
        $languages = null;
        $releaseType = null;
        $version = null;

        // Hardware markers
        foreach ($this->hardwareIndex as $marker => $hardwareCode) {
            if (stripos($workTitle, $marker) !== false) {
                if (str_contains($marker, '(')) {
                    $workTitle = str_ireplace($marker, '', $workTitle);
                }
                $hardwareRequired[] = $hardwareCode;
            }
        }

        // Release type markers
        if (stripos($workTitle, '(dsk)') !== false) {
            $workTitle = str_ireplace('(dsk)', '', $workTitle);
            $releaseType = 'adaptation';
        }

        if (stripos($workTitle, '(mod)') !== false) {
            $workTitle = str_ireplace('(mod)', '', $workTitle);
            $releaseType = 'mod';
        }

        // Language markers
        if (stripos($workTitle, '(rus)') !== false) {
            $workTitle = str_ireplace('(rus)', '', $workTitle);
            $languages = ['ru'];
        }

        if (stripos($workTitle, '(ita)') !== false) {
            $workTitle = str_ireplace('(ita)', '', $workTitle);
            $languages = ['it'];
        }

        if (stripos($workTitle, '(pol)') !== false) {
            $workTitle = str_ireplace('(pol)', '', $workTitle);
            $languages = ['pl'];
        }

        if (stripos($workTitle, '(eng)') !== false) {
            $workTitle = str_ireplace('(eng)', '', $workTitle);
            $languages = ['en'];
        }

        if (stripos($workTitle, '(ukr)') !== false) {
            $workTitle = str_ireplace('(ukr)', '', $workTitle);
            $languages = ['ua'];
        }

        // Version markers, e.g. "Game v1.0"
        if (preg_match('#(v[0-9]\.[0-9])#i', $workTitle, $matches, PREG_OFFSET_CAPTURE)) {
            $offset = (int)$matches[0][1];
            $versionString = substr($workTitle, $offset);
            $version = ltrim(trim($versionString), 'vV ');
            $workTitle = trim(substr($workTitle, 0, $offset));
        }

        // Clean base title (бывший processTitle)
        $cleanTitle = $this->cleanupBaseTitle($workTitle);

        return new VtrdosTitleParseResult(
            title: $cleanTitle,
            languages: $languages,
            hardwareRequired: array_values(array_unique($hardwareRequired)),
            releaseType: $releaseType,
            version: $version,
        );
    }

    private function cleanupBaseTitle(string $text): string
    {
        $text = preg_replace('#([(].*[)])*#', '', $text);
        $text = trim(
            preg_replace('!\s+!', ' ', $text),
            " \t\n\r\0\x0B" . chr(0xC2) . chr(0xA0)
        );

        if (strtolower(substr($text, -4)) === 'demo') {
            $text = trim(mb_substr($text, 0, -4));
        }

        return $text;
    }
}
