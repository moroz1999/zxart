<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Web;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Shared\Http\FetchedPageDto;
use ZxArt\Shared\Http\PageFetcher;

/**
 * @psalm-api
 */
final readonly class FetchUrlAction implements ReadAction
{
    public function __construct(
        private PageFetcher $pageFetcher,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'fetch_url';
    }

    #[Override]
    public function description(): string
    {
        return 'Fetches a page from the web with a plain GET, no JavaScript run: the final URL after redirects, '
            . 'the status, the content type and the body as it came, in UTF-8. A body past '
            . PageFetcher::MAX_BYTES . ' bytes is cut and marked truncated; a binary response gives only its '
            . 'type and size. Look at images with view_image.';
    }

    #[Override]
    public function schema(): string
    {
        return FetchUrlArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): FetchedPageDto
    {
        $request = FetchUrlArgumentsDto::from($arguments);
        return $this->pageFetcher->fetch($request->url);
    }
}
