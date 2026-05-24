<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class AuthorCoreRestDto
{
    /**
     * @param string[]                  $roles
     * @param string[]                  $badges
     * @param AuthorLocationItemRestDto[] $location
     * @param AuthorGroupRestDto[]       $groups
     * @param AuthorAliasRefRestDto[]    $aliases
     * @param AuthorLinkRestDto[]        $links
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $realName,
        public string $url,
        public ?string $parentUrl,
        public ?string $parentTitle,
        public ?string $siteUser,
        public ?string $joined,
        public array $roles,
        public array $badges,
        #[Map(transform: MapCollection::class)]
        public array $location,
        #[Map(transform: MapCollection::class)]
        public array $groups,
        #[Map(transform: MapCollection::class)]
        public array $aliases,
        #[Map(transform: MapCollection::class)]
        public array $links,
        public AuthorTechRestDto $tech,
        public AuthorCountersRestDto $counters,
        public AuthorRatingsRestDto $ratings,
        public AuthorTabsRestDto $tabs,
    ) {
    }
}
