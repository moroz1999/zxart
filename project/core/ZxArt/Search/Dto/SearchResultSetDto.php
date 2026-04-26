<?php

declare(strict_types=1);

namespace ZxArt\Search\Dto;

readonly class SearchResultSetDto
{
    /**
     * @param object[] $items items are typed per set: AuthorListItemDto for author/authorAlias,
     *                        GroupListItemDto for group/groupAlias, PictureDto for zxPicture,
     *                        SearchItemDto for any other type.
     */
    public function __construct(
        public string $type,
        public bool $partial,
        public int $totalCount,
        public array $items,
    ) {
    }
}
