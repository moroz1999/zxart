<?php

declare(strict_types=1);

namespace ZxArt\Releases\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;
use ZxArt\Prods\Rest\ProdAuthorInfoRestDto;
use ZxArt\Prods\Rest\ProdFileRestDto;
use ZxArt\Prods\Rest\ProdGroupRefRestDto;
use ZxArt\Prods\Rest\ProdHardwareInfoRestDto;
use ZxArt\Prods\Rest\ProdLanguageInfoRestDto;
use ZxArt\Prods\Rest\ProdLinkInfoRestDto;
use ZxArt\Prods\Rest\ProdPartyInfoRestDto;
use ZxArt\Prods\Rest\ProdReleaseFormatRestDto;
use ZxArt\Prods\Rest\ProdReleaseInlayRestDto;
use ZxArt\Prods\Rest\ProdReleaseInstructionFileRestDto;
use ZxArt\Prods\Rest\ProdVotingRestDto;
use ZxArt\Releases\Rest\ReleaseFileStructureItemRestDto;

readonly class ReleaseDetailsRestDto
{
    /**
     * @param string[]                               $hardwareRequired
     * @param ProdLanguageInfoRestDto[]              $languages
     * @param ProdHardwareInfoRestDto[]              $hardware
     * @param ProdAuthorInfoRestDto[]                $authors
     * @param ProdGroupRefRestDto[]                  $publishers
     * @param ProdReleaseFormatRestDto[]             $formats
     * @param ProdLinkInfoRestDto[]                  $externalLinks
     * @param ProdFileRestDto[]                      $screenshots
     * @param ProdReleaseInlayRestDto[]              $inlays
     * @param ProdReleaseInstructionFileRestDto[]    $instructions
     * @param ReleaseFileStructureItemRestDto[]      $fileStructure
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
        public array $authors,
        #[Map(transform: MapCollection::class)]
        public array $publishers,
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
        #[Map(transform: MapCollection::class)]
        public array $screenshots,
        public ReleaseProdRefRestDto $prod,
        #[Map(transform: MapCollection::class)]
        public array $inlays,
        #[Map(transform: MapCollection::class)]
        public array $instructions,
        public ProdVotingRestDto $votes,
        public ReleaseTabsRestDto $tabs,
        #[Map(transform: MapCollection::class)]
        public array $fileStructure,
    ) {
    }
}
