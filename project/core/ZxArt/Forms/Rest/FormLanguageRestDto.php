<?php

declare(strict_types=1);

namespace ZxArt\Forms\Rest;

readonly class FormLanguageRestDto
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }
}
