<?php

declare(strict_types=1);

namespace ZxArt\ElementPrivileges\Dto;

readonly class ElementPrivilegesDto
{
    /**
     * @param array<string, bool> $privileges
     */
    public function __construct(
        public int $elementId,
        public array $privileges,
    ) {
    }
}
