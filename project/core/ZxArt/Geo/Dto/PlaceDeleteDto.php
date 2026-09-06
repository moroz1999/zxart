<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dto;

/**
 * A delete request for one country or city.
 *
 * `id` has no default, so a body without it is rejected by the serializer as a
 * bad request rather than reaching the service as a zero.
 */
readonly class PlaceDeleteDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
