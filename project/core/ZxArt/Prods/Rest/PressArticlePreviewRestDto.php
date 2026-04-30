<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class PressArticlePreviewRestDto
{
    /**
     * @param PressArticleAuthorRestDto[] $authors
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public string $introduction,
        #[Map(transform: MapCollection::class)]
        public array $authors,
        public ?PressArticlePublicationRestDto $publication,
    ) {
    }
}
