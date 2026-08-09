<?php

declare(strict_types=1);

namespace ZxArt\Hardware\Dto;

/**
 * The labels submitted for one language by the management form.
 *
 * Deliberately carries no language of its own: it arrives as the value of a
 * language-keyed map, so repeating the code inside the object would be a second
 * source of truth for the same fact.
 *
 * Both fields default to empty so a half-filled form reaches
 * {@see \ZxArt\Hardware\HardwareCatalogService}, which reports precisely which
 * language is incomplete, instead of failing as a malformed request.
 */
readonly class HardwareNameInputDto
{
    public function __construct(
        public string $name = '',
        public string $shortName = '',
    ) {
    }
}
