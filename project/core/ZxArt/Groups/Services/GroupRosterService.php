<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use structureManager;
use ZxArt\Groups\Dto\GroupMemberDto;
use ZxArt\Groups\Dto\GroupRosterDto;
use ZxArt\Groups\Dto\GroupSubgroupDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Shared\EntityType;

readonly class GroupRosterService
{
    public function __construct(private structureManager $structureManager)
    {
    }

    public function getRoster(int $groupId): GroupRosterDto
    {
        $group = $this->getGroup($groupId);

        return new GroupRosterDto(
            subgroups: $this->buildSubgroups($group),
            members: $this->buildMembers($group),
        );
    }

    public function countMembers(groupElement|groupAliasElement $group): int
    {
        $records = (array)$group->getAuthorsInfo(EntityType::Group->value);

        return count($records);
    }

    public function countSubgroups(groupElement|groupAliasElement $group): int
    {
        if (!$group instanceof groupElement) {
            return 0;
        }

        $subgroups = (array)$group->getSubGroups();

        return count($subgroups);
    }

    private function getGroup(int $groupId): groupElement|groupAliasElement
    {
        $group = $this->structureManager->getElementById($groupId);
        if (!($group instanceof groupElement) && !($group instanceof groupAliasElement)) {
            throw new ProdDetailsException('Group or alias not found', 404);
        }

        return $group;
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
        foreach ((array)$group->getSubGroups() as $subgroup) {
            if (!$subgroup instanceof groupElement) {
                continue;
            }
            $subgroups[] = new GroupSubgroupDto(
                id: $subgroup->getId(),
                title: $this->decode((string)$subgroup->getTitle()),
                abbreviation: $this->decode($subgroup->abbreviation),
                url: (string)$subgroup->getUrl(),
                membersCount: $this->countMembers($subgroup),
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
        /** @var list<array{authorElement: authorElement|authorAliasElement, roles: mixed, startDate: string, endDate: string}> $records */
        $records = (array)$group->getAuthorsInfo(EntityType::Group->value);
        foreach ($records as $record) {
            $authorElement = $record['authorElement'];
            $authorId = $authorElement->getId();
            $rawRoles = is_array($record['roles']) ? $record['roles'] : [];
            $roles = array_values(array_filter(
                $rawRoles,
                static fn(mixed $role): bool => is_string($role) && $role !== 'unknown',
            ));
            $members[] = new GroupMemberDto(
                id: $authorId,
                title: $this->decode((string)$authorElement->getTitle()),
                url: (string)$authorElement->getUrl(),
                realName: $authorElement instanceof authorElement ? $this->decode($authorElement->realName) : '',
                roles: $roles,
                years: $this->joinYears(
                    $this->yearFromDateString($record['startDate']),
                    $this->yearFromDateString($record['endDate']),
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
        foreach ((array)$group->getSubGroups() as $subgroup) {
            if (!$subgroup instanceof groupElement) {
                continue;
            }
            $subgroupTitle = $this->decode((string)$subgroup->getTitle());
            /** @var list<array{authorElement: authorElement|authorAliasElement}> $records */
            $records = (array)$subgroup->getAuthorsInfo(EntityType::Group->value);
            foreach ($records as $record) {
                $authorId = $record['authorElement']->getId();
                $membership[$authorId][] = $subgroupTitle;
            }
        }
        return $membership;
    }

    private function buildYears(groupElement $group): ?string
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

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES);
    }
}
