<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorProdRestDto;

#[Map(target: AuthorProdRestDto::class)]
readonly class AuthorProdDto
{
    /**
     * @param string[]                $rolesInProd
     * @param AuthorProdCoAuthorDto[] $coAuthors
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
        public array $coAuthors,
        public string $type = 'prod',
    ) {
    }
}
