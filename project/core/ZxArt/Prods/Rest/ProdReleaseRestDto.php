<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class ProdReleaseRestDto
{
    /**
     * @param string[]                   $hardwareRequired
     * @param ProdLanguageInfoRestDto[]  $languages
     * @param ProdHardwareInfoRestDto[]  $hardware
     * @param ProdGroupRefRestDto[]      $releaseBy
     * @param ProdReleaseFormatRestDto[] $formats
     * @param ProdLinkInfoRestDto[]      $externalLinks
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public int $year,
        public string $version,
        public string $releaseType,
        public ?string $releaseTypeLabel,
        public array $hardwareRequired,
        public string $description,
        public bool $isRealtime,
        public ?ProdPartyInfoRestDto $party,
        #[Map(transform: MapCollection::class)]
        public array $languages,
        #[Map(transform: MapCollection::class)]
        public array $hardware,
        #[Map(transform: MapCollection::class)]
        public array $releaseBy,
        #[Map(transform: MapCollection::class)]
        public array $formats,
        public bool $isDownloadable,
        public bool $isPlayable,
        public ?string $downloadUrl,
        public ?string $playUrl,
        public ?string $fileName,
        public ?string $emulatorType,
        public string $prodLegalStatus,
        public string $prodExternalLink,
        public int $downloadsCount,
        public int $playsCount,
        #[Map(transform: MapCollection::class)]
        public array $externalLinks,
    ) {
    }
}
