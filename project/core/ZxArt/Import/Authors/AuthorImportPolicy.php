<?php

declare(strict_types=1);

namespace ZxArt\Import\Authors;

/**
 * What an author import may overwrite: without a flag it only fills what is
 * still empty.
 *
 * @psalm-api
 */
final readonly class AuthorImportPolicy
{
    public function __construct(
        public bool $forceUpdateRealName = false,
        public bool $forceUpdateCountry = false,
        public bool $forceUpdateCity = false,
        public bool $forceUpdateGroups = false,
    ) {
    }
}
