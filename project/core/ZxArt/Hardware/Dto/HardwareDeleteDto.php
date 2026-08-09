<?php

declare(strict_types=1);

namespace ZxArt\Hardware\Dto;

/**
 * A delete request for one hardware item.
 *
 * `id` has no default, so a body without it is rejected by the serializer as a
 * bad request rather than reaching the service as a zero.
 */
readonly class HardwareDeleteDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
