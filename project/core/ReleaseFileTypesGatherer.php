<?php

use ZxArt\FileParsing\ZxParsingItem;

trait ReleaseFileTypesGatherer
{
    /**
     * @param ZxParsingItem[] $items
     * @param ZxParsingItem[] $result
     *
     * @return (ZxParsingItem|string)[]
     *
     * @psalm-return array<ZxParsingItem|string>
     */
    protected function gatherReleaseFiles(array $items, array &$result = []): array
    {
        foreach ($items as $item) {
            $extension = $item->getItemExtension();
            if ($extension && in_array($extension, $this->getReleaseFormats(), true)) {
                $result[$item->getMd5()] = $extension;
            }

            if ($subItems = $item->getItems()) {
                $this->gatherReleaseFiles($subItems, $result);
            }
        }
        return $result;
    }
}