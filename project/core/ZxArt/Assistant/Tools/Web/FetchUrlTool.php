<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Web;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Shared\Http\FetchedPageDto;
use ZxArt\Shared\Http\PageFetcher;

/**
 * @implements ReadTool<FetchUrlRequestDto>
 *
 * @psalm-api
 */
final readonly class FetchUrlTool implements ReadTool
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
    public function requestClass(): string
    {
        return FetchUrlRequestDto::class;
    }

    #[Override]
    public function execute(object $request): FetchedPageDto
    {
        return $this->pageFetcher->fetch($request->url);
    }
}
