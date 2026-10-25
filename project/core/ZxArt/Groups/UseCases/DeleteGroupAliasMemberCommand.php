<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

final readonly class DeleteGroupAliasMemberCommand
{
    public function __construct(
        public int $aliasId,
        public int $authorId,
    ) {
    }
}
