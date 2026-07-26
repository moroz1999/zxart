<?php

declare(strict_types=1);

namespace ZxArt\Registration\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Registration\Rest\RegistrationResultRestDto;

#[Map(target: RegistrationResultRestDto::class)]
readonly class RegistrationResultDto
{
    public function __construct(
        public bool $success,
        public string $message,
    ) {
    }
}
