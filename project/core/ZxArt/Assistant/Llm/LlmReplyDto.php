<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Llm;

final readonly class LlmReplyDto
{
    /**
     * @param list<LlmToolCallDto> $toolCalls
     */
    public function __construct(
        public string $text,
        public array $toolCalls,
        public int $tokensIn,
        public int $tokensOut,
    ) {
    }
}
