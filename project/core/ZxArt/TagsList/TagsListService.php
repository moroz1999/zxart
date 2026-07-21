<?php

declare(strict_types=1);

namespace ZxArt\TagsList;

use LanguagesManager;
use structureManager;
use tagElement;
use ZxArt\Shared\DatabaseTable;
use ZxArt\TagsList\Dto\TagListItemDto;
use ZxArt\TagsList\Repositories\TagsListRepository;

/**
 * Builds the tag cloud for a collection section. The section's tag ids come from
 * the repository (structure links); titles and usage amounts are read from the
 * loaded, language-resolved tag elements.
 */
readonly class TagsListService
{
    public const int MINIMUM_ALLOWED_AMOUNT = 1;
    public const int DEFAULT_MINIMUM_AMOUNT = 10;

    public function __construct(
        private TagsListRepository $tagsListRepository,
        private structureManager $structureManager,
        private LanguagesManager $languagesManager,
    ) {
    }

    /**
     * @return TagListItemDto[]
     */
    public function getSectionTags(string $section, int $minimumAmount = self::DEFAULT_MINIMUM_AMOUNT): array
    {
        $table = match ($section) {
            'graphics' => DatabaseTable::ZxPicture,
            'music' => DatabaseTable::ZxMusic,
            'software' => DatabaseTable::ZxProd,
            default => null,
        };
        if ($table === null) {
            return [];
        }

        $amounts = $this->tagsListRepository->getSectionTagAmounts($table, $minimumAmount);
        if ($amounts === []) {
            return [];
        }

        $ids = array_keys($amounts);
        // The language element id doubles as the parent scope, resolving titles for the current language.
        $elements = $this->structureManager->getElementsByIdList($ids, (int)$this->languagesManager->getCurrentLanguageId());

        $items = [];
        foreach ($elements as $element) {
            if (!$element instanceof tagElement) {
                continue;
            }
            $id = (int)$element->id;
            $amount = $amounts[$id] ?? null;
            if ($amount === null) {
                continue;
            }
            $items[] = new TagListItemDto(
                id: $id,
                title: html_entity_decode($element->title, ENT_QUOTES),
                amount: $amount,
            );
        }

        usort($items, static fn(TagListItemDto $a, TagListItemDto $b): int => strcasecmp($a->title, $b->title));

        return $items;
    }
}
