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

            if ($subItems = $item->getItems()) {
                $this->gatherReleaseFiles($subItems, $result);
            }
        }
        return $result;
    }
}