<?php
declare(strict_types=1);

namespace ZxArt\Releases\Services;

use ZxArt\FileParsing\ZxParsingItem;

final readonly class ReleaseFileTypesGatherer
{
    public function __construct(
        private ReleaseFormatsProvider $releaseFormatsProvider
    )
    {

    }

    /**
     * The files of a release that are releases in their own right: the top-level
     * file itself, and whatever a folder or an archive holds beside it.
     *
     * A disk or a tape is not opened. Its catalogue names are that image's
     * contents, not files that were published — a TR-DOS entry ending in `.o` or
     * a +D name reading as `.mgt` says nothing about the format the release came
     * in, and taking it for one is how a plain disk ends up filed under three.
     *
     * @param ZxParsingItem[] $items
     * @param ZxParsingItem[] $result
     *
     * @return ZxParsingItem[]
     */
    public function gatherReleaseFiles(array $items, array &$result = []): array
    {
        foreach ($items as $item) {
            $extension = $item->getItemExtension();
            if ($extension && in_array($extension, $this->releaseFormatsProvider->getReleaseFormats(), true)) {
                $md5 = $item->getMd5();
                if ($md5 !== null) {
                    $result[$md5] = $item;
                }
            }

            if ($item->holdsSeparateFiles() && ($subItems = $item->getItems())) {
                $this->gatherReleaseFiles($subItems, $result);
            }
        }
        return $result;
    }
}