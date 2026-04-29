<?php

declare(strict_types=1);

namespace ZxArt\Search\Dto;

readonly class SearchResultSetDto
{
    /**
     * @param object[] $items items are typed per set: AuthorListItemDto for author/authorAlias,
     *                        GroupListItemDto for group/groupAlias, PictureDto for zxPicture,
     *                        ProdDto for zxProd, TuneDto for zxMusic, PressArticleDto for
     *                        pressArticle, PartyDto for party.
     */
    public function __construct(
        public string $type,
        public int $totalCount,
        public array $items,
    ) {
    }
}
