<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use breadcrumbsManager;
use groupAliasElement;
use groupElement;
use structureManager;
use ZxArt\Groups\Dto\GroupBreadcrumbDto;
use ZxArt\Groups\Dto\GroupCoreDto;
use ZxArt\Groups\Dto\GroupCountersDto;
use ZxArt\Groups\Dto\GroupLinkDto;
use ZxArt\Groups\Dto\GroupLocationDto;
use ZxArt\Groups\Dto\GroupLocationItemDto;
use ZxArt\Groups\Dto\GroupMemberDto;
use ZxArt\Groups\Dto\GroupRefDto;
use ZxArt\Groups\Dto\GroupSubgroupDto;
use ZxArt\Groups\Dto\GroupTabsDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\PressArticlePreviewFactory;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Shared\EntityType;

readonly class GroupDetailsService
{
    public function __construct(
        private structureManager $structureManager,
        private breadcrumbsManager $breadcrumbsManager,
        private PressArticlePreviewFactory $pressArticlePreviewFactory,
    ) {
    }

    public function getDetails(int $groupId): GroupCoreDto
    {
        $group = $this->structureManager->getElementById($groupId);
        if (!($group instanceof groupElement) && !($group instanceof groupAliasElement)) {
            throw new ProdDetailsException('Group or alias not found', 404);
        }

        $profileGroup = $this->resolveProfileGroup($group);
        $subgroups = $this->buildSubgroups($group);
        $members = $this->buildMembers($group);
        $counters = $this->buildCounters($group, $members, $subgroups);

        return new GroupCoreDto(
            id: (int)$group->getId(),
            entityType: ($group instanceof groupElement ? EntityType::Group : EntityType::GroupAlias)->value,
            title: $this->decode((string)$group->getTitle()),
            abbreviation: $profileGroup instanceof groupElement ? $this->decode((string)$profileGroup->abbreviation) : '',
            url: (string)$group->getUrl(),
            type: $profileGroup instanceof groupElement ? (string)$profileGroup->type : '',
            slogan: $profileGroup instanceof groupElement ? $this->decode((string)$profileGroup->slogan) : '',
            imageUrl: $profileGroup instanceof groupElement ? $profileGroup->getImageUrl() : '',
            years: $this->buildYears($group),
            nature: $this->buildNature($group),
            location: $this->buildLocation($profileGroup),
            links: $this->buildLinks($group),
            parentGroups: $this->buildParentGroups($group),
            aliases: $this->buildAliases($group),
            subgroups: $subgroups,
            members: $members,
            mentions: $this->buildMentions($group),
            counters: $counters,
            tabs: $this->buildTabs($counters),
            breadcrumbs: $this->buildBreadcrumbs($group),
        );
    }

    private function resolveProfileGroup(groupElement|groupAliasElement $group): ?groupElement
    {
        if ($group instanceof groupElement) {
            return $group;
        }
        $profileGroup = $group->getGroupElement();
        return $profileGroup instanceof groupElement ? $profileGroup : null;
    }

    private function buildLocation(?groupElement $group): GroupLocationDto
    {
        if (!$group instanceof groupElement) {
            return new GroupLocationDto(city: null, country: null);
        }
        $city = null;
        if ($cityElement = $group->getCityElement()) {
            $city = new GroupLocationItemDto(
                title: $this->decode((string)$cityElement->title),
                url: (string)$cityElement->getUrl('group'),
            );
        }
        $country = null;
        if ($countryElement = $group->getCountryElement()) {
            $country = new GroupLocationItemDto(
                title: $this->decode((string)$countryElement->title),
                url: (string)$countryElement->getUrl('group'),
            );
        }
        return new GroupLocationDto(city: $city, country: $country);
    }

    /**
     * @return GroupLinkDto[]
     */
    private function buildLinks(groupElement|groupAliasElement $group): array
    {
        $links = [];
        if ($group instanceof groupElement && ($website = (string)$group->website) !== '') {
            $links[] = new GroupLinkDto(url: $website, label: $this->extractDomain($website));
        }
        foreach ($group->getLinksInfo() as $linkInfo) {
            $links[] = new GroupLinkDto(
                url: (string)$linkInfo['url'],
                label: (string)$linkInfo['name'],
            );
        }
        return $links;
    }

    /**
     * @return string[]
     */
    private function buildNature(groupElement|groupAliasElement $group): array
    {
        $nature = [];
        if ($group->getGroupProds() !== []) {
            $nature[] = 'developer';
        }
        $releases = $group->getReleases();
        if ($group->getPublisherProds() !== [] || $releases !== []) {
            $nature[] = 'publisher';
        }
        foreach ($releases as $release) {
            if ((string)$release->releaseType === ReleaseTypes::crack->value) {
                $nature[] = 'cracker';
                break;
            }
        }
        return $nature;
    }

    /**
     * @return GroupSubgroupDto[]
     */
    private function buildSubgroups(groupElement|groupAliasElement $group): array
    {
        if (!$group instanceof groupElement) {
            return [];
        }
        $subgroups = [];
        foreach ($group->getSubGroups() as $subgroup) {
            if (!$subgroup instanceof groupElement) {
                continue;
            }
            $subgroups[] = new GroupSubgroupDto(
                id: (int)$subgroup->getId(),
                title: $this->decode((string)$subgroup->getTitle()),
                abbreviation: $this->decode((string)$subgroup->abbreviation),
                url: (string)$subgroup->getUrl(),
                membersCount: count($subgroup->getAuthorsInfo(EntityType::Group->value)),
                prodsCount: count($subgroup->getGroupProds()),
                years: $this->buildYears($subgroup),
            );
        }
        return $subgroups;
    }

    /**
     * @return GroupMemberDto[]
     */
    private function buildMembers(groupElement|groupAliasElement $group): array
    {
        $subgroupMembership = $this->buildSubgroupMembership($group);
        $members = [];
        foreach ($group->getAuthorsInfo(EntityType::Group->value) as $record) {
            $authorElement = $record['authorElement'];
            $authorId = (int)$authorElement->getId();
            $rawRoles = is_array($record['roles']) ? $record['roles'] : [];
            $roles = array_values(array_filter(
                $rawRoles,
                static fn(mixed $role): bool => is_string($role) && $role !== 'unknown',
            ));
            $members[] = new GroupMemberDto(
                id: $authorId,
                title: $this->decode((string)$authorElement->getTitle()),
                url: (string)$authorElement->getUrl(),
                realName: $this->decode((string)$authorElement->realName),
                roles: array_values($roles),
                years: $this->joinYears(
                    $this->yearFromDateString((string)$record['startDate']),
                    $this->yearFromDateString((string)$record['endDate']),
                ),
                subgroups: $subgroupMembership[$authorId] ?? [],
            );
        }
        return $members;
    }

    /**
     * @return array<int, string[]>
     */
    private function buildSubgroupMembership(groupElement|groupAliasElement $group): array
    {
        if (!$group instanceof groupElement) {
            return [];
        }
        $membership = [];
        foreach ($group->getSubGroups() as $subgroup) {
            if (!$subgroup instanceof groupElement) {
                continue;
            }
            $subgroupTitle = $this->decode((string)$subgroup->getTitle());
            foreach ($subgroup->getAuthorsInfo(EntityType::Group->value) as $record) {
                $authorId = (int)$record['authorElement']->getId();
                $membership[$authorId][] = $subgroupTitle;
            }
        }
        return $membership;
    }

    /**
     * @return GroupRefDto[]
     */
    private function buildParentGroups(groupElement|groupAliasElement $group): array
    {
        if (!$group instanceof groupElement) {
            return [];
        }
        $parents = [];
        foreach ($group->parentGroups as $parentGroup) {
            if (!$parentGroup instanceof groupElement) {
                continue;
            }
            $parents[] = new GroupRefDto(
                id: (int)$parentGroup->getId(),
                title: $this->decode((string)$parentGroup->getTitle()),
                url: (string)$parentGroup->getUrl(),
                years: null,
            );
        }
        return $parents;
    }

    /**
     * @return GroupRefDto[]
     */
    private function buildAliases(groupElement|groupAliasElement $group): array
    {
        if (!$group instanceof groupElement) {
            return [];
        }
        $aliases = [];
        foreach ($group->getAliasElements() as $aliasElement) {
            if (!$aliasElement instanceof groupAliasElement) {
                continue;
            }
            $aliases[] = new GroupRefDto(
                id: (int)$aliasElement->getId(),
                title: $this->decode((string)$aliasElement->title),
                url: (string)$aliasElement->getUrl(),
                years: $this->buildYears($aliasElement),
            );
        }
        return $aliases;
    }

    /**
     * @return \ZxArt\Prods\Dto\PressArticlePreviewDto[]
     */
    private function buildMentions(groupElement|groupAliasElement $group): array
    {
        return $this->pressArticlePreviewFactory->createList($group->getPressMentions());
    }

    /**
     * @param GroupMemberDto[]   $members
     * @param GroupSubgroupDto[] $subgroups
     */
    private function buildCounters(
        groupElement|groupAliasElement $group,
        array $members,
        array $subgroups,
    ): GroupCountersDto {
        return new GroupCountersDto(
            members: count($members),
            subgroups: count($subgroups),
            prods: count($group->getGroupProds()),
            published: count($group->getPublisherProds()),
            releases: $this->countReleases($group),
            mentions: count($group->getPressMentions()),
            comments: (int)$group->getCommentsAmount(),
        );
    }

    /**
     * Counts the group's published releases, excluding types that are not shown in the
     * "releases & cracks" listing (see {@see ReleaseTypes::getGroupExcludedValues()}).
     */
    private function countReleases(groupElement|groupAliasElement $group): int
    {
        $excluded = ReleaseTypes::getGroupExcludedValues();
        $count = 0;
        foreach ($group->getReleases() as $release) {
            if (!in_array((string)$release->releaseType, $excluded, true)) {
                $count++;
            }
        }
        return $count;
    }

    private function buildTabs(GroupCountersDto $counters): GroupTabsDto
    {
        $hasProds = $counters->prods > 0;
        $hasReleases = $counters->releases > 0;
        return new GroupTabsDto(
            hasProds: $hasProds,
            hasPublished: $counters->published > 0,
            hasReleases: $hasReleases,
            hasMembers: $counters->members > 0,
            hasSubgroups: $counters->subgroups > 0,
            hasConnections: $hasProds || $hasReleases || $counters->published > 0,
            hasMentions: $counters->mentions > 0,
        );
    }

    private function buildYears(groupElement|groupAliasElement $group): ?string
    {
        return $this->joinYears(
            $this->yearFromDateString((string)$group->startDate),
            $this->yearFromDateString((string)$group->endDate),
        );
    }

    private function joinYears(?string $startYear, ?string $endYear): ?string
    {
        if ($startYear === null && $endYear === null) {
            return null;
        }
        if ($startYear === $endYear) {
            return $startYear;
        }
        if ($startYear !== null && $endYear !== null) {
            return $startYear . '–' . $endYear;
        }
        return $startYear !== null ? $startYear . '–' : '–' . $endYear;
    }

    private function yearFromDateString(string $date): ?string
    {
        if (preg_match('/(\d{4})/', $date, $matches) === 1) {
            return $matches[1];
        }
        return null;
    }

    private function extractDomain(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        return is_string($host) && $host !== '' ? $host : $url;
    }

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES);
    }

    /**
     * @return GroupBreadcrumbDto[]
     */
    private function buildBreadcrumbs(groupElement|groupAliasElement $group): array
    {
        $groupUrl = (string)$group->getUrl();
        $path = trim((string)parse_url($groupUrl, PHP_URL_PATH), '/');
        if ($path === '') {
            return [];
        }
        $segments = array_values(array_filter(explode('/', $path)));
        /** @var array<array{title: string, URL: string}> $ancestors */
        $ancestors = array_slice($this->breadcrumbsManager->getBreadcrumbsForPath($segments), 1, -1);

        $breadcrumbs = [];
        foreach ($ancestors as $item) {
            $breadcrumbs[] = new GroupBreadcrumbDto(
                title: $this->decode((string)$item['title']),
                url: (string)$item['URL'],
            );
        }
        return $breadcrumbs;
    }
}
