<?php

declare(strict_types=1);

namespace ZxArt\Forms\Dto;

/**
 * One interface language a multi-language form field is edited in.
 */
readonly class FormLanguageDto
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }
}
