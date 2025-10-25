<?php
declare(strict_types=1);


namespace ZxArt\Import\Press\DataUpdater;

use authorElement;
use groupElement;
use partyElement;
use pressArticleElement;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Hardware\Services\HardwareService;
use ZxArt\Import\Authors\AuthorSufficiencyChecker;
use ZxArt\Import\Labels\GroupLabel;
use ZxArt\Import\Labels\PersonLabel;
use ZxArt\Import\Parties\Party;
use ZxArt\Import\Parties\PartyResolver;
use ZxArt\Import\Pictures\PictureLabel;
use ZxArt\Import\Pictures\PictureResolver;
use ZxArt\Import\Press\PressUpdateException;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Tunes\TuneLabel;
use ZxArt\Import\Tunes\TuneResolver;
use ZxArt\Prods\Services\ProdsService;
use zxProdElement;

/**
 * @psalm-type ParsedParty = array{
 *     name: string,
 *     city?: string,
 *     country?: string,
 *     year: string,
 * }
 * @psalm-type ParsedGroup = array{
 *     parentTeamIds: array,
 *     id: string,
 *     name: string,
 *     city?: string,
 *     country?: string,
 *     type?: string,
 * }
 * @psalm-type ParsedTune = array{
 * }
 * @psalm-type ParsedPicture = array{
 * }
 * @psalm-type ParsedAuthor = array{
 *     id: string,
 *     nickName?: string,
 *     realName?: string,
 *     city?: string,
 *     country?: string,
 *     teamIds?: string[],
 *     teamRoles?: string[],
 * }
 * @psalm-type ParsedAuthorRole = array{
 *     id: string,
 *     softwareRoles?: string[],
 * }
 * @psalm-type ParsedProd = array{
 *     name: string,
 *     year: int|null,
 *     teamIds: string[],
 *     publisherIds: string[],
 *     authorship: ParsedAuthorRole[],
 * }
 * @psalm-type ParsedData = array{
 *     software: ParsedProd[],
 *     teams: ParsedGroup[],
 *     persons: ParsedAuthor[],
 *     pictures: ParsedPicture[],
 *     music: ParsedTune[],
 *     hardware: array,
 *     pressAuthorship: array,
 *     pressTeamIds: string[],
 *     articleAuthorIds: string[],
 *     mentionedTeamIds: string[],
 *     mentionedPersonIds: string[],
 *     publicationYear: string,
 *     parties: ParsedParty[],
 * }
 */
final class ArticleParsedDataUpdater
{
    private const string ORIGIN = 'zxp';
    /**
     * @var authorElement[]
     */
    private array $authorsMap;
    /**
     * @var groupElement[]
     */
    private array $groupsMap;
    private array $memberNamesMap;

    /**
     * @var array<string, string>
     */
    private array $toSoftwareRolesMap;
    /**
     * @var array<string, string>
     */
    private array $toGroupRolesMap;

    public function __construct(
        private readonly AuthorshipRepository     $authorshipRepository,
        private readonly AuthorsService           $authorsService,
        private readonly GroupsService            $groupsService,
        private readonly ProdsService             $prodsService,
        private readonly PartyResolver            $partyResolver,
        private readonly AuthorSufficiencyChecker $authorSufficiencyChecker,
        private readonly PictureResolver          $pictureResolver,
        private readonly TuneResolver             $tuneResolver,
        private readonly HardwareService          $hardwareService,
    )
    {
        $this->authorsService->setForceUpdateGroups(true);
        $this->prodsService->setMatchProdsWithoutYear(true);
        $this->prodsService->setUpdateExistingProds(true);

        $this->toSoftwareRolesMap = [
            'coder' => 'code',
            'cracker' => 'release',
            'graphician' => 'graphics',
            'hardware' => 'adaptation',
            'musician' => 'music',
            'organizer' => 'organizing',
            'support' => 'support',
            'tester' => 'testing',
            'gamedesigner' => 'gamedesign',
            'unknown' => 'unknown',
        ];
        $this->toGroupRolesMap = [
            'code' => 'coder',
            'release' => 'cracker',
            'graphics' => 'graphician',
            'adaptation' => 'coder',
            'music' => 'musician',
            'organizing' => 'organizer',
            'support' => 'support',
            'testing' => 'tester',
            'gamedesign' => 'gamedesigner',
            'unknown' => 'unknown',
        ];
    }

    /**
     * @param ParsedData $parsedData
     * @throws PressUpdateException
     */
    public function updatePressArticleData(pressArticleElement $pressArticleElement, array $parsedData): void
    {
        $pressElement = $pressArticleElement->getParent();
        if (!$pressElement) {
            throw new PressUpdateException("Prod could not be found for $pressArticleElement->id {$pressArticleElement->getTitle()}");
        }

        $groupsData = $parsedData['teams'] ?? [];
        $personsData = $parsedData['persons'] ?? [];

        $this->prepareMemberNamesMap($personsData);
        $this->prepareGroupsMap($groupsData);
        $this->prepareAuthorsMap($personsData, $groupsData);

        $pictures = $parsedData['pictures'] ?? null;
        if ($pictures !== null) {
            $this->updateArticlePictures($pictures, $pressArticleElement);
        }
        $tunes = $parsedData['music'] ?? null;
        if ($tunes !== null) {
            $this->updateArticleTunes($tunes, $pressArticleElement);
        }
        $hardware = $parsedData['hardware'] ?? null;
        if ($hardware !== null) {
            $this->storeHardware($hardware, $pressArticleElement);
        }
        $software = $parsedData['software'] ?? null;
        if ($software !== null) {
            $this->updateArticleSoftware($software, $pressArticleElement, $pressElement);
        }
        $parties = $parsedData['parties'] ?? null;
        if ($parties !== null) {
            $this->updateArticleParties($parties, $pressArticleElement);
        }

        $pressAuthorship = $parsedData['pressAuthorship'] ?? null;
        if ($pressAuthorship !== null) {
            $this->updatePressAuthorship($pressAuthorship, $pressElement);
        }

        $pressGroupIds = $parsedData['pressTeamIds'] ?? null;
        if ($pressGroupIds !== null) {
            $this->updatePressGroups($pressGroupIds, $pressElement);
        }

        $mentionedGroupIds = $parsedData['mentionedTeamIds'] ?? null;
        if ($mentionedGroupIds !== null) {
            $this->updateArticleGroups($mentionedGroupIds, $pressArticleElement);
        }

        $mentionedPersonIds = $parsedData['mentionedPersonIds'] ?? null;
        if ($mentionedPersonIds !== null) {
            $this->updateArticlePeople($mentionedPersonIds, $pressArticleElement);
        }

        $articleAuthorIds = $parsedData['articleAuthorIds'] ?? null;
        if ($articleAuthorIds !== null) {
            $pressAuthorship = [];
            // article authors should also be added to press entity
            foreach ($articleAuthorIds as $articleAuthorId) {
                $pressAuthorship[] = ['id' => $articleAuthorId, 'softwareRoles' => ['text']];
            }
            $this->updatePressAuthorship($pressAuthorship, $pressElement);
            $this->updateArticleAuthors($articleAuthorIds, $pressArticleElement);
        }

        $publicationYear = $parsedData['publicationYear'] ? (int)$parsedData['publicationYear'] : null;
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
            foreach ($datum['teamIds'] ?? [] as $groupId) {
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

    private function updatePressAuthorship(array $pressAuthorship, zxProdElement $pressElement): void
    {
        $authorRoles = [];
        foreach ($pressAuthorship as $item) {
            $authorRoles[$item['id']] = array_map(function ($role) {
                return $this->toSoftwareRolesMap[$role] ?? $role;
            }, $item['softwareRoles'] ?? ['unknown']);
        }

        foreach ($authorRoles as $authorId => $roles) {
            $authorElement = $this->authorsMap[$authorId] ?? null;
            if ($authorElement !== null) {
                $this->authorshipRepository->addAuthorship($pressElement->id, $authorElement->id, 'prod', $roles);
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

    private function updateArticleGroups(array $groupIds, pressArticleElement $pressArticleElement): void
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

    /**
     * @param ParsedProd[] $parsedProds
     */
    private function updateArticleSoftware(array $parsedProds, pressArticleElement $pressArticleElement, zxProdElement $pressElement): void
    {
        $resolvedProds = $this->prepareProds($parsedProds);
        $articleProds = $pressArticleElement->software;
        foreach ($resolvedProds as $prod) {
            if ($prod->id === $pressElement->id) {
                continue;
            }
            if ($prod !== null && !in_array($prod, $articleProds, true)) {
                $articleProds[] = $prod;
            }
        }
        $pressArticleElement->software = $articleProds;
    }

    private function updateArticlePictures(array $parsedPictures, pressArticleElement $pressArticleElement): void
    {
        $resolvedPictures = [];
        foreach ($parsedPictures as $parsedPicture) {
            $parsedPictureLabel = new PictureLabel(
                title: $parsedPicture['name'],
                year: $parsedPicture['year'] ?? null,
            );
            $resolvedPictures[] = $this->pictureResolver->resolve($parsedPictureLabel);
        }

        $articlePictures = $pressArticleElement->pictures;
        foreach ($resolvedPictures as $picture) {
            if ($picture !== null && !in_array($picture, $articlePictures, true)) {
                $articlePictures[] = $picture;
            }
        }
        $pressArticleElement->pictures = $articlePictures;
    }

    private function updateArticleTunes(array $parsedTunes, pressArticleElement $pressArticleElement): void
    {
        $resolvedTunes = [];
        foreach ($parsedTunes as $parsedTune) {
            $parsedTuneLabel = new TuneLabel(
                title: $parsedTune['name'],
                year: $parsedTune['year'] ?? null,
            );
            $resolvedTunes[] = $this->tuneResolver->resolve($parsedTuneLabel);
        }

        $articleTunes = $pressArticleElement->tunes;
        foreach ($resolvedTunes as $tune) {
            if ($tune !== null && !in_array($tune, $articleTunes, true)) {
                $articleTunes[] = $tune;
            }
        }
        $pressArticleElement->tunes = $articleTunes;
    }

    private function storeHardware($parsedHardware, pressArticleElement $pressArticleElement): void
    {
        foreach ($parsedHardware as $item) {
            $this->hardwareService->storeHardwareData($item, $pressArticleElement->id);
        }
    }

    /**
     * @param ParsedParty[] $parsedParties
     */
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

    /**
     * @param ParsedGroup[] $parsedGroups
     */
    private function prepareGroupsMap(array $parsedGroups): void
    {
        $sortedGroups = $this->sortGroupsByParent($parsedGroups);

        $this->groupsMap = [];
        $groupLabels = $this->makeGroupLabels($sortedGroups);
        foreach ($groupLabels as $label) {
            $element = $this->groupsService->importGroup($label, self::ORIGIN);
            if ($element !== null) {
                $this->groupsMap[$label->id] = $element;
            }
        }
    }

    /**
     * @param ParsedGroup[] $groups
     * @return ParsedGroup[]
     */
    private function sortGroupsByParent(array $groups): array
    {
        $parentIdsMap = [];
        foreach ($groups as $group) {
            $parentIds = $group['parentTeamIds'] ?? [];
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
     * @param ParsedGroup[] $parsedGroups
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

    /**
     * @param ParsedGroup $parsedGroup
     */
    private function makeGroupLabel(array $parsedGroup): GroupLabel
    {
        $memberNames = $this->memberNamesMap[$parsedGroup['id']] ?? null;
        return $this->transformGroupToLabel($parsedGroup, $memberNames);
    }

    /**
     * @param ParsedAuthor[] $parsedPersons
     * @param ParsedGroup[] $parsedGroups
     */
    private function prepareAuthorsMap(array $parsedPersons, array $parsedGroups): void
    {
        $groupsMap = [];
        foreach ($parsedGroups as $group) {
            $groupsMap[$group['id']] = $group;
        }

        $this->authorsMap = [];
        foreach ($parsedPersons as $parsedPerson) {
            $groupsInfo = array_map(static function (string $groupId) use ($groupsMap) {
                return $groupsMap[$groupId] ?? null;
            }, $parsedPerson['teamIds'] ?? []);
            $label = $this->transformAuthorToLabel(
                parsedAuthor: $parsedPerson,
                groupsInfo: $groupsInfo,
            );
            if (!$this->authorSufficiencyChecker::isDataSufficient(
                $label->realName ?? '',
                $label->name ?? '',
                $label->groupImportIds ?? [],
                $label->groupsIds ?? [],
            )) {
                continue;
            }
            $element = $this->authorsService->importAuthor($label, self::ORIGIN);
            if ($element !== null) {
                $this->authorsMap[$label->id] = $element;
            }
        }
    }

    /**
     * @param ParsedProd[] $parsedProds
     * @return zxProdElement[]
     */
    private function prepareProds(array $parsedProds): array
    {
        $elements = [];
        foreach ($parsedProds as $parsedProd) {
            $prod = $this->transformToProd($parsedProd);
            $element = $this->prodsService->importProd($prod, self::ORIGIN);

            if ($element !== null) {
                $elements[] = $element;
            }
        }
        return $elements;
    }

    /**
     * @param ParsedParty[] $parsedParties
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

    private function updatePressYear(int $year, zxProdElement $pressElement): void
    {
        if ($pressElement->year > 0) {
            return;
        }
        $pressElement->year = $year;
    }

    /**
     * @param ParsedAuthor $parsedAuthor
     * @param Array<ParsedGroup|null> $groupsInfo
     */
    private function transformAuthorToLabel(
        array $parsedAuthor,
        array $groupsInfo,
    ): PersonLabel
    {
        $groups = [];
        foreach ($groupsInfo as $groupDatum) {
            if ($groupDatum === null) {
                continue;
            }
            $groups[] = $this->transformGroupToLabel($groupDatum);
        }

        $groupRoles = array_map(function (string $role) {
            return $this->toGroupRolesMap[$role] ?? $role;
        }, $parsedAuthor['teamRoles'] ?? ['unknown']);

        return new PersonLabel(
            id: $parsedAuthor['id'],
            name: $parsedAuthor['nickName'] ?? null,
            realName: $parsedAuthor['realName'] ?? null,
            cityName: $parsedAuthor['city'] ?? null,
            countryName: $parsedAuthor['country'] ?? null,
            groupImportIds: $groups,
            groupsIds: $parsedAuthor['teamIds'] ?? null,
            groupRoles: $groupRoles,
        );
    }

    private function transformGroupToLabel(array $parsedGroup, ?array $memberNames = null): GroupLabel
    {
        return new GroupLabel(
            id: $parsedGroup['id'],
            name: $parsedGroup['name'],
            cityName: $parsedGroup['city'] ?? null,
            countryName: $parsedGroup['country'] ?? null,
            memberNames: $memberNames,
            parentGroupIds: $parsedGroup['parentTeamIds'] ?? null,
            type: $parsedGroup['type'] ?? null
        );
    }

    /**
     * @param ParsedParty $parsedParty
     */
    private function transformToParty(array $parsedParty): Party
    {
        return new Party(
            title: $parsedParty['name'],
            city: $parsedParty['city'] ?? null,
            country: $parsedParty['country'] ?? null,
            year: (int)$parsedParty['year'],
        );
    }

    /**
     * @param ParsedProd $parsedProd
     */
    private function transformToProd(array $parsedProd): ProdImportDTO
    {
        $groupIds = $parsedProd['teamIds'] ?? [];
        $publisherIds = $parsedProd['publisherIds'] ?? [];
        $authorRoles = [];
        foreach ($parsedProd['authorship'] ?? [] as $authorRoleDatum) {
            $authorRoles[$authorRoleDatum['id']] = array_map(
                function (string $role): string {
                    return $this->toSoftwareRolesMap[$role] ?? $role;
                },
                $authorRoleDatum['softwareRoles'] ?? ['unknown']);
        }

        return new ProdImportDTO(
            id: $parsedProd['name'],
            title: $parsedProd['name'],
            year: $parsedProd['year'] ?? null,
            authorRoles: $authorRoles,
            groups: $groupIds,
            publishers: $publisherIds,
        );
    }
}