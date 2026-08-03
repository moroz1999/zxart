<?php

declare(strict_types=1);

use ZxArt\Urls\EntityUrlResolver;

/**
 * Canonical absolute address of the element's page, for metadata that leaves
 * the site: `og:url` and ld+json. The legacy structure URL is not canonical —
 * it only redirects to the SPA route that actually renders the entity.
 */
trait CanonicalUrlTrait
{
    public function getCanonicalUrl(): string
    {
        return $this->getService(EntityUrlResolver::class)->absoluteUrlFor($this);
    }
}
