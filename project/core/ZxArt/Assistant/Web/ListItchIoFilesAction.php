<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Web;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Releases\ItchIo\ItchIoDownloads;

/**
 * @psalm-api
 */
final readonly class ListItchIoFilesAction implements ReadAction
{
    public function __construct(
        private ItchIoDownloads $itchIoDownloads,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'list_itch_io_files';
    }

    #[Override]
    public function description(): string
    {
        return 'Lists the files an itch.io game offers for download, also behind a donation prompt: the upload id, '
            . 'the label the creator gave it (often naming the platform, language and version) and whether it is a '
            . 'native PC, Mac or Android build. itch.io pages carry no direct download links; store a file into a '
            . 'release with set_release_itch_io_file.';
    }

    #[Override]
    public function schema(): string
    {
        return ListItchIoFilesArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): ItchIoFilesDto
    {
        $game = ListItchIoFilesArgumentsDto::from($arguments)->game;

        return new ItchIoFilesDto($game->gameUrl, $this->itchIoDownloads->open($game)->uploads);
    }
}
