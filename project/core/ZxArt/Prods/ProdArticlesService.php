<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use ZxArt\Prods\Dto\PressArticlesDto;

readonly class ProdArticlesService
{
    public function __construct(
        private ProdElementService $prodElementService,
        private PressArticlePreviewFactory $previewFactory,
    ) {
    }

    public function getArticles(int $elementId): PressArticlesDto
    {
        $prod = $this->prodElementService->get($elementId);

        return new PressArticlesDto(articles: $this->previewFactory->createList($prod->articles));
    }

    public function getMentions(int $elementId): PressArticlesDto
    {
        $prod = $this->prodElementService->get($elementId);

        return new PressArticlesDto(articles: $this->previewFactory->createList($prod->getPressMentions()));
    }
}
