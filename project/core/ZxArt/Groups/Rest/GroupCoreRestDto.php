<?php

declare(strict_types=1);

namespace ZxArt\Groups\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class GroupCoreRestDto
{
    /**
     * @param string[]                  $nature
     * @param GroupLinkRestDto[]        $links
     * @param GroupRefRestDto[]         $parentGroups
     * @param GroupRefRestDto[]         $aliases
     * @param GroupSubgroupRestDto[]    $subgroups
     * @param GroupMemberRestDto[]      $members
     * @param \ZxArt\Prods\Rest\PressArticlePreviewRestDto[] $mentions
     * @param GroupBreadcrumbRestDto[]  $breadcrumbs
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
        public GroupLocationRestDto $location,
        #[Map(transform: MapCollection::class)]
        public array $links,
        #[Map(transform: MapCollection::class)]
        public array $parentGroups,
        #[Map(transform: MapCollection::class)]
        public array $aliases,
        #[Map(transform: MapCollection::class)]
        public array $subgroups,
        #[Map(transform: MapCollection::class)]
        public array $members,
        #[Map(transform: MapCollection::class)]
        public array $mentions,
        public GroupCountersRestDto $counters,
        public GroupTabsRestDto $tabs,
        #[Map(transform: MapCollection::class)]
        public array $breadcrumbs,
    ) {
    }
}
