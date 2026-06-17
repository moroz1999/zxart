<?php

declare(strict_types=1);

namespace ZxArt\Email;

use SpamCheckAdapter;
use VerifaliaAdapter;
use VerifyMailAdapter;

/**
 * Validates an email address against local heuristics, a cached domain ban list
 * and external anti-spam services. Used by registration and feedback flows.
 */
final readonly class EmailValidationService
{
    private const int MAX_LOCAL_PART_DOTS = 2;

    public function __construct(
        private DomainBanRepository $domainBanRepository,
        private VerifaliaAdapter $verifaliaAdapter,
        private VerifyMailAdapter $verifyMailAdapter,
    ) {
    }

    /** True when the email address is acceptable, false when it is considered spam. */
    public function isAllowed(string $email): bool
    {
        $email = trim($email);
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return false;
        }

        [$localPart, $domain] = $parts;
        if (str_contains($localPart, '+') || substr_count($localPart, '.') > self::MAX_LOCAL_PART_DOTS) {
            return false;
        }

        $knownDecision = $this->domainBanRepository->findAllowed($domain);
        if ($knownDecision !== null) {
            return $knownDecision;
        }

        $allowed = $this->checkExternalServices($email);
        $this->domainBanRepository->save($domain, $allowed);

        return $allowed;
    }

    /** A domain is blocked only when an external service explicitly rejects it. */
    private function checkExternalServices(string $email): bool
    {
        foreach ($this->getAdapters() as $adapter) {
            if ($adapter->checkEmail($email) === false) {
                return false;
            }
        }

        return true;
    }

    /** @return SpamCheckAdapter[] */
    private function getAdapters(): array
    {
        return [
            $this->verifaliaAdapter,
            $this->verifyMailAdapter,
        ];
    }
}
