<?php

declare(strict_types=1);

namespace ZxArt\Tags;

/**
 * An item a visitor tags. Tags exist only as links to tag elements; the titles
 * are what the item's form reads and submits.
 */
interface TagsHolderInterface
{
    /**
     * @return list<string>
     */
    public function getTagsTexts(): array;

    public function updateTagsInfo(): void;
}
