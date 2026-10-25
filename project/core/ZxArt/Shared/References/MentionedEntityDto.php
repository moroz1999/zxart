<?php

declare(strict_types=1);

namespace ZxArt\Shared\References;

/**
 * What a link to a mentioned entity shows and where it leads.
 *
 * @psalm-api
 */
final readonly class MentionedEntityDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
