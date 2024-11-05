<?php
declare(strict_types=1);


namespace ZxArt\Import\Press\DataUpdater;

use LanguagesManager;
use pressArticleElement;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Import\Labels\Label;
use ZxArt\Import\Labels\LabelResolver;
use ZxArt\Import\Labels\LabelType;
use ZxArt\Import\Parties\Party;
use ZxArt\Import\Parties\PartyResolver;
use ZxArt\Import\Press\PressUpdateException;
use ZxArt\Import\Prods\Prod;
use ZxArt\Import\Prods\ProdResolver;
use ZxArt\Prods\Services\ProdsService;
use zxProdElement;
use partyElement;

final class DataUpdater
{
    private array $languageIdsMap;
    private const ORIGIN = 'zxp';

    public function __construct(
        private readonly AuthorsService       $authorsManager,
        private readonly GroupsService        $groupsService,
        private readonly ProdsService         $prodsService,
        private readonly AuthorshipRepository $authorshipRepository,
        private readonly LanguagesManager     $languagesManager,
        private readonly LabelResolver        $labelResolver,
        private readonly ProdResolver         $prodsResolver,
        private readonly PartyResolver        $partyResolver,
    )
    {
        $this->languageIdsMap = $this->languagesManager->getLanguagesIdsMap();
    }
//todo: filter out "Viktor" case
//todo: author roles in group
//todo: author roles in prod
//todo: alphanumeric hack to group/party names
    public function updatePressArticleData(pressArticleElement $pressArticleElement, array $mergedContent): void
    {
        $pressElement = $pressArticleElement->getParent();
        if (!$pressElement) {
            throw new PressUpdateException("Prod could not be found for {$pressArticleElement->id} {$pressArticleElement->getTitle()}");
        }

        $pictures = $mergedContent['pictures'] ?? null;
        $tunes = $mergedContent['tunes'] ?? null;
        $hardware = $mergedContent['hardware'] ?? null;

        $software = $mergedContent['software'] ?? null;
        if ($software !== null) {
            $this->updateArticleSoftware($software, $pressArticleElement);
        }
        $parties = $mergedContent['parties'] ?? null;
        if ($parties !== null) {
            $this->updateArticleParties($parties, $pressArticleElement);
        }
        $tags = $mergedContent['tags'] ?? null;
        if ($tags !== null) {
            $this->updateArticleTags($tags, $pressArticleElement);
        }


        $pressAuthors = $mergedContent['pressAuthors'] ?? null;
        if ($pressAuthors !== null) {
            $this->updatePressAuthors($pressAuthors, $pressElement);
        }

        $pressGroups = $mergedContent['pressGroups'] ?? null;
        if ($pressGroups !== null) {
            $this->updatePressGroups($pressGroups, $pressElement);
        }

        $groups = $mergedContent['groups'] ?? null;
        if ($groups !== null) {
            $this->updatePressArticleGroups($groups, $pressArticleElement);
        }

        $articleAuthors = $mergedContent['articleAuthors'] ?? null;
        if ($articleAuthors !== null) {
            // article authors should also be added to press entity
            $this->updatePressAuthors($articleAuthors, $pressElement);
            $this->updateArticleAuthors($articleAuthors, $pressArticleElement);
        }

        $publicationYear = $mergedContent['publicationYear'] ?? null;
        if ($publicationYear !== null) {
            $this->updatePressYear($publicationYear, $pressElement);
        }
        $people = $mergedContent['people'] ?? null;
        if ($people !== null) {
            $this->updateArticlePeople($people, $pressArticleElement);
        }


        $shortContent = $mergedContent['shortContent'] ?? null;
        if ($shortContent !== null) {
            $this->updateArticleTranslatedProperty('introduction', $shortContent, $pressArticleElement);
        }

        $h1 = $mergedContent['h1'] ?? null;
        if ($h1 !== null) {
            $this->updateArticleTranslatedProperty('h1', $h1, $pressArticleElement);
        }

        $metaDescription = $mergedContent['metaDescription'] ?? null;
        if ($metaDescription !== null) {
            $this->updateArticleTranslatedProperty('metaDescription', $metaDescription, $pressArticleElement);
        }

        $pageTitle = $mergedContent['pageTitle'] ?? null;
        if ($pageTitle !== null) {
            $this->updateArticleTranslatedProperty('metaTitle', $pageTitle, $pressArticleElement);
        }
        $pressElement->persistElementData();
        $pressArticleElement->persistElementData();
    }

    private function updateArticleTranslatedProperty(string $propertyName, array $values, pressArticleElement $pressArticleElement): void
    {
        foreach ($this->languageIdsMap as $code => $languageId) {
            $value = $values[$code] ?? null;
            if ($value === null) {
                continue;
            }
            $pressArticleElement->setValue($propertyName, $value, $languageId);
        }
    }

    private function updatePressAuthors(array $pressAuthors, zxProdElement $pressElement): void
    {
        $resolvedAuthorship = $this->prepareAuthorship($pressAuthors);

        foreach ($resolvedAuthorship as $item) {
            $this->authorshipRepository->checkAuthorship($pressElement->id, $item->author->id, 'prod', $item->roles);
        }
    }

    private function updatePressGroups(array $pressGroups, zxProdElement $pressElement): void
    {
        $resolvedGroups = $this->prepareGroups($pressGroups);
        $groups = $pressElement->groups;
        foreach ($resolvedGroups as $resolvedGroup) {
            $groups[] = $resolvedGroup->group;
        }
        $pressElement->groups = $groups;
    }

    private function updatePressArticleGroups(array $pressGroups, pressArticleElement $pressArticleElement): void
    {
        $resolvedGroups = $this->prepareGroups($pressGroups);
        $groups = $pressArticleElement->groups;
        foreach ($resolvedGroups as $resolvedGroup) {
            $groups[] = $resolvedGroup->group;
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

    private function updateArticleTags(array $tags, pressArticleElement $pressArticleElement): void
    {
        $pressArticleElement->addTags($tags);
    }

    private function updateArticlePeople(array $persons, pressArticleElement $pressArticleElement): void
    {
        $resolvedAuthorships = $this->prepareAuthorship($persons);
        $people = $pressArticleElement->people;
        foreach ($resolvedAuthorships as $resolvedAuthorship) {
            $people[] = $resolvedAuthorship->author;
        }
        $pressArticleElement->people = $people;
    }

    private function updateArticleAuthors(array $pressAuthors, pressArticleElement $pressArticleElement): void
    {
        $resolvedAuthorship = $this->prepareAuthorship($pressAuthors);
        $authors = $pressArticleElement->authors;
        foreach ($resolvedAuthorship as $item) {
            $authors[] = $item->author;
        }
        $pressArticleElement->authors = $authors;
    }

    /**
     * @return ResolvedAuthorship[]
     */
    private function prepareAuthorship(array $parsedAuthors): array
    {
        $elements = [];
        foreach ($parsedAuthors as $parsedAuthor) {
            $label = $this->transformAuthorToLabel($parsedAuthor);
            $element = $this->labelResolver->resolve($label);
            if ($element === null) {
                $authorInfo = $label->toArray();
                $element = $this->authorsManager->importAuthor($authorInfo, self::ORIGIN);
            }
            if ($element !== null) {
                //todo: split roles in soft and roles in group
                $elements[] = new ResolvedAuthorship(
                    label: $label,
                    author: $element,
                    roles: $parsedAuthor['roles'] ?? [],
                );
            }
        }
        return $elements;
    }

    /**
     * @return ResolvedGroup[]
     */
    private function prepareGroups(array $parsedGroups): array
    {
        $elements = [];
        foreach ($parsedGroups as $parsedGroup) {
            $label = $this->transformGroupToLabel($parsedGroup);
            $element = $this->labelResolver->resolve($label);
            if ($element === null) {
                $groupInfo = $label->toArray();
                $element = $this->groupsService->importGroup($groupInfo, self::ORIGIN);
            }
            if ($element !== null) {
                $elements[] = new ResolvedGroup(
                    label: $label,
                    group: $element,
                );
            }
        }
        return $elements;
    }

    /**
     * @return zxProdElement[]
     */
    private function prepareProds(array $parsedProds): array
    {
        $elements = [];
        foreach ($parsedProds as $parsedProd) {
            $prod = $this->transformToProd($parsedProd);
            $element = $this->prodsResolver->resolve($prod, true);
            if ($element === null) {
                $prodInfo = $prod->toArray();
                $element = $this->prodsService->importProd($prodInfo, self::ORIGIN);
            }
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
            $element = $this->partyResolver->resolve($party, true);
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

    private function transformAuthorToLabel($parsedAuthor): Label
    {
        $groups = [];
        $groupsData = $parsedAuthor['groups'] ?? [];
        foreach ($groupsData as $groupDatum) {
            $groups[] = $this->transformGroupToLabel($groupDatum);
        }

        return new Label(
            name: $parsedAuthor['nickName'] ?? null,
            realName: $parsedAuthor['realName'] ?? null,
            city: $parsedAuthor['city'] ?? null,
            country: $parsedAuthor['country'] ?? null,
            groups: $groups !== [] ? $groups : null,
            type: LabelType::person,
        );
    }

    private function transformGroupToLabel($parsedGroup): Label
    {
        return new Label(
            name: $parsedGroup['name'],
            city: $parsedGroup['city'] ?? null,
            country: $parsedGroup['country'] ?? null,
            type: LabelType::group,
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
        $groupsData = $parsedProd['groups'] ?? [];
        $groups = [];
        foreach ($groupsData as $groupDatum) {
            $groups[] = $this->transformGroupToLabel($groupDatum);
        }
        $publishersData = $parsedProd['publishers'] ?? [];
        $publishers = [];
        foreach ($publishersData as $publisherDatum) {
            $publishers[] = $this->transformGroupToLabel($publisherDatum);
        }

        $authorsData = $parsedProd['authors'] ?? [];
        $authors = [];
        foreach ($authorsData as $authorDatum) {
            $authors[] = $this->transformAuthorToLabel($authorDatum);
        }

        return new Prod(
            title: $parsedProd['name'],
            year: $parsedProd['year'] ?? null,
            authors: $authors,
            groups: $groups,
            publishers: $publishers,
        );
    }
}