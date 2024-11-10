<?php
declare(strict_types=1);


namespace ZxArt\Import\Press\DataUpdater;

use LanguagesManager;
use pressArticleElement;
use ZxArt\Import\Press\PressUpdateException;

final class ArticleSeoDataUpdater
{
    private array $languageIdsMap;

    public function __construct(
        private readonly LanguagesManager $languagesManager,
    )
    {
        $this->languageIdsMap = $this->languagesManager->getLanguagesIdsMap();
    }

    public function updatePressArticleData(pressArticleElement $pressArticleElement, array $updatedContentt): void
    {
        $shortContent = $updatedContentt['shortContent'] ?? null;
        if ($shortContent !== null) {
            $this->updateArticleTranslatedProperty('introduction', $shortContent, $pressArticleElement);
        }

        $h1 = $updatedContentt['h1'] ?? null;
        if ($h1 !== null) {
            $this->updateArticleTranslatedProperty('h1', $h1, $pressArticleElement);
        }

        $metaDescription = $updatedContentt['metaDescription'] ?? null;
        if ($metaDescription !== null) {
            $this->updateArticleTranslatedProperty('metaDescription', $metaDescription, $pressArticleElement);
        }

        $pageTitle = $updatedContentt['pageTitle'] ?? null;
        if ($pageTitle !== null) {
            $this->updateArticleTranslatedProperty('metaTitle', $pageTitle, $pressArticleElement);
        }

        $tags = $parserData['tags'] ?? null;
        if ($tags !== null) {
            $this->updateArticleTags($tags, $pressArticleElement);
        }

        $pressArticleElement->persistElementData();
    }

    private function updateArticleTags(array $tags, pressArticleElement $pressArticleElement): void
    {
        $pressArticleElement->addTags($tags);
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
}