<?php
declare(strict_types=1);

namespace ZxArt\Import\Services;

final class VtrdosTitleParser
{
    private const LANGUAGE_MAP = [
        'rus' => 'ru',
        'ru' => 'ru',

        'slk' => 'sk',
        'sk' => 'sk',

        'eng' => 'en',
        'en' => 'en',

        'esp' => 'es',
        'es' => 'es',

        'ita' => 'it',
        'it' => 'it',

        'pol' => 'pl',
        'pl' => 'pl',

        'ukr' => 'ua',
        'ua' => 'ua',
    ];

    /**
     * Markers that should be stripped from the title but do not affect language or release type.
     *
     * @var string[]
     */
    private const array TECHNICAL_MARKERS = [
        'txt',
    ];

    /**
     * @var array<string, array{type:string,priority:int}>
     */
    private const RELEASE_TYPE_MAP = [
        'mod' => ['type' => 'mod', 'priority' => 100],
        'bugfix' => ['type' => 'adaptation', 'priority' => 50],
        'pentfix' => ['type' => 'adaptation', 'priority' => 50],
        'dsk' => ['type' => 'adaptation', 'priority' => 10],
    ];

    public function __construct(
        private readonly VtrdosHardwareProvider $vtrdosHardwareProvider,
    )
    {
    }

    public function parse(string $rawTitle): VtrdosTitleParseResult
    {
        $workTitle = trim($rawTitle);
        $languages = null;
        $releaseType = null;
        $version = null;
        $hardwareRequired = $this->vtrdosHardwareProvider->match($workTitle);
        $workTitle = $this->vtrdosHardwareProvider->removeMatches($workTitle);

        $languageCodes = [];
        $currentReleaseType = null;
        $currentReleaseTypePriority = -1;

        $workTitle = preg_replace_callback(
            '/\(([^)]*)\)/u',
            function (array $matches) use (&$languageCodes, &$currentReleaseType, &$currentReleaseTypePriority): string {
                $inside = $matches[1];

                $tokens = preg_split('~[/,]~', $inside);
                if ($tokens === false) {
                    return $matches[0];
                }

                $foundSemanticMarker = false;

                foreach ($tokens as $token) {
                    $normalized = strtolower(trim($token));
                    if ($normalized === '') {
                        continue;
                    }

                    $code = $this->mapLanguageMarkerToCode($normalized);
                    if ($code !== null) {
                        $foundSemanticMarker = true;
                        if (!in_array($code, $languageCodes, true)) {
                            $languageCodes[] = $code;
                        }
                        continue;
                    }

                    $typeInfo = $this->mapReleaseTypeMarkerToTypeInfo($normalized);
                    if ($typeInfo !== null) {
                        $foundSemanticMarker = true;

                        if ($typeInfo['priority'] > $currentReleaseTypePriority) {
                            $currentReleaseTypePriority = $typeInfo['priority'];
                            $currentReleaseType = $typeInfo['type'];
                        }
                        continue;
                    }

                    if (in_array($normalized, self::TECHNICAL_MARKERS, true)) {
                        $foundSemanticMarker = true;
                    }
                }

                if ($foundSemanticMarker) {
                    return '';
                }

                return $matches[0];
            },
            $workTitle
        );

        if (!empty($languageCodes)) {
            $languages = $languageCodes;
            $releaseType = 'translation';
        }

        if ($currentReleaseType !== null) {
            $releaseType = $currentReleaseType;
        }

        if (preg_match('#(v[0-9]\.[0-9])#i', $workTitle, $matches, PREG_OFFSET_CAPTURE)) {
            $offset = (int)$matches[0][1];
            $versionString = substr($workTitle, $offset);
            $version = ltrim(trim($versionString), 'vV ');
            $workTitle = trim(substr($workTitle, 0, $offset));
        }

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

    private function mapLanguageMarkerToCode(string $marker): ?string
    {
        return self::LANGUAGE_MAP[$marker] ?? null;
    }

    /**
     * @return array{type:string,priority:int}|null
     */
    private function mapReleaseTypeMarkerToTypeInfo(string $marker): ?array
    {
        return self::RELEASE_TYPE_MAP[$marker] ?? null;
    }
}
