<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Fixtures;

final readonly class SampleItemDto
{
    /**
     * @param int $authorId the author to credit
     * @param string[] $roles the author's roles
     */
    public function __construct(
        public int $authorId,
        public array $roles,
    ) {
    }
}
