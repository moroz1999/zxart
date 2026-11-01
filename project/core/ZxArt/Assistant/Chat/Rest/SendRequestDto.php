<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Rest;

use ZxArt\Assistant\Chat\Dto\PageDto;

final readonly class SendRequestDto
{
    public function __construct(
        public int $conversationId,
        public string $text,
        public ?PageDto $page = null,
    ) {
    }
}
