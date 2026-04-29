<?php

declare(strict_types=1);

namespace ZxArt\Tags;

use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\Tags\Dto\TagDto;
use ZxArt\Tags\Dto\TagsDto;
use ZxArt\Tags\Exception\TagsException;
use zxMusicElement;
use zxPictureElement;
use zxProdElement;

readonly class TagsService
{
    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
    ) {
    }

    public function getTags(int $elementId): TagsDto
    {
        $element = $this->resolveAuthorizedElement($elementId);

        return $this->buildTagsDto($elementId, $element);
    }

    /**
     * @param array<array{id?: mixed, title?: mixed}> $rawTags
     */
    public function saveTags(int $elementId, array $rawTags): TagsDto
    {
        $element = $this->resolveAuthorizedElement($elementId);
        $normalizedTitles = $this->normalizeTitles($rawTags);

        $element->updateTagsFromList($normalizedTitles);
        $element->persistElementData();

        $this->structureManager->clearElementCache($elementId);

        return $this->buildTagsDto($elementId, $element);
    }

    /**
     * @param array<array{id?: mixed, title?: mixed}> $rawTags
     * @return string[]
     */
    private function normalizeTitles(array $rawTags): array
    {
        $normalizedTags = [];

        foreach ($rawTags as $rawTag) {
            if (!is_array($rawTag)) {
                continue;
            }

            $title = trim((string)($rawTag['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $normalizedKey = mb_strtolower($title);
            if (!isset($normalizedTags[$normalizedKey])) {
                $normalizedTags[$normalizedKey] = $title;
            }
        }

        return array_values($normalizedTags);
    }

    private function buildTagsDto(
        int $elementId,
        zxPictureElement|zxMusicElement|zxProdElement $element,
    ): TagsDto {
        $tags = [];
        foreach ((array)$element->getTagsList() as $tagElement) {
            $tags[] = new TagDto(
                id: (int)$tagElement->getId(),
                title: $this->decodeText((string)$tagElement->title),
            );
        }

        $selectedTagIds = [];
        foreach ($tags as $tag) {
            $selectedTagIds[$tag->id] = true;
        }

        $suggestedTags = [];
        foreach ((array)$element->getSuggestedTags() as $tagElement) {
            $tagId = (int)$tagElement->getId();
            if (isset($selectedTagIds[$tagId])) {
                continue;
            }

            $description = trim($this->decodeText((string)($tagElement->description ?? '')));
            $suggestedTags[] = new TagDto(
                id: $tagId,
                title: $this->decodeText((string)$tagElement->title),
                description: $description !== '' ? $description : null,
            );
        }

        return new TagsDto(
            elementId: $elementId,
            tags: $tags,
            suggestedTags: $suggestedTags,
        );
    }

    private function resolveAuthorizedElement(int $elementId): zxPictureElement|zxMusicElement|zxProdElement
    {
        $element = $this->structureManager->getElementById($elementId);
        if (!$element instanceof structureElement) {
            throw new TagsException('Element not found', 404);
        }

        if (
            !$element instanceof zxPictureElement &&
            !$element instanceof zxMusicElement &&
            !$element instanceof zxProdElement
        ) {
            throw new TagsException('Unsupported element type', 400);
        }

        $structureType = (string)$element->structureType;
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction($elementId, 'submitTags', $structureType);
        if ($isAllowed !== true) {
            throw new TagsException('Forbidden', 403);
        }

        return $element;
    }

    private function decodeText(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
