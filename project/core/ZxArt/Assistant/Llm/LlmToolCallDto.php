<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Llm;

final readonly class LlmToolCallDto
{
    public function __construct(
        public string $id,
        public string $name,
        /** JSON as the model wrote it. */
        public string $arguments,
    ) {
    }
}
