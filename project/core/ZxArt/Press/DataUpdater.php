<?php
declare(strict_types=1);


namespace ZxArt\Press;

use pressArticleElement;
use LanguagesManager;

final class DataUpdater
{
    private array $languageIdsMap;

    public function __construct(
        private LanguagesManager $languagesManager,
    )
    {
        $this->languageIdsMap = $this->languagesManager->getLanguagesIdsMap();
    }

    public function updatePressArticleData(pressArticleElement $pressArticleElement, array $mergedContent): void
    {
        $pressAuthors = $mergedContent['pressAuthors'] ?? null;
        $pressGroups = $mergedContent['pressGroups'] ?? null;
        $articleAuthors = $mergedContent['articleAuthors'] ?? null;
        $articleGroups = $mergedContent['articleGroups'] ?? null;
        $people = $mergedContent['people'] ?? null;
        $groups = $mergedContent['groups'] ?? null;
        $parties = $mergedContent['parties'] ?? null;
        $pictures = $mergedContent['pictures'] ?? null;
        $tunes = $mergedContent['tunes'] ?? null;
        $software = $mergedContent['software'] ?? null;
        $hardware = $mergedContent['hardware'] ?? null;
        $tags = $mergedContent['tags'] ?? null;
        $publicationYear = $mergedContent['publicationYear'] ?? null;

        if ($publicationYear !== null) {
            $this->updatePressYear($publicationYear, $pressArticleElement);
        }

        $shortContent = $mergedContent['shortContent'] ?? null;
        if ($shortContent !== null) {
            $this->updateArticleProperty('introduction', $shortContent, $pressArticleElement);
        }

        $h1 = $mergedContent['h1'] ?? null;
        if ($h1 !== null) {
            $this->updateArticleProperty('h1', $h1, $pressArticleElement);
        }

        $metaDescription = $mergedContent['metaDescription'] ?? null;
        if ($metaDescription !== null) {
            $this->updateArticleProperty('metaDescription', $metaDescription, $pressArticleElement);
        }

        $pageTitle = $mergedContent['pageTitle'] ?? null;
        if ($pageTitle !== null) {
            $this->updateArticleProperty('metaTitle', $pageTitle, $pressArticleElement);
        }
    }

    private function updateArticleProperty(string $propertyName, array $values, pressArticleElement $pressArticleElement): void
    {
        foreach ($this->languageIdsMap as $code => $languageId) {
            $value = $values[$code] ?? null;
            if ($value === null) {
                continue;
            }
            $pressArticleElement->setValue($propertyName, $value, $languageId);
        }
        $pressArticleElement->persistElementData();
    }

    private function updatePressYear($year, pressArticleElement $pressArticleElement): void
    {
        $pressElement = $pressArticleElement->getParent();
        if (!$pressElement) {
            throw new PressUpdateException("Prod could not be found for {$pressArticleElement->id} {$pressArticleElement->getTitle()}");
        }
        if ($pressElement->year > 0) {
            return;
        }
        $pressElement->year = $year;
        $pressElement->persistElementData();
    }
}