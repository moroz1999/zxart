<?php

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
    protected function gatherReleaseFiles($items, &$result = []): array
    {
        foreach ($items as $item) {
            if ($extension = $item->getItemExtension()) {
                if (in_array($extension, $this->getReleaseFormats())) {
                    $result[$item->getMd5()] = $extension;
                }
            }
            if ($subItems = $item->getItems()) {
                $this->gatherReleaseFiles($subItems, $result);
            }
        }
        return $result;
    }
}