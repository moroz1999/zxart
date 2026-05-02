<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdReleaseRestDto;

#[Map(target: ProdReleaseRestDto::class)]
readonly class ProdReleaseDto
{
    /**
     * @param string[]               $hardwareRequired
     * @param ProdLanguageInfoDto[]  $languages
     * @param ProdHardwareInfoDto[]  $hardware
     * @param ProdGroupRefDto[]      $releaseBy
     * @param ProdReleaseFormatDto[] $formats
     * @param ProdLinkInfoDto[]      $externalLinks
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
        public ?ProdPartyInfoDto $party,
        public array $languages,
        public array $hardware,
        public array $releaseBy,
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
        public array $externalLinks,
    ) {
    }
}
