<?php
declare(strict_types=1);


namespace ZxArt\Import\Press\DataUpdater;

use groupElement;
use pressArticleElement;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Import\Authors\AuthorSufficiencyChecker;
use ZxArt\Import\Labels\PersonLabel;
use ZxArt\Import\Labels\GroupLabel;
use ZxArt\Import\Parties\Party;
use ZxArt\Import\Parties\PartyResolver;
use ZxArt\Import\Press\PressUpdateException;
use ZxArt\Import\Prods\Prod;
use ZxArt\Prods\Services\ProdsService;
use zxProdElement;
use partyElement;

final class ArticleParsedDataUpdater
{
    private const ORIGIN = 'zxp';
    /**
     * @var Author[]
     */
    private array $authorsMap;
    /**
     * @var groupElement[]
     */
    private array $groupsMap;
    private array $memberNamesMap;

    public function __construct(
        private readonly AuthorsService           $authorsService,
        private readonly GroupsService            $groupsService,
        private readonly ProdsService             $prodsService,
        private readonly PartyResolver            $partyResolver,
        private readonly AuthorSufficiencyChecker $authorSufficiencyChecker,
    )
    {
        $this->authorsService->setForceUpdateGroups(true);
        $this->prodsService->setMatchProdsWithoutYear(true);
        $this->prodsService->setUpdateExistingProds(true);
    }

    public function updatePressArticleData(pressArticleElement $pressArticleElement, array $parsedData): void
    {
        $pressElement = $pressArticleElement->getParent();
        if (!$pressElement) {
            throw new PressUpdateException("Prod could not be found for {$pressArticleElement->id} {$pressArticleElement->getTitle()}");
        }

        $groupsData = $parsedData['groups'] ?? [];
        $personsData = $parsedData['persons'] ?? [];

        $this->prepareMemberNamesMap($personsData);
        $this->prepareGroupsMap($groupsData);
        $this->prepareAuthorsMap($personsData, $groupsData);

        $pictures = $parsedData['pictures'] ?? null;
        $tunes = $parsedData['tunes'] ?? null;
        $hardware = $parsedData['hardware'] ?? null;

        $software = $parsedData['software'] ?? null;
        if ($software !== null) {
            $this->updateArticleSoftware($software, $pressArticleElement);
        }
        $parties = $parsedData['parties'] ?? null;
        if ($parties !== null) {
            $this->updateArticleParties($parties, $pressArticleElement);
        }

        $pressAuthorIds = $parsedData['pressAuthorIds'] ?? null;
        if ($pressAuthorIds !== null) {
            $this->updatePressAuthors($pressAuthorIds, $pressElement);
        }

        $pressGroupIds = $parsedData['pressGroupIds'] ?? null;
        if ($pressGroupIds !== null) {
            $this->updatePressGroups($pressGroupIds, $pressElement);
        }

        $mentionedGroupIds = $parsedData['mentionedGroupIds'] ?? null;
        if ($mentionedGroupIds !== null) {
            $this->updatePressArticleGroups($mentionedGroupIds, $pressArticleElement);
        }

        $mentionedPersonIds = $parsedData['mentionedPersonIds'] ?? null;
        if ($mentionedPersonIds !== null) {
            $this->updateArticlePeople($mentionedPersonIds, $pressArticleElement);
        }

        $articleAuthorIds = $parsedData['articleAuthorIds'] ?? null;
        if ($articleAuthorIds !== null) {
            // article authors should also be added to press entity
            $this->updatePressAuthors($articleAuthorIds, $pressElement, ['text']);
            $this->updateArticleAuthors($articleAuthorIds, $pressArticleElement);
        }

        $publicationYear = $parsedData['publicationYear'] ?? null;
        if ($publicationYear !== null) {
            $this->updatePressYear($publicationYear, $pressElement);
        }

        $pressElement->persistElementData();
        $pressArticleElement->persistElementData();
    }

    private function prepareMemberNamesMap(array $personsData): void
    {
        $this->memberNamesMap = [];
        foreach ($personsData as $datum) {
            foreach ($datum['groupIds'] ?? [] as $groupId) {
                $this->memberNamesMap[$groupId] ??= [];
                if (isset($datum['realName'])) {
                    $this->memberNamesMap[$groupId][] = $datum['realName'];
                }
                if (isset($datum['nickName'])) {
                    $this->memberNamesMap[$groupId][] = $datum['nickName'];
                }
            }
        }
    }

    private function updatePressAuthors(array $pressAuthorIds, zxProdElement $pressElement): void
    {
        foreach ($pressAuthorIds as $authorId) {
            $authors = $this->authorsMap[$authorId] ?? null;
            if ($authors !== null) {
                //todo: prod roles
//                $this->authorsRepository->checkAuthors($pressElement->id, $authors->author->id, 'prod', $authors->prodRoles);
            }
        }
    }

    private function updatePressGroups(array $groupIds, zxProdElement $pressElement): void
    {
        $groups = $pressElement->groups;
        foreach ($groupIds as $groupId) {
            $group = $this->groupsMap[$groupId] ?? null;
            if ($group !== null && !in_array($group, $groups, true)) {
                $groups[] = $group;
            }
        }
        $pressElement->groups = $groups;
    }

    private function updatePressArticleGroups(array $groupIds, pressArticleElement $pressArticleElement): void
    {
        $groups = $pressArticleElement->groups;
        foreach ($groupIds as $groupId) {
            $group = $this->groupsMap[$groupId] ?? null;
            if ($group !== null && !in_array($group, $groups, true)) {
                $groups[] = $group;
            }
        }
        $pressArticleElement->groups = $groups;
    }

    private function updateArticleSoftware(array $parsedProds, pressArticleElement $pressArticleElement): void
    {
        $resolvedProds = $this->prepareProds($parsedProds);
        $articleProds = $pressArticleElement->software;
        foreach ($resolvedProds as $prod) {
            $articleProds[] = $prod;
        }
        $pressArticleElement->software = $articleProds;
    }

    private function updateArticleParties(array $parsedParties, pressArticleElement $pressArticleElement): void
    {
        $resolvedParties = $this->prepareParties($parsedParties);
        $articleParties = $pressArticleElement->parties;
        foreach ($resolvedParties as $party) {
            $articleParties[] = $party;
        }
        $pressArticleElement->parties = $articleParties;
    }

    private function updateArticlePeople(array $personIds, pressArticleElement $pressArticleElement): void
    {
        $people = $pressArticleElement->people;
        foreach ($personIds as $personId) {
            $person = $this->authorsMap[$personId] ?? null;
            if ($person !== null && !in_array($person, $people, true)) {
                $people[] = $person;
            }
        }
        $pressArticleElement->people = $people;
    }

    private function updateArticleAuthors(array $personIds, pressArticleElement $pressArticleElement): void
    {
        $authors = $pressArticleElement->authors;
        foreach ($personIds as $personId) {
            $person = $this->authorsMap[$personId] ?? null;
            if ($person !== null && !in_array($person, $authors, true)) {
                $authors[] = $person;
            }
        }
        $pressArticleElement->authors = $authors;
    }

    private function prepareGroupsMap(array $parsedGroups): void
    {
        $sortedGroups = $this->sortGroupsByParent($parsedGroups);

        $this->groupsMap = [];
        $groupLabels = $this->makeGroupLabels($sortedGroups);
        foreach ($groupLabels as $label) {
            $groupInfo = $label->toArray();
            $element = $this->groupsService->importGroup($groupInfo, self::ORIGIN);
            if ($element !== null) {
                $this->groupsMap[$label->id] = $element;
            }
        }
    }

    private function sortGroupsByParent(array $groups): array
    {
        $parentIdsMap = [];
        foreach ($groups as $group) {
            $parentIds = $group['parentGroupIds'] ?? [];
            foreach ($parentIds as $parentId) {
                $parentIdsMap[$parentId] = true;
            }
        }
        $parentIdsMap = array_unique($parentIdsMap);

        $result = [];
        foreach ($groups as $group) {
            if (isset($parentIdsMap[$group['id']])) {
                $result[] = $group;
            }
        }
        foreach ($groups as $group) {
            if (!isset($parentIdsMap[$group['id']])) {
                $result[] = $group;
            }
        }
        return $result;
    }

    /**
     * @return GroupLabel[]
     */
    private function makeGroupLabels(array $parsedGroups): array
    {
        $groupLabels = [];
        foreach ($parsedGroups as $parsedGroup) {
            $groupLabels[] = $this->makeGroupLabel($parsedGroup);
        }
        return $groupLabels;
    }

    private function makeGroupLabel(array $parsedGroup): GroupLabel
    {
        $memberNames = $this->memberNamesMap[$parsedGroup['id']] ?? null;
        return $this->transformGroupToLabel($parsedGroup, $memberNames);
    }

    /**
     * @return Author[]
     */
    private function prepareAuthorsMap(array $parsedPersons, array $parsedGroups): array
    {
        $groupsMap = [];
        foreach ($parsedGroups as $group) {
            $groupsMap[$group['id']] = $group;
        }

        $this->authorsMap = [];
        foreach ($parsedPersons as $parsedPerson) {
            $groupsInfo = array_map(static function (string $groupId) use ($groupsMap) {
                return $groupsMap[$groupId] ?? null;
            }, $parsedPerson['groupIds'] ?? []);
            $label = $this->transformAuthorToLabel(
                parsedAuthor: $parsedPerson,
                groupsInfo: $groupsInfo,
            );
            if (!$this->authorSufficiencyChecker::isDataSufficient(
                $label->realName ?? '',
                $label->name ?? '',
                $label->groups ?? [],
                $label->groupsIds ?? [],
            )) {
                continue;
            }
            $authorInfo = $label->toArray();
            $element = $this->authorsService->importAuthor($authorInfo, self::ORIGIN);
            if ($element !== null) {
                $this->authorsMap[$label->id] = $element;
            }
        }
        return $this->authorsMap;
    }

    /**
     * @return zxProdElement[]
     */
    private function prepareProds(array $parsedProds): array
    {
        $elements = [];
        foreach ($parsedProds as $parsedProd) {
            $prod = $this->transformToProd($parsedProd);
            $prodInfo = $prod->toArray();
            $element = $this->prodsService->importProd($prodInfo, self::ORIGIN);

            if ($element !== null) {
                $elements[] = $element;
            }
        }
        return $elements;
    }

    /**
     * @return partyElement[]
     */
    private function prepareParties(array $parsedParties): array
    {
        $elements = [];
        foreach ($parsedParties as $parsedParty) {
            $party = $this->transformToParty($parsedParty);
            $element = $this->partyResolver->resolve($party);
            if ($element !== null) {
                $elements[] = $element;
            }
        }
        return $elements;
    }

    private function updatePressYear($year, zxProdElement $pressElement): void
    {
        if ($pressElement->year > 0) {
            return;
        }
        $pressElement->year = $year;
    }

    private function transformAuthorToLabel(
        array $parsedAuthor,
        array $groupsInfo,
    ): PersonLabel
    {
        $groups = [];
        foreach ($groupsInfo as $groupDatum) {
            $groups[] = $this->transformGroupToLabel($groupDatum);
        }
        return new PersonLabel(
            id: $parsedAuthor['id'],
            name: $parsedAuthor['nickName'] ?? null,
            realName: $parsedAuthor['realName'] ?? null,
            city: $parsedAuthor['city'] ?? null,
            country: $parsedAuthor['country'] ?? null,
            groups: $groups,
            groupsIds: $parsedAuthor['groupIds'] ?? null,
            groupRoles: $parsedAuthor['groupRoles'] ?? null,
        );
    }

    private function transformGroupToLabel(array $parsedGroup, ?array $memberNames = null): GroupLabel
    {
        $groups = [];
        $groupsData = $parsedGroup['parentGroups'] ?? [];
        foreach ($groupsData as $groupDatum) {
            $groups[] = $this->transformGroupToLabel($groupDatum);
        }

        return new GroupLabel(
            id: $parsedGroup['id'],
            name: $parsedGroup['name'],
            city: $parsedGroup['city'] ?? null,
            country: $parsedGroup['country'] ?? null,
            groups: $groups,
            memberNames: $memberNames,
            parentGroupIds: $parsedGroup['parentGroupIds'] ?? null,
            type: $parsedGroup['type'] ?? null
        );
    }

    private function transformToParty($parsedParty): Party
    {
        return new Party(
            title: $parsedParty['name'],
            city: $parsedParty['city'] ?? null,
            country: $parsedParty['country'] ?? null,
            year: (int)$parsedParty['year'],
        );
    }

    private function transformToProd($parsedProd): Prod
    {
        $groupIds = $parsedProd['groupIds'] ?? [];
        $publisherIds = $parsedProd['publisherIds'] ?? [];
        $authorRoles = [];
        foreach ($parsedProd['authorship'] ?? [] as $authorRoleDatum) {
            $authorRoles[$authorRoleDatum['id']] = $authorRoleDatum['roles'];
        }

        return new Prod(
            title: $parsedProd['name'],
            year: $parsedProd['year'] ?? null,
            authorRoles: $authorRoles,
            groupIds: $groupIds,
            publisherIds: $publisherIds,
        );
    }
}