<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Domain\Exception;

final class InvalidPreferenceCodeException extends UserPreferencesException
{
    public static function fromCode(string $code): self
    {
        return new self("Invalid preference code: {$code}");
    }
}
