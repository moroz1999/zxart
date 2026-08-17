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
        yield ['/author/42/aliases/add'];
        yield ['/author/42/pictures/add'];
        yield ['/author/42/music/add'];
        yield ['/author/42/prods/add'];
        yield ['/group/42/prods/add'];
        yield ['/party/42/pictures/add'];
        yield ['/prod/42/releases/add'];
        yield ['/prod/42/articles/add'];
        yield ['/author-alias/42/edit'];
        yield ['/authors/add'];
        yield ['/authors/a/add'];
        yield ['/groups/add'];
        yield ['/groups/a/add'];
        yield ['/group-alias/42/join'];
        yield ['/parties/2026/add'];
        yield ['/pictures/top'];
        yield ['/pictures/top/loading'];
        yield ['/music/top'];
        yield ['/music/top/turbosound'];
        yield ['/prods/tags'];
        yield ['/prods/batch-upload'];
        yield ['/stats/activity'];
        yield ['/manage'];
        yield ['/manage/hardware'];
        yield ['/manage/hardware/add'];
        yield ['/manage/hardware/42'];
    }

    public static function legacyRoutes(): iterable
    {
        yield ['/eng/software/games/example/'];
        yield ['/index.php?id=123&action=showForm'];
        yield ['/unknown-route'];
        // the legacy Smarty panel owns /admin, which is why the section lives at /manage
        yield ['/admin'];
        yield ['/admin/hardware'];
    }

    public static function menuRouteRoots(): iterable
    {
        foreach ([
            'prods', 'pictures', 'music', 'authors', 'parties', 'groups',
            'comments', 'geo', 'about', 'stats', 'feedback', 'file-search', 'manage',
        ] as $routeRoot) {
            yield $routeRoot => [$routeRoot];
        }
    }
}
