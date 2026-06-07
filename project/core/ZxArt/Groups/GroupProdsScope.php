<?php

declare(strict_types=1);

namespace ZxArt\Groups;

use ZxArt\LinkTypes;

enum GroupProdsScope: string
{
    case Own = 'own';
    case Published = 'published';
    case Releases = 'releases';

    public function linkType(): LinkTypes
    {
        return match ($this) {
            self::Own => LinkTypes::ZX_PROD_GROUPS,
            self::Published => LinkTypes::ZX_PROD_PUBLISHERS,
            self::Releases => LinkTypes::ZX_RELEASE_PUBLISHERS,
        };
    }

    public function isReleases(): bool
    {
        return $this === self::Releases;
    }
}
