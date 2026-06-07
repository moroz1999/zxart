<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Groups\Rest\GroupCoreRestDto;

#[Map(target: GroupCoreRestDto::class)]
readonly class GroupCoreDto
{
    /**
     * @param string[]            $nature
     * @param GroupLinkDto[]      $links
     * @param GroupRefDto[]       $parentGroups
     * @param GroupRefDto[]       $aliases
     * @param GroupSubgroupDto[]  $subgroups
     * @param GroupMemberDto[]    $members
     * @param \ZxArt\Prods\Dto\PressArticlePreviewDto[] $mentions
     * @param GroupBreadcrumbDto[] $breadcrumbs
     */
    public function __construct(
        public int $id,
        public string $entityType,
        public string $title,
        public string $abbreviation,
        public string $url,
        public string $type,
        public string $slogan,
        public string $imageUrl,
        public ?string $years,
        public array $nature,
        public GroupLocationDto $location,
        public array $links,
        public array $parentGroups,
        public array $aliases,
        public array $subgroups,
        public array $members,
        public array $mentions,
        public GroupCountersDto $counters,
        public GroupTabsDto $tabs,
        public array $breadcrumbs,
    ) {
    }
}
