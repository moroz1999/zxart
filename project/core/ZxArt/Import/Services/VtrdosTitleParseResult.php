<?php
declare(strict_types=1);

namespace ZxArt\Import\Services;

final class VtrdosTitleParseResult
{
    /**
     * @param string $title Cleaned base title without markers
     * @param string[]|null $languages Parsed language codes or null if not found
     * @param string[] $hardwareRequired Parsed hardware codes
     * @param string|null $releaseType Parsed release type (e.g. "mod", "adaptation")
     * @param string|null $version Parsed version string without leading "v" (e.g. "1.0")
     */
    public function __construct(
        public readonly string  $title,
        public readonly ?array  $languages,
        public readonly array   $hardwareRequired,
        public readonly ?string $releaseType,
        public readonly ?string $version,
    )
    {
    }
}
