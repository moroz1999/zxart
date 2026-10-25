<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

use Override;

/**
 * Lets the site fetch a URL given from outside only when it is http(s) and its
 * host resolves to public addresses alone: loopback, private, link-local,
 * shared, reserved, multicast and NAT64 addresses are refused. A redirect is a
 * new URL and is checked again.
 *
 * @psalm-api
 */
final readonly class PublicAddressGuard implements UrlGuard
{
    private const array SCHEME_PORTS = ['http' => 80, 'https' => 443];

    /** ranges PHP's private and reserved filters let through */
    private const array REFUSED_RANGES = [
        '100.64.0.0/10',
        '198.18.0.0/15',
        '224.0.0.0/4',
        '64:ff9b::/96',
        'ff00::/8',
    ];

    public function __construct(
        private HostResolver $hostResolver,
    ) {
    }

    /**
     * @throws UnsafeUrlException
     */
    #[Override]
    public function check(string $url): GuardedUrlDto
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = $parts['host'] ?? '';
        if (!isset(self::SCHEME_PORTS[$scheme]) || $host === '') {
            throw new UnsafeUrlException("Only http and https addresses can be fetched: $url");
        }
        $port = $parts['port'] ?? self::SCHEME_PORTS[$scheme];

        $literal = trim($host, '[]');
        $addresses = filter_var($literal, FILTER_VALIDATE_IP) !== false
            ? [$literal]
            : $this->hostResolver->resolve($host);
        if ($addresses === []) {
            throw new UnsafeUrlException("The host does not resolve: $host");
        }
        foreach ($addresses as $address) {
            if (!$this->isPublic($address)) {
                throw new UnsafeUrlException("The host is not on the public internet: $host");
            }
        }

        return new GuardedUrlDto($url, $host, $port, $addresses[0]);
    }

    private function isPublic(string $address): bool
    {
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        foreach (self::REFUSED_RANGES as $range) {
            if ($this->isInRange($address, $range)) {
                return false;
            }
        }

        return true;
    }

    private function isInRange(string $address, string $range): bool
    {
        [$network, $bits] = explode('/', $range);
        $packedAddress = inet_pton($address);
        $packedNetwork = inet_pton($network);
        if ($packedAddress === false || $packedNetwork === false || strlen($packedAddress) !== strlen($packedNetwork)) {
            return false;
        }
        $fullBytes = intdiv((int)$bits, 8);
        if (substr($packedAddress, 0, $fullBytes) !== substr($packedNetwork, 0, $fullBytes)) {
            return false;
        }
        $remainingBits = (int)$bits % 8;
        if ($remainingBits === 0) {
            return true;
        }
        $mask = (0xFF << (8 - $remainingBits)) & 0xFF;

        return (ord($packedAddress[$fullBytes]) & $mask) === (ord($packedNetwork[$fullBytes]) & $mask);
    }
}
