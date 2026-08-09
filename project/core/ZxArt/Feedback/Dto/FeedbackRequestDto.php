<?php

declare(strict_types=1);

namespace ZxArt\Feedback\Dto;

final readonly class FeedbackRequestDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $message,
    ) {
    }
}
