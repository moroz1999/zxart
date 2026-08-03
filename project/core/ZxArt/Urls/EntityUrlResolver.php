<?php

declare(strict_types=1);

namespace ZxArt\Urls;

use folderElement;
use structureElement;
use userElement;
use ZxArt\Shared\StructureType;

/**
 * Single source of truth for clean SPA URLs of routed entities and sections.
 *
 * Maps a structure element (by its legacy structureType + id) to its SPA URL
 * (e.g. a `zxProd` with id 123 → `/prod/123`, a countries list → `/geo`). Used
 * both to emit links in REST DTOs and to drive legacy→SPA 301 redirects. Returns
 * null for types that do not have a routed SPA page.
 */
class EntityUrlResolver
{
    /** legacy section structureType => SPA URL */
    private const array SECTION_URL = [
        StructureType::AuthorsCatalogue->value => '/authors',
        StructureType::GroupsCatalogue->value => '/groups',
        StructureType::PartiesCatalogue->value => '/parties',
        StructureType::ZxProdCategoriesCatalogue->value => '/prods',
        StructureType::PicturesCatalogue->value => '/pictures',
        StructureType::MusicCatalogue->value => '/music',
        StructureType::CountriesList->value => '/geo',
        StructureType::Stats->value => '/stats',
        StructureType::CommentsList->value => '/comments',
        StructureType::Feedback->value => '/feedback',
        StructureType::Registration->value => '/register',
        StructureType::UserPlaylists->value => '/playlists',
    ];

    /** legacy structureType => new URL prefix */
    private const array TYPE_PREFIX = [
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
        'country' => 'geo/country',
        'city' => 'geo/city',
    ];

    public function resolveByType(string $structureType, int $id): ?string
    {
        $sectionUrl = self::SECTION_URL[$structureType] ?? null;
        if ($sectionUrl !== null) {
            return $sectionUrl;
        }

        if ($structureType === StructureType::ZxProdCategory->value && $id > 0) {
            return '/prods?cat=' . $id;
        }

        $prefix = self::TYPE_PREFIX[$structureType] ?? null;
        if ($prefix === null || $id <= 0) {
            return null;
        }

        return '/' . $prefix . '/' . $id;
    }

    public function resolve(structureElement $element): ?string
    {
        $url = $this->resolveByType($element->structureType, (int)$element->id);
        if ($url !== null || !$element instanceof folderElement) {
            return $url;
        }

        foreach ($element->getContentElements() as $contentElement) {
            $url = $this->resolveByType($contentElement->structureType, (int)$contentElement->id);
            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Clean SPA URL for the element, falling back to its legacy URL for types
     * that do not have a routed SPA page.
     */
    public function urlFor(structureElement $element): string
    {
        return $this->resolve($element) ?? (string)$element->getUrl();
    }

    /**
     * A user's public page is the author page they are connected to, so a user
     * without an author has no page and gets null. `userElement::getUrl()` is
     * not an alternative here: it returns the author's legacy URL, which does
     * not resolve as an SPA route.
     */
    public function urlForUser(userElement $user): ?string
    {
        $author = $user->getAuthorElement();

        return $author instanceof structureElement ? $this->urlFor($author) : null;
    }
}
