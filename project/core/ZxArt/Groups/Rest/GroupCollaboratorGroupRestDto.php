<?php

declare(strict_types=1);

namespace ZxArt\Groups\Rest;

readonly final class GroupCollaboratorGroupRestDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?string $years,
        public int $membersCount,
        public int $jointProds,
    ) {
    }
}
