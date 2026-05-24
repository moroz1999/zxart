<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class AuthorProdRestDto
{
    /**
     * @param string[]                   $rolesInProd
     * @param AuthorProdCoAuthorRestDto[] $coAuthors
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public int $year,
        public ?string $thumbnailUrl,
        public string $category,
        public float $votes,
        public int $votesAmount,
        public array $rolesInProd,
        #[Map(transform: MapCollection::class)]
        public array $coAuthors,
        public string $type = 'prod',
    ) {
    }
}
