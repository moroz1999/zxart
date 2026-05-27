<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Dto\ProdAuthorInfoDto;
use ZxArt\Prods\Dto\ProdFileDto;
use ZxArt\Prods\Dto\ProdGroupRefDto;
use ZxArt\Prods\Dto\ProdHardwareInfoDto;
use ZxArt\Prods\Dto\ProdLanguageInfoDto;
use ZxArt\Prods\Dto\ProdLinkInfoDto;
use ZxArt\Prods\Dto\ProdPartyInfoDto;
use ZxArt\Prods\Dto\ProdReleaseFormatDto;
use ZxArt\Prods\Dto\ProdReleaseInlayDto;
use ZxArt\Prods\Dto\ProdReleaseInstructionFileDto;
use ZxArt\Prods\Dto\ProdVotingDto;
use ZxArt\Releases\Rest\ReleaseDetailsRestDto;
use ZxArt\Releases\Dto\ReleaseFileStructureItemDto;

#[Map(target: ReleaseDetailsRestDto::class)]
readonly class ReleaseDetailsDto
{
    /**
     * @param string[]                       $hardwareRequired
     * @param ProdLanguageInfoDto[]          $languages
     * @param ProdHardwareInfoDto[]          $hardware
     * @param ProdAuthorInfoDto[]            $authors
     * @param ProdGroupRefDto[]              $publishers
     * @param ProdReleaseFormatDto[]         $formats
     * @param ProdLinkInfoDto[]              $externalLinks
     * @param ProdFileDto[]                  $screenshots
     * @param ProdReleaseInlayDto[]          $inlays
     * @param ProdReleaseInstructionFileDto[] $instructions
     * @param ReleaseFileStructureItemDto[]  $fileStructure
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
        public array $authors,
        public array $publishers,
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
        public array $screenshots,
        public ReleaseProdRefDto $prod,
        public array $inlays,
        public array $instructions,
        public ProdVotingDto $votes,
        public ReleaseTabsDto $tabs,
        public array $fileStructure,
        public bool $canUploadScreenshot,
        public string $screenshotUploadUrl,
        public bool $canReorderScreenshots,
    ) {
    }
}
