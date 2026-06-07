<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use authorElement;
use structureManager;
use ZxArt\Prods\Dto\PressArticlesDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\PressArticlePreviewFactory;

readonly class AuthorMentionsService
{
    public function __construct(
        private structureManager $structureManager,
        private PressArticlePreviewFactory $previewFactory,
    ) {
    }

    public function getMentions(int $authorId): PressArticlesDto
    {
        $author = $this->structureManager->getElementById($authorId);
        if (!($author instanceof authorElement) && !($author instanceof authorAliasElement)) {
            throw new ProdDetailsException('Author or alias not found', 404);
        }

        return new PressArticlesDto(articles: $this->previewFactory->createList($author->getPressMentions()));
    }
}
