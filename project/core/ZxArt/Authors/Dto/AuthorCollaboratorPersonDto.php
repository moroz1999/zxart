<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

readonly final class AuthorCollaboratorPersonDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public int $jointPictures,
        public int $jointTunes,
        public int $jointProds,
        public int $jointTotal,
    ) {
    }
}
