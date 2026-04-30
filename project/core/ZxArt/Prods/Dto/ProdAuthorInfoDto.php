<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdAuthorInfoDto
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public array $roles,
    ) {
    }
}
