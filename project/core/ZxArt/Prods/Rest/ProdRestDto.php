<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdRestDto
{
    /**
     * @param string[] $listImagesUrls
     * @param array<array{id: string}> $hardwareInfo
     * @param array<array{id: int, structureType: string, title: string, roles: string[]}> $authorsInfoShort
     * @param array<array{id: int, structureType: string, title: string}> $categoriesInfo
     * @param array{id: int, structureType: string, title: string}|null $partyInfo
     * @param array<array{id: string, title: string}> $languagesInfo
     * @param array<array{id: int, structureType: string, title: string}> $groupsInfo
     * @param array<array{id: int, structureType: string, title: string}> $publishersInfo
     */
    public function __construct(
        public int $id,
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
        public array $publishersInfo,
        public ?string $youtubeId,
    ) {
    }
}
