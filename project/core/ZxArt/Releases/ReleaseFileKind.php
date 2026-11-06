<?php

declare(strict_types=1);

namespace ZxArt\Releases;

use Override;
use ZxArt\Shared\Elements\AttachedFileKind;
use ZxArt\Shared\Elements\AttachedFileTypes;

/**
 * The file lists of a release, besides its own file.
 */
enum ReleaseFileKind: string implements AttachedFileKind
{
    case Screenshots = 'screenshots';
    case Inlays = 'inlays';
    case Info = 'info';
    case Ads = 'ads';

    #[Override]
    public function propertyName(): string
    {
        return match ($this) {
            self::Screenshots => 'screenshotsSelector',
            self::Inlays => 'inlayFilesSelector',
            self::Info => 'infoFilesSelector',
            self::Ads => 'adFilesSelector',
        };
    }

    #[Override]
    public function maxBytes(): int
    {
        return $this === self::Info ? AttachedFileTypes::MAX_DOCUMENT_BYTES : AttachedFileTypes::MAX_IMAGE_BYTES;
    }

    /**
     * Instructions may also be a PDF or plain text. Screenshots also take raw
     * machine screens, whose formats have no dedicated media type and are
     * detected as binary. The other lists take browser images.
     */
    #[Override]
    public function accepts(string $contentType): bool
    {
        return in_array($contentType, $this === self::Info ? AttachedFileTypes::DOCUMENTS : AttachedFileTypes::IMAGES, true)
            || ($this === self::Screenshots && $contentType === AttachedFileTypes::BINARY);
    }
}
