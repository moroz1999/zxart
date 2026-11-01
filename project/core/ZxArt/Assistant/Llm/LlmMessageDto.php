<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Llm;

use ZxArt\Shared\Images\ImageDto;

final readonly class LlmMessageDto
{
    /**
     * @param list<LlmToolCallDto> $toolCalls the calls an assistant message makes
     * @param ?string $toolCallId the call a tool message answers
     * @param list<ImageDto> $images shown along with a user message
     */
    public function __construct(
        public LlmRole $role,
        public string $text,
        public array $toolCalls = [],
        public ?string $toolCallId = null,
        public array $images = [],
    ) {
    }
}
