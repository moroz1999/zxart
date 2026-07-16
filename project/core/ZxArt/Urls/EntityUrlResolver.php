<?php

declare(strict_types=1);

namespace ZxArt\Urls;

use structureElement;

/**
 * Single source of truth for the new clean front-end URLs of business entities.
 *
 * Maps a structure element (by its legacy structureType + id) to its new SPA URL
 * (e.g. a `zxProd` with id 123 → `/prod/123`). Used both to emit links in REST
 * DTOs and to drive the legacy→new 301 redirects. Returns null for types that do
 * not yet have a routed SPA page, so callers fall back to the legacy URL.
 */
class EntityUrlResolver
{
    /** legacy structureType => new URL prefix */
    private const TYPE_PREFIX = [
        'author' => 'author',
        // aliases are rendered by the same detail page as their real entity,
        // so they resolve to the same route prefix using the alias's own id
        'authorAlias' => 'author',
        'group' => 'group',
        'groupAlias' => 'group',
        'party' => 'party',
        'zxProd' => 'prod',
        'zxRelease' => 'release',
        'zxPicture' => 'picture',
        'zxMusic' => 'tune',
        'pressArticle' => 'press',
    ];

    public function resolveByType(string $structureType, int $id): ?string
    {
        $prefix = self::TYPE_PREFIX[$structureType] ?? null;
        if ($prefix === null || $id <= 0) {
            return null;
        }

        return '/' . $prefix . '/' . $id;
    }

    public function resolve(structureElement $element): ?string
    {
        return $this->resolveByType($element->structureType, (int)$element->id);
    }

    /**
     * New clean URL for the element, falling back to its legacy URL for types
     * that do not yet have a routed SPA page.
     */
    public function urlFor(structureElement $element): string
    {
        return $this->resolve($element) ?? (string)$element->getUrl();
    }
}
