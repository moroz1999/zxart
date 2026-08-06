<?php

declare(strict_types=1);

namespace ZxArt\Press\Services;

use pressArticleElement;
use structureElement;
use structureManager;
use ZxArt\Press\Exception\PressDetailsException;
use ZxArt\Press\Dto\PressDetailsDto;
use ZxArt\Press\Dto\PressMentionDto;
use ZxArt\Press\Dto\PressPublicationDto;
use ZxArt\Press\Dto\PressTagDto;
use ZxArt\Urls\EntityUrlResolver;
use zxProdElement;

/**
 * Builds press article details independently of the transport representation.
 */
readonly class PressDetailsService
{
    public function __construct(
        private structureManager $structureManager,
        private EntityUrlResolver $entityUrlResolver,
    ) {
    }

    public function getDetails(int $articleId): PressDetailsDto
    {
        $element = $this->structureManager->getElementById($articleId);
        if (!$element instanceof pressArticleElement) {
            throw new PressDetailsException('Press article not found', 404);
        }

        $parent = $element->getParent();

        return new PressDetailsDto(
            id: (int)$element->getId(),
            title: $this->decode($element->getH1()),
            shortTitle: $this->decode($element->getShortTitle()),
            url: $this->entityUrlResolver->urlFor($element),
            externalLink: ((string)$element->externalLink) !== '' ? (string)$element->externalLink : null,
            introduction: ((string)$element->introduction) !== '' ? (string)$element->introduction : null,
            content: $this->buildContent($element),
            tags: $this->buildTags($element),
            authors: $this->buildMentions($element->authors),
            people: $this->buildMentions($element->people),
            groups: $this->buildMentions($element->groups),
            software: $this->buildMentions($element->software),
            pictures: $this->buildMentions($element->pictures),
            tunes: $this->buildMentions($element->tunes),
            parties: $this->buildMentions($element->parties),
            publication: $parent !== null ? $this->buildPublication($parent) : null,
        );
    }

    /**
     * The article as it can be read: the processed text, or the archived original
     * while the AI has not produced one. Both carry the markup the magazine text
     * was typed with, so the page renders them the same way.
     */
    private function buildContent(pressArticleElement $element): ?string
    {
        $content = $element->isContentOriginal()
            ? $element->getOriginalContent()
            : (string)$element->content;

        return $content !== '' ? $content : null;
    }

    private function buildPublication(zxProdElement $publication): PressPublicationDto
    {
        $year = $publication->year;

        return new PressPublicationDto(
            id: $publication->getId(),
            title: $this->decode((string)$publication->getTitle()),
            url: $this->entityUrlResolver->urlFor($publication),
            year: $year > 0 ? $year : null,
            imageUrl: $publication->getCoverImageUrl(),
            articles: $this->buildIssueArticles($publication),
        );
    }

    /**
     * The publication's table of contents, in publishing order.
     *
     * @return list<PressMentionDto>
     */
    private function buildIssueArticles(zxProdElement $publication): array
    {
        $articles = [];
        foreach ($publication->articles as $article) {
            $articles[] = new PressMentionDto(
                id: $article->getId(),
                title: $this->decode($article->title),
                url: $this->entityUrlResolver->urlFor($article),
            );
        }

        return $articles;
    }

    /**
     * @param mixed $elements
     * @return list<PressMentionDto>
     */
    private function buildMentions($elements): array
    {
        if (!is_array($elements)) {
            return [];
        }
        $mentions = [];
        foreach ($elements as $element) {
            if ($element instanceof structureElement) {
                $mentions[] = $this->buildMention($element);
            }
        }
        usort($mentions, static fn(PressMentionDto $a, PressMentionDto $b) => strcmp($a->title, $b->title));
        return $mentions;
    }

    private function buildMention(structureElement $element): PressMentionDto
    {
        $title = method_exists($element, 'getSearchTitle')
            ? (string)$element->getSearchTitle()
            : (string)$element->getTitle();
        return new PressMentionDto(
            id: (int)$element->getId(),
            title: $this->decode($title),
            url: $this->entityUrlResolver->urlFor($element),
        );
    }

    /** @return list<PressTagDto> */
    private function buildTags(pressArticleElement $element): array
    {
        $tags = [];
        foreach ($element->getTagsList() as $tag) {
            if ($tag instanceof structureElement) {
                $tags[] = new PressTagDto(
                    title: $this->decode((string)$tag->title),
                    url: $this->entityUrlResolver->urlFor($tag),
                );
            }
        }
        return $tags;
    }

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
