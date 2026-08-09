<?php

declare(strict_types=1);

namespace ZxArt\Hardware\Dto;

use ZxArt\Hardware\HardwareGroup;

/**
 * A create or update request for one hardware item.
 *
 * Denormalized from the request body by `symfony/serializer`, so the controller
 * never handles an untyped array: an unknown category or a non-numeric id fails
 * as a bad request before anything below the controller sees it.
 *
 * Optional fields carry defaults on purpose. Anything the *domain* considers
 * incomplete — a missing language, a blank label, a malformed code — is checked
 * by {@see \ZxArt\Hardware\HardwareCatalogService}, which can say which field is
 * wrong and why; the serializer can only say the payload did not fit.
 */
readonly class HardwareSaveDto
{
    /**
     * @param array<string, HardwareNameInputDto> $names keyed by {@see \ZxArt\Shared\InterfaceLanguage} value
     */
    public function __construct(
        public string $code,
        public HardwareGroup $category,
        public ?int $id = null,
        public int $position = 0,
        public array $names = [],
    ) {
    }
}
