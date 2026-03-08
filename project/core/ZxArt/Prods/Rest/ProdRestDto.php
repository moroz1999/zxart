<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdRestDto
{
    /**
     * @param string[] $listImagesUrls
     * @param array<array{id: string, title: string}> $hardwareInfo
     * @param array<array{title: string, url: string, roles: string[]}> $authorsInfoShort
     * @param array<array{id: int, title: string, url: string}> $categoriesInfo
     * @param array{id: int, title: string, url: string}|null $partyInfo
     * @param array<array{id: string, title: string, url: string|null}> $languagesInfo
     * @param array<array{id: int, title: string, url: string}> $groupsInfo
     */
    public function __construct(
        public int $id,
        public string $url,
        public string $structureType,
        public int $dateCreated,
        public string $title,
        public ?int $year,
        public array $listImagesUrls,
        public float $votes,
        public int $votesAmount,
        public ?int $userVote,
        public bool $denyVoting,
        public array $hardwareInfo,
        public array $authorsInfoShort,
        public array $categoriesInfo,
        public ?array $partyInfo,
        public int $partyPlace,
        public ?string $legalStatus,
        public array $languagesInfo,
        public array $groupsInfo,
        public ?string $youtubeId,
        public ?string $releaseType = null,
    ) {
    }
}
