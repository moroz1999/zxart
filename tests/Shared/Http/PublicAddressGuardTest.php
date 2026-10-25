<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared\Http;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Shared\Http\GuardedUrlDto;
use ZxArt\Shared\Http\HostResolver;
use ZxArt\Shared\Http\PublicAddressGuard;
use ZxArt\Shared\Http\UnsafeUrlException;

class PublicAddressGuardTest extends TestCase
{
    public function testAPublicHostIsPinnedToTheAddressItResolvesTo(): void
    {
        $guard = $this->createGuard(['files.example' => ['93.184.216.34']]);

        self::assertEquals(
            new GuardedUrlDto('https://files.example/a/b.zip', 'files.example', 443, '93.184.216.34'),
            $guard->check('https://files.example/a/b.zip'),
        );
    }

    public function testThePortIsTheGivenOneOrTheSchemesDefault(): void
    {
        $guard = $this->createGuard(['files.example' => ['93.184.216.34']]);

        self::assertSame(80, $guard->check('http://files.example/b.zip')->port);
        self::assertSame(8080, $guard->check('http://files.example:8080/b.zip')->port);
    }

    public function testAPublicAddressLiteralNeedsNoLookup(): void
    {
        $guard = $this->createGuard([]);

        self::assertSame('93.184.216.34', $guard->check('http://93.184.216.34/b.zip')->address);
        self::assertSame('2a00:1450::1', $guard->check('http://[2a00:1450::1]/b.zip')->address);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function refusedUrls(): iterable
    {
        yield 'another scheme' => ['ftp://files.example/b.zip'];
        yield 'a file' => ['file:///etc/passwd'];
        yield 'no host' => ['http:///b.zip'];
        yield 'not a url' => ['files.example/b.zip'];
        yield 'loopback' => ['http://127.0.0.1/'];
        yield 'private' => ['http://10.1.2.3/'];
        yield 'link-local metadata' => ['http://169.254.169.254/latest/meta-data/'];
        yield 'shared address space' => ['http://100.64.0.1/'];
        yield 'multicast' => ['http://224.0.0.1/'];
        yield 'ipv6 loopback' => ['http://[::1]/'];
        yield 'ipv4-mapped private' => ['http://[::ffff:10.0.0.1]/'];
        yield 'nat64' => ['http://[64:ff9b::7f00:1]/'];
        yield 'a host resolving to a private address' => ['http://intranet.example/'];
        yield 'a host resolving to a public and a private address' => ['http://mixed.example/'];
        yield 'a host that does not resolve' => ['http://nowhere.example/'];
    }

    #[DataProvider('refusedUrls')]
    public function testRefuses(string $url): void
    {
        $guard = $this->createGuard([
            'intranet.example' => ['192.168.1.10'],
            'mixed.example' => ['93.184.216.34', '127.0.0.1'],
            'nowhere.example' => [],
        ]);

        $this->expectException(UnsafeUrlException::class);
        $guard->check($url);
    }

    /**
     * @param array<string, string[]> $addresses
     */
    private function createGuard(array $addresses): PublicAddressGuard
    {
        $resolver = $this->createStub(HostResolver::class);
        $resolver->method('resolve')->willReturnCallback(static fn(string $host): array => $addresses[$host] ?? []);

        return new PublicAddressGuard($resolver);
    }
}
