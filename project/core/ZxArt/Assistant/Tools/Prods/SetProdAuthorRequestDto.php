<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Prods;

/**
 * @psalm-api
 */
final readonly class SetProdAuthorRequestDto
{
    /**
     * @param int $prodId the production
     * @param int $authorId an author or an author alias
     * @param list<string> $roles roles from list_options; they replace the author's roles, none stands for unknown
     */
    public function __construct(
        public int $prodId,
        public int $authorId,
        public array $roles,
    ) {
    }
}
