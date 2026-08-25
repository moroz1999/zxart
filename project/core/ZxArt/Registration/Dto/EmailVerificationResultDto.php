<?php

declare(strict_types=1);

namespace ZxArt\Registration\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Registration\Rest\EmailVerificationResultRestDto;

#[Map(target: EmailVerificationResultRestDto::class)]
readonly class EmailVerificationResultDto
{
    public function __construct(
        public bool $success,
        public string $message,
    ) {
    }
}
