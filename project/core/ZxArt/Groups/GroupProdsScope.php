<?php

declare(strict_types=1);

namespace ZxArt\Groups;

use LogicException;
use ZxArt\LinkTypes;

enum GroupProdsScope: string
{
    /** Everything the group is credited on, in either role. */
    case All = 'all';
    case Own = 'own';
    case Published = 'published';
    case Releases = 'releases';

    /**
     * The single link type a scope selects by. Merged scopes read several link
     * types and assemble their rows in PHP instead.
     */
    public function linkType(): LinkTypes
    {
        return match ($this) {
            self::Own => LinkTypes::ZX_PROD_GROUPS,
            self::Published => LinkTypes::ZX_PROD_PUBLISHERS,
            self::Releases => LinkTypes::ZX_RELEASE_PUBLISHERS,
            self::All => throw new LogicException('The "all" scope spans several link types'),
        };
    }

    public function isReleases(): bool
    {
        return $this === self::Releases;
    }

    /** Whether the scope mixes prods and releases and is assembled in PHP. */
    public function isMerged(): bool
    {
        return $this === self::All || $this === self::Published;
    }
}
