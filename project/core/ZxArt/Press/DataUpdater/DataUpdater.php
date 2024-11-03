<?php
declare(strict_types=1);


namespace ZxArt\Press\DataUpdater;

use AuthorsManager;
use LanguagesManager;
use pressArticleElement;
use ZxArt\Labels\Label;
use ZxArt\Labels\LabelResolver;
use ZxArt\Labels\LabelType;
use ZxArt\Press\PressUpdateException;
use zxProdElement;

final class DataUpdater
{
    private array $languageIdsMap;
    private const ORIGIN = 'zxp';

    public function __construct(
        private readonly AuthorsManager   $authorsManager,
        private readonly LanguagesManager $languagesManager,
        private readonly LabelResolver    $labelResolver,
    )
    {
        $this->languageIdsMap = $this->languagesManager->getLanguagesIdsMap();
    }

    public function updatePressArticleData(pressArticleElement $pressArticleElement, array $mergedContent): void
    {
        $pressElement = $pressArticleElement->getParent();
        if (!$pressElement) {
            throw new PressUpdateException("Prod could not be found for {$pressArticleElement->id} {$pressArticleElement->getTitle()}");
        }

        $pressGroups = $mergedContent['pressGroups'] ?? null;
        $people = $mergedContent['people'] ?? null;
        $groups = $mergedContent['groups'] ?? null;
        $parties = $mergedContent['parties'] ?? null;
        $pictures = $mergedContent['pictures'] ?? null;
        $tunes = $mergedContent['tunes'] ?? null;
        $software = $mergedContent['software'] ?? null;
        $hardware = $mergedContent['hardware'] ?? null;
        $tags = $mergedContent['tags'] ?? null;

        $pressAuthors = $mergedContent['pressAuthors'] ?? null;
        if ($pressAuthors !== null) {
            $this->updatePressAuthors($pressAuthors, $pressElement);
        }

        $articleAuthors = $mergedContent['articleAuthors'] ?? null;
        if ($articleAuthors !== null) {
            $this->updatePressAuthors($articleAuthors, $pressElement);
            $this->updatePressArticleAuthors($articleAuthors, $pressArticleElement);
        }

        $publicationYear = $mergedContent['publicationYear'] ?? null;
        if ($publicationYear !== null) {
            $this->updatePressYear($publicationYear, $pressElement);
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
        $resolvedAuthorship = $this->prepareAuthorElements($pressAuthors);
        foreach ($resolvedAuthorship as $item) {
            $this->authorsManager->checkAuthorship($pressElement->id, $item->author->id, 'prod', $item->roles);
        }
    }

    private function updatePressArticleAuthors(array $pressAuthors, pressArticleElement $pressArticleElement): void
    {
        $resolvedAuthorship = $this->prepareAuthorElements($pressAuthors);
        $authors = $pressArticleElement->authors;
        foreach ($resolvedAuthorship as $item) {
            $authors[] = $item->author;
        }
        $pressArticleElement->authors = $authors;
    }

    /**
     * @return ResolvedAuthorship[]
     */
    private function prepareAuthorElements(array $parsedAuthors): array
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
                $elements[] = new ResolvedAuthorship(
                    label: $label,
                    author: $element,
                    roles: $parsedAuthor['roles'] ?? [],
                );
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

    private function transformAuthorToLabel($author): Label
    {
        $groups = [];
        $groupsData = $author['groups'] ?? [];
        foreach ($groupsData as $groupDatum) {
            $groups[] = $this->transformGroupToLabel($groupDatum);
        }

        return new Label(
            name: $author['nickName'] ?? null,
            realName: $author['realName'] ?? null,
            city: $author['city'] ?? null,
            country: $author['country'] ?? null,
            groups: $groups !== [] ? $groups : null,
            type: LabelType::person,
        );
    }

    private function transformGroupToLabel($group): Label
    {
        return new Label(
            name: $group['name'] ?? null,
            city: $author['city'] ?? null,
            country: $author['country'] ?? null,
            type: LabelType::group,
        );
    }
}