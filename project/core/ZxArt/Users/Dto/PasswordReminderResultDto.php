<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Users\Rest\PasswordReminderResultRestDto;

#[Map(target: PasswordReminderResultRestDto::class)]
readonly class PasswordReminderResultDto
{
    public function __construct(
        public bool $success,
        public string $message,
    ) {
    }
}
