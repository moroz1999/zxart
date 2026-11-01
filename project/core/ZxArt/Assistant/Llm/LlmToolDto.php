<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Llm;

final readonly class LlmToolDto
{
    /**
     * @param array<string, mixed> $parameters JSON schema of the arguments
     */
    public function __construct(
        public string $name,
        public string $description,
        public array $parameters,
    ) {
    }
}
