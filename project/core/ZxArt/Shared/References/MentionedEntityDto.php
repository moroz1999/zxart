<?php

declare(strict_types=1);

namespace ZxArt\Shared\References;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Shared\Rest\MentionedEntityRestDto;

/**
 * What a link to a mentioned entity shows and where it leads.
 *
 * @psalm-api
 */
#[Map(target: MentionedEntityRestDto::class)]
final readonly class MentionedEntityDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
