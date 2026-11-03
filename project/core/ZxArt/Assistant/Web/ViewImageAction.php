<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Web;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Shared\Images\ImageDto;
use ZxArt\Shared\Images\RemoteImageReader;
use ZxArt\Shared\Images\StoredImageReader;

/**
 * @psalm-api
 */
final readonly class ViewImageAction implements ReadAction
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
    public function schema(): string
    {
        return ViewImageArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): ImageDto
    {
        $request = ViewImageArgumentsDto::from($arguments);
        if ($request->url !== null && $request->fileId === null) {
            return $this->remoteImageReader->read($request->url);
        }
        if ($request->fileId !== null && $request->url === null) {
            return $this->storedImageReader->read($request->fileId);
        }

        throw new ToolArgumentsException('Give either url or fileId');
    }
}
