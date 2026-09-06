<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dto;

/**
 * A create or update request for one country.
 *
 * Denormalized from the request body by `symfony/serializer`, so the controller
 * never handles an untyped array. What the *domain* considers incomplete — a
 * language without a title, coordinates outside the globe — is checked by
 * {@see \ZxArt\Geo\PlacesManageService}, which can say which field is wrong.
 */
readonly class CountrySaveDto
{
    /**
     * @param array<int, string> $titles keyed by language id
     */
    public function __construct(
        public array $titles = [],
        public ?int $id = null,
        public float $latitude = 0.0,
        public float $longitude = 0.0,
    ) {
    }
}
