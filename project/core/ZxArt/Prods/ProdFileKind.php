<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use Override;
use ZxArt\Shared\Elements\AttachedFileKind;
use ZxArt\Shared\Elements\AttachedFileTypes;

/**
 * The file lists of a production.
 */
enum ProdFileKind: string implements AttachedFileKind
{
    case Screenshots = 'screenshots';
    case Inlays = 'inlays';
    case Maps = 'maps';
    case Rzx = 'rzx';

    #[Override]
    public function propertyName(): string
    {
        return match ($this) {
            self::Screenshots => 'connectedFile',
            self::Inlays => 'inlayFilesSelector',
            self::Maps => 'mapFilesSelector',
            self::Rzx => 'rzx',
        };
    }

    #[Override]
    public function maxBytes(): int
    {
        return $this === self::Rzx ? AttachedFileTypes::MAX_DOCUMENT_BYTES : AttachedFileTypes::MAX_IMAGE_BYTES;
    }

    /**
     * A recording is of any type. Screenshots also take raw machine screens,
     * whose formats have no dedicated media type and are detected as binary.
     * The other lists take browser images.
     */
    #[Override]
    public function accepts(string $contentType): bool
    {
        return $this === self::Rzx
            || in_array($contentType, AttachedFileTypes::IMAGES, true)
            || ($this === self::Screenshots && $contentType === AttachedFileTypes::BINARY);
    }
}
