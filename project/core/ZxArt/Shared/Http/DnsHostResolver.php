<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

use Override;

/**
 * Resolves through the system's DNS: the A and AAAA records of the name.
 */
final readonly class DnsHostResolver implements HostResolver
{
    #[Override]
    public function resolve(string $host): array
    {
        $records = dns_get_record($host, DNS_A | DNS_AAAA);
        if ($records === false) {
            return [];
        }
        $addresses = [];
        /** @var array{ip?: string, ipv6?: string} $record */
        foreach ($records as $record) {
            $address = $record['ip'] ?? $record['ipv6'] ?? null;
            if ($address !== null) {
                $addresses[] = $address;
            }
        }

        return $addresses;
    }
}
