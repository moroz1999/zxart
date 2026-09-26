<?php

declare(strict_types=1);

namespace ZxArt\Releases\ItchIo;

readonly class ItchIoFile
{
    public function __construct(
        public string $name,
        public string $data,
    ) {
    }
}
