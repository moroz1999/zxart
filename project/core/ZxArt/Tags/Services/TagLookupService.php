<?php

declare(strict_types=1);

namespace ZxArt\Tags\Services;

use structureManager;
use tagElement;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Tags\Dbal\TagSearchDbal;
use ZxArt\Tags\Dto\FoundTagDto;
use ZxArt\Tags\Dto\TagLookupItemDto;
use ZxArt\Urls\EntityUrlResolver;

/**
 * What a tag picker offers for a typed phrase: the tags `TagSearchDbal` finds,
 * the most used first, each named in the current language.
 *
 * @psalm-api
 */
final readonly class TagLookupService
{
    private const int LIMIT = 30;

    public function __construct(
        private TagSearchDbal $tagSearchDbal,
        private structureManager $structureManager,
        private EntityUrlResolver $entityUrlResolver,
    ) {
    }

    /**
     * @return TagLookupItemDto[]
     */
    public function lookup(string $phrase): array
    {
        $items = [];
        foreach ($this->tagSearchDbal->find($phrase, self::LIMIT) as $found) {
            $item = $this->toItem($found);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }

    private function toItem(FoundTagDto $found): ?TagLookupItemDto
    {
        $tag = $this->structureManager->getElementById($found->id);
        if (!$tag instanceof tagElement) {
            return null;
        }
        $name = ElementFields::text($tag, 'title');
        $synonym = ElementFields::text($tag, 'synonym');
        $description = ElementFields::text($tag, 'description');

        return new TagLookupItemDto(
            id: $found->id,
            title: $name . ($synonym !== '' ? ', ' . $synonym : '') . ($description !== '' ? ' (' . $description . ')' : ''),
            value: $name,
            synonym: $synonym,
            description: $description,
            url: $this->entityUrlResolver->urlFor($tag),
        );
    }
}
