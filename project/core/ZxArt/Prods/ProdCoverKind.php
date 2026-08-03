<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use ZxArt\LinkTypes;

enum ProdCoverKind: string
{
    case Inlay = 'inlay';
    case Ad = 'ad';

    public function linkType(): LinkTypes
    {
        return match ($this) {
            self::Inlay => LinkTypes::INLAY_FILES_SELECTOR,
            self::Ad => LinkTypes::AD_FILES_SELECTOR,
        };
    }
}
