<?php

declare(strict_types=1);

namespace ZxArt\Import\Prods;

/**
 * What a production import may change in the productions and releases it
 * finds already stored. A new production or release takes everything the
 * source gives; an existing one keeps what it has unless a flag says
 * otherwise. Each import sets the flags its source deserves.
 */
final readonly class ProdImportPolicy
{
    public function __construct(
        public bool $updateExistingProds = false,
        public bool $updateExistingReleases = false,
        public bool $matchProdsWithoutYear = false,
        public bool $forceUpdateTitles = false,
        public bool $forceUpdateYear = false,
        public bool $forceUpdateYoutube = false,
        public bool $forceUpdateExternalLink = false,
        public bool $forceUpdateCategories = false,
        public bool $forceUpdateAuthors = false,
        public bool $forceUpdateGroups = false,
        public bool $forceUpdatePublishers = false,
        public bool $forceUpdateImages = false,
        public bool $addImages = false,
        public bool $resizeImages = false,
        public bool $forceUpdateReleaseType = false,
        public bool $forceUpdateReleaseFiles = false,
        public bool $forceUpdateReleaseAuthors = false,
        public bool $forceUpdateReleasePublishers = false,
    ) {
    }
}
