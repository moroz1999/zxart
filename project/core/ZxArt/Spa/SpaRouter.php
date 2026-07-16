<?php

namespace ZxArt\Spa;

/**
 * Decides whether an incoming public request must be served by the Angular SPA
 * shell instead of the legacy structure-tree page rendering.
 *
 * The pattern list mirrors the new clean-URL routing defined in the Angular
 * router (ng-zxart/src/app/app.routes.ts) and grows as entities are migrated.
 */
class SpaRouter
{
    /** @var string[] */
    private const ROUTE_PATTERNS = [
        '#^/author/\d+(/[a-z0-9-]+)?/?$#',
        '#^/author-alias/\d+/edit/?$#',
        '#^/group/\d+(/[a-z0-9-]+)?/?$#',
        '#^/party/\d+(/[a-z0-9-]+)?/?$#',
        '#^/prod/\d+(/[a-z0-9-]+)?/?$#',
        '#^/release/\d+(/[a-z0-9-]+)?/?$#',
        '#^/picture/\d+(/[a-z0-9-]+)?/?$#',
        '#^/tune/\d+(/[a-z0-9-]+)?/?$#',
        '#^/press/\d+(/[a-z0-9-]+)?/?$#',
        '#^/profile(/edit)?/?$#',
        '#^/playlists/?$#',
        '#^/playlist/\d+/?$#',
        '#^/register/?$#',
        '#^/$#',
        '#^/(prods|pictures|music)/?$#',
        '#^/(pictures|music)/search/?$#',
        '#^/(pictures|music)/tags/?$#',
        '#^/(groups|authors)(/[a-zA-Z])?/?$#',
        '#^/parties(/\d+)?/?$#',
        '#^/stats/?$#',
        '#^/geo/?$#',
        '#^/comments/?$#',
        '#^/feedback/?$#',
        '#^/about(/[a-z]+)?/?$#',
        '#^/file-search/?$#',
    ];

    public function isSpaRequest(string $uri): bool
    {
        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path)) {
            return false;
        }
        foreach (self::ROUTE_PATTERNS as $pattern) {
            if (preg_match($pattern, $path) === 1) {
                return true;
            }
        }

        return false;
    }
}
