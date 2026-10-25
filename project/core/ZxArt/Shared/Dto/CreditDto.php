<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dto;

use ZxArt\Shared\References\EntityRefDto;

/**
 * An author (or alias) credited on an element, with the roles.
 *
 * @psalm-api
 */
final readonly class CreditDto
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        public EntityRefDto $author,
        public array $roles,
    ) {
    }
}
