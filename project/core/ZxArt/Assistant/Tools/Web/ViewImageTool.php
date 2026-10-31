<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Web;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Assistant\Tools\ToolArgumentsException;
use ZxArt\Shared\Images\ImageDto;
use ZxArt\Shared\Images\RemoteImageReader;
use ZxArt\Shared\Images\StoredImageReader;

/**
 * @implements ReadTool<ViewImageRequestDto>
 *
 * @psalm-api
 */
final readonly class ViewImageTool implements ReadTool
{
    public function __construct(
        private RemoteImageReader $remoteImageReader,
        private StoredImageReader $storedImageReader,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'view_image';
    }

    #[Override]
    public function description(): string
    {
        return 'Shows an image: one from the web by url, or a file of the site by fileId. PNG, JPEG, GIF and WebP '
            . 'files; a ZX Spectrum screen file of the site (scr, img, mlt and the like) comes rendered as the site '
            . 'shows it.';
    }

    #[Override]
    public function requestClass(): string
    {
        return ViewImageRequestDto::class;
    }

    #[Override]
    public function execute(object $request): ImageDto
    {
        if ($request->url !== null && $request->fileId === null) {
            return $this->remoteImageReader->read($request->url);
        }
        if ($request->fileId !== null && $request->url === null) {
            return $this->storedImageReader->read($request->fileId);
        }

        throw new ToolArgumentsException('Give either url or fileId');
    }
}
