<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use authorAliasElement;
use authorElement;
use pressArticleElement;
use structureElement;
use zxProdElement;
use ZxArt\Prods\Dto\PressArticleAuthorDto;
use ZxArt\Prods\Dto\PressArticlePreviewDto;
use ZxArt\Prods\Dto\PressArticlePublicationDto;

/**
 * Builds press-article preview DTOs from press article elements.
 * Shared by prod and group details so both render via the same zx-article-preview component.
 */
readonly class PressArticlePreviewFactory
{
    private const string PUBLICATION_IMAGE_PRESET = 'prodImage';

    /**
     * @param iterable<structureElement> $articles
     * @return PressArticlePreviewDto[]
     */
    public function createList(iterable $articles): array
    {
        $previews = [];
        foreach ($articles as $article) {
            if (!$article instanceof pressArticleElement) {
                continue;
            }
            $previews[] = $this->create($article);
        }
        return $previews;
    }

    public function create(pressArticleElement $article): PressArticlePreviewDto
    {
        return new PressArticlePreviewDto(
            id: $article->getId(),
            title: $this->decodeText((string)$article->title),
            url: (string)$article->getUrl(),
            introduction: (string)$article->introduction,
            authors: $this->buildAuthors($article),
            publication: $this->buildPublication($article),
        );
    }

    /**
     * @return PressArticleAuthorDto[]
     */
    private function buildAuthors(pressArticleElement $article): array
    {
        $authors = [];
        foreach ($article->authors as $author) {
            if (!$author instanceof authorElement && !$author instanceof authorAliasElement) {
                continue;
            }
            $authors[] = new PressArticleAuthorDto(
                id: $author->getId(),
                title: $this->decodeText((string)$author->getTitle()),
                url: (string)$author->getUrl(),
            );
        }
        return $authors;
    }

    private function buildPublication(pressArticleElement $article): ?PressArticlePublicationDto
    {
        $parent = $article->getParent();
        if (!$parent instanceof structureElement) {
            return null;
        }

        $year = (int)$parent->year;

        return new PressArticlePublicationDto(
            id: $parent->getId(),
            title: $this->decodeText((string)$parent->getTitle()),
            url: (string)$parent->getUrl(),
            year: $year > 0 ? $year : null,
            imageUrl: $this->resolvePublicationImageUrl($parent),
        );
    }

    private function resolvePublicationImageUrl(structureElement $publication): ?string
    {
        if (!$publication instanceof zxProdElement) {
            return null;
        }

        /** @var mixed $rawImageUrl */
        $rawImageUrl = $publication->getImageUrl(0, self::PUBLICATION_IMAGE_PRESET);
        if (!is_string($rawImageUrl) || $rawImageUrl === '') {
            return null;
        }

        return $rawImageUrl;
    }

    private function decodeText(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
