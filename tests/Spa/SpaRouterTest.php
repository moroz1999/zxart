<?php

declare(strict_types=1);

namespace Tests\Spa;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Spa\SpaRouter;

final class SpaRouterTest extends TestCase
{
    #[DataProvider('spaRoutes')]
    public function testRecognizesSpaRoute(string $uri): void
    {
        self::assertTrue(new SpaRouter()->isSpaRequest($uri));
    }

    #[DataProvider('legacyRoutes')]
    public function testRejectsLegacyRoute(string $uri): void
    {
        self::assertFalse(new SpaRouter()->isSpaRequest($uri));
    }

    #[DataProvider('menuRouteRoots')]
    public function testMenuRouteRootDoesNotResolveToController(string $routeRoot): void
    {
        $controllerName = str_replace('-', '', ucwords($routeRoot, '-'));

        self::assertFalse(
            class_exists('ZxArt\\Controllers\\' . $controllerName),
            sprintf('Menu route /%s is shadowed by a backend controller', $routeRoot),
        );
    }

    public static function spaRoutes(): iterable
    {
        yield ['/'];
        yield ['/prod/123'];
        yield ['/prod/123/edit?from=legacy'];
        yield ['/author/42/join'];
        yield ['/author-alias/42/edit'];
        yield ['/group-alias/42/convert-to-group'];
        yield ['/pictures/top'];
        yield ['/stats/activity'];
    }

    public static function legacyRoutes(): iterable
    {
        yield ['/eng/software/games/example/'];
        yield ['/index.php?id=123&action=showForm'];
        yield ['/unknown-route'];
    }

    public static function menuRouteRoots(): iterable
    {
        foreach ([
            'prods', 'pictures', 'music', 'authors', 'parties', 'groups',
            'comments', 'geo', 'about', 'stats', 'feedback', 'file-search',
        ] as $routeRoot) {
            yield $routeRoot => [$routeRoot];
        }
    }
}
