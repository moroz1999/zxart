<?php

declare(strict_types=1);

namespace ZxArt\Tags\Dto;

final readonly class TagsSaveRequestDto
{
    /** @param list<string> $tags */
    public function __construct(public array $tags)
    {
    }
}
