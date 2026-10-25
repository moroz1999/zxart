<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

final readonly class SetProdMemberCommand
{
    /**
     * @param int $authorId an author or an author alias
     * @param list<string> $roles none stands for an unknown role
     */
    public function __construct(
        public int $prodId,
        public int $authorId,
        public array $roles,
    ) {
    }
}
