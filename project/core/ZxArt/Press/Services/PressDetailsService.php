<?php

declare(strict_types=1);

namespace ZxArt\Press\Services;

use pressArticleElement;
use structureElement;
use structureManager;
use ZxArt\Press\Exception\PressDetailsException;
use ZxArt\Press\Dto\PressDetailsDto;
use ZxArt\Press\Dto\PressMentionDto;
use ZxArt\Press\Dto\PressTagDto;
use ZxArt\Urls\EntityUrlResolver;

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
            url: $this->entityUrlResolver->urlFor($element),
            externalLink: ((string)$element->externalLink) !== '' ? (string)$element->externalLink : null,
            introduction: ((string)$element->introduction) !== '' ? (string)$element->introduction : null,
            content: ((string)$element->content) !== '' ? (string)$element->content : null,
            tags: $this->buildTags($element),
            authors: $this->buildMentions($element->authors),
            people: $this->buildMentions($element->people),
            groups: $this->buildMentions($element->groups),
            software: $this->buildMentions($element->software),
            pictures: $this->buildMentions($element->pictures),
            tunes: $this->buildMentions($element->tunes),
            parties: $this->buildMentions($element->parties),
            publication: $parent instanceof structureElement ? $this->buildMention($parent) : null,
        );
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
