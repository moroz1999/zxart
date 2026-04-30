<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use authorAliasElement;
use authorElement;
use pressArticleElement;
use structureElement;
use structureManager;
use ZxArt\Prods\Dto\PressArticleAuthorDto;
use ZxArt\Prods\Dto\PressArticlePreviewDto;
use ZxArt\Prods\Dto\PressArticlePublicationDto;
use ZxArt\Prods\Dto\PressArticlesDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use zxProdElement;

readonly class ProdArticlesService
{
    public function __construct(
        private structureManager $structureManager,
    ) {
    }

    public function getArticles(int $elementId): PressArticlesDto
    {
        $prod = $this->getProd($elementId);

        $articles = [];
        foreach ($prod->articles as $article) {
            if (!$article instanceof pressArticleElement) {
                continue;
            }
            $articles[] = $this->buildArticle($article);
        }

        return new PressArticlesDto(articles: $articles);
    }

    public function getMentions(int $elementId): PressArticlesDto
    {
        $prod = $this->getProd($elementId);

        $articles = [];
        foreach ($prod->getPressMentions() as $article) {
            if (!$article instanceof pressArticleElement) {
                continue;
            }
            $articles[] = $this->buildArticle($article);
        }

        return new PressArticlesDto(articles: $articles);
    }

    private function getProd(int $elementId): zxProdElement
    {
        $element = $this->structureManager->getElementById($elementId);
        if (!$element instanceof zxProdElement) {
            throw new ProdDetailsException('Prod not found', 404);
        }
        return $element;
    }

    private function buildArticle(pressArticleElement $article): PressArticlePreviewDto
    {
        return new PressArticlePreviewDto(
            id: $article->getId(),
            title: $this->decodeText($article->title),
            url: (string)$article->getUrl(),
            introduction: $article->introduction,
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

        $year = $parent->year;

        return new PressArticlePublicationDto(
            id: $parent->getId(),
            title: $this->decodeText((string)$parent->getTitle()),
            url: (string)$parent->getUrl(),
            year: $year > 0 ? $year : null,
        );
    }

    private function decodeText(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
