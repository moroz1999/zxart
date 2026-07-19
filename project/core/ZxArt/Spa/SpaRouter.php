<?php

declare(strict_types=1);

namespace ZxArt\Spa;

final class SpaRouter
{
    /** @var string[] */
    private const array ROUTE_PATTERNS = [
        '#^/author/\d+(/[a-z0-9-]+)?/?$#',
        '#^/author-alias/\d+/[a-z0-9-]+/?$#',
        '#^/group/\d+(/[a-z0-9-]+)?/?$#',
        '#^/group-alias/\d+/[a-z0-9-]+/?$#',
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
        '#^/password-reminder/?$#',
        '#^/search/?$#',
        '#^/$#',
        '#^/(prods|pictures|music)/?$#',
        '#^/(pictures|music)/(search|tags|top)/?$#',
        '#^/(groups|authors)(/[a-zA-Z])?/?$#',
        '#^/parties(/\d+)?/?$#',
        '#^/stats(/[a-z0-9-]+)?/?$#',
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
