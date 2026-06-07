<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use structureManager;
use ZxArt\Groups\Dto\GroupCollaboratorGroupDto;
use ZxArt\Groups\Dto\GroupCollaboratorPersonDto;
use ZxArt\Groups\Repositories\GroupCollaboratorsRepository;
use ZxArt\LinkTypes;
use ZxArt\Prods\Exception\ProdDetailsException;

readonly final class GroupCollaboratorsService
{
    public function __construct(
        private structureManager $structureManager,
        private GroupCollaboratorsRepository $collaboratorsRepository,
    ) {
    }

    /**
     * @return array{people: GroupCollaboratorPersonDto[], publishedGroups: GroupCollaboratorGroupDto[]}
     */
    public function getCollaborators(int $groupId): array
    {
        $group = $this->structureManager->getElementById($groupId);
        if (!($group instanceof groupElement) && !($group instanceof groupAliasElement)) {
            throw new ProdDetailsException('Group or alias not found', 404);
        }

        $groupIds = $this->collaboratorsRepository->getGroupAndAliasIds($groupId);
        $memberIds = $this->collaboratorsRepository->getMemberAuthorAndAliasIds($groupIds);
        $ownProdIds = $this->collaboratorsRepository->getLinkedChildIds($groupIds, LinkTypes::ZX_PROD_GROUPS);
        $publishedProdIds = $this->collaboratorsRepository->getLinkedChildIds($groupIds, LinkTypes::ZX_PROD_PUBLISHERS);
        $publishedReleaseIds = $this->collaboratorsRepository->getLinkedChildIds($groupIds, LinkTypes::ZX_RELEASE_PUBLISHERS);

        $peopleStats = $this->collaboratorsRepository->findPeopleStats($ownProdIds, $publishedReleaseIds, $memberIds);
        $groupStats = $this->collaboratorsRepository->findPublishedGroupStats($publishedProdIds, $groupIds);

        return [
            'people' => $this->buildPeople($peopleStats),
            'publishedGroups' => $this->buildGroups($groupStats),
        ];
    }

    /**
     * @param array<array{authorId: int, roles: string[], jointTotal: int}> $stats
     * @return GroupCollaboratorPersonDto[]
     */
    private function buildPeople(array $stats): array
    {
        $people = [];
        foreach ($stats as $row) {
            $element = $this->structureManager->getElementById($row['authorId']);
            if ($element instanceof authorAliasElement) {
                $element = $element->getAuthorElement();
            }
            if (!$element instanceof authorElement) {
                continue;
            }
            $people[] = new GroupCollaboratorPersonDto(
                id: $element->getId(),
                title: html_entity_decode($element->getTitle(), ENT_QUOTES),
                url: (string)$element->getUrl(),
                roles: $row['roles'],
                jointTotal: $row['jointTotal'],
            );
        }
        return $people;
    }

    /**
     * @param array<array{groupId: int, years: int[], jointProds: int}> $stats
     * @return GroupCollaboratorGroupDto[]
     */
    private function buildGroups(array $stats): array
    {
        $groups = [];
        foreach ($stats as $row) {
            $element = $this->structureManager->getElementById($row['groupId']);
            if (!($element instanceof groupElement) && !($element instanceof groupAliasElement)) {
                continue;
            }
            $groups[] = new GroupCollaboratorGroupDto(
                id: $element->getId(),
                title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
                url: (string)$element->getUrl(),
                years: $this->formatYears($row['years']),
                membersCount: $this->collaboratorsRepository->countMembers($element->getId()),
                jointProds: $row['jointProds'],
            );
        }
        return $groups;
    }

    /**
     * @param int[] $years
     */
    private function formatYears(array $years): ?string
    {
        if ($years === []) {
            return null;
        }
        sort($years);
        $first = (string)reset($years);
        $last = (string)end($years);
        if ($first === $last) {
            return $first;
        }
        return $first . '–' . $last;
    }
}
