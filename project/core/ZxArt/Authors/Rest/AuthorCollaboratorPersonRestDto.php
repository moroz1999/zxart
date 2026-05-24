<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

readonly final class AuthorCollaboratorPersonRestDto
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
