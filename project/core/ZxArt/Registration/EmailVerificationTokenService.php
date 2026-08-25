<?php

declare(strict_types=1);

namespace ZxArt\Registration;

use ConfigManager;

/**
 * Signs the address a verification link is issued for. The token proves nothing
 * but that the link came from us — receiving it in the mailbox is what proves
 * the address belongs to the account, so it carries no expiry.
 */
readonly class EmailVerificationTokenService
{
    public function __construct(private ConfigManager $configManager)
    {
    }

    public function create(string $email): string
    {
        return hash_hmac('sha256', $email, (string)$this->configManager->get('emails.dispatchmentSecret'));
    }

    public function isValid(string $email, string $token): bool
    {
        return $token !== '' && hash_equals($this->create($email), $token);
    }
}
