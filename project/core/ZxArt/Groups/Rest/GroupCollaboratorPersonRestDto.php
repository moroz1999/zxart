<?php

declare(strict_types=1);

namespace ZxArt\Groups\Rest;

readonly final class GroupCollaboratorPersonRestDto
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public array $roles,
        public int $jointTotal,
    ) {
    }
}
