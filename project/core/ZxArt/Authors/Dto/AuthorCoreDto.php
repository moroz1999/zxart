<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorCoreRestDto;

#[Map(target: AuthorCoreRestDto::class)]
readonly class AuthorCoreDto
{
    /**
     * @param string[]                $roles
     * @param string[]                $badges
     * @param AuthorGroupDto[]        $groups
     * @param AuthorAliasRefDto[]     $aliases
     * @param AuthorLinkDto[]         $links
     * @param AuthorBreadcrumbDto[]   $breadcrumbs
     */
    public function __construct(
        public int $id,
        public string $entityType,
        public string $title,
        public string $realName,
        public string $url,
        public ?string $parentUrl,
        public ?string $parentTitle,
        public ?AuthorAliasRefDto $primaryAuthor,
        public ?string $siteUser,
        public ?string $joined,
        public AuthorLocationDto $location,
        public array $roles,
        public array $badges,
        public array $groups,
        public array $aliases,
        public array $links,
        public AuthorTechDto $tech,
        public AuthorCountersDto $counters,
        public AuthorRatingsDto $ratings,
        public AuthorTabsDto $tabs,
        public array $breadcrumbs,
    ) {
    }
}
