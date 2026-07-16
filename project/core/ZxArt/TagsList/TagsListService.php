<?php

declare(strict_types=1);

namespace ZxArt\TagsList;

use LanguagesManager;
use structureManager;
use tagElement;
use ZxArt\TagsList\Dto\TagListItemDto;
use ZxArt\TagsList\Repositories\TagsListRepository;

/**
 * Builds the tag cloud for a collection section. The section's tag ids come from
 * the repository (structure links); titles and usage amounts are read from the
 * loaded, language-resolved tag elements.
 */
readonly class TagsListService
{
    private const array SECTION_TABLES = [
        'graphics' => 'module_zxpicture',
        'music' => 'module_zxmusic',
    ];

    public function __construct(
        private TagsListRepository $tagsListRepository,
        private structureManager $structureManager,
        private LanguagesManager $languagesManager,
    ) {
    }

    /**
     * @return TagListItemDto[]
     */
    public function getSectionTags(string $section): array
    {
        $table = self::SECTION_TABLES[$section] ?? null;
        if ($table === null) {
            return [];
        }

        $ids = $this->tagsListRepository->getSectionTagIds($table);
        if ($ids === []) {
            return [];
        }

        // The language element id doubles as the parent scope, resolving titles for the current language.
        /** @psalm-suppress InvalidArgument — legacy signature types the language/parent argument as bool */
        $elements = $this->structureManager->getElementsByIdList($ids, (int)$this->languagesManager->getCurrentLanguageId());

        $items = [];
        foreach ($elements as $element) {
            if (!$element instanceof tagElement) {
                continue;
            }
            /** @psalm-suppress UndefinedMagicPropertyFetch — module field on the tag element */
            $amount = (int)$element->amount;
            if ($amount <= 0) {
                continue;
            }
            $items[] = new TagListItemDto(
                id: (int)$element->id,
                title: html_entity_decode($element->title, ENT_QUOTES),
                amount: $amount,
            );
        }

        usort($items, static fn(TagListItemDto $a, TagListItemDto $b): int => strcasecmp($a->title, $b->title));

        return $items;
    }
}
