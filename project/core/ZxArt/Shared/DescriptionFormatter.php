<?php

declare(strict_types=1);

namespace ZxArt\Shared;

final class DescriptionFormatter
{
    public function decode(string $description): string
    {
        do {
            $encodedDescription = $description;
            $description = html_entity_decode(
                $encodedDescription,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8',
            );
        } while ($description !== $encodedDescription);

        return preg_replace('#</?pre(?:\s[^>]*)?>#iu', '', $description) ?? $description;
    }
}
