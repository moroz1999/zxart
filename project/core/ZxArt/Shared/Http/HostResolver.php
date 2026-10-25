<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

/**
 * The addresses a host name resolves to.
 */
interface HostResolver
{
    /**
     * @return string[] IPv4 and IPv6 addresses, none when the name does not resolve
     */
    public function resolve(string $host): array;
}
