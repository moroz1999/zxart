<?php

use ZxArt\Screenshots\ListImagePresetResolver;

trait ZxSoft
{
    /**
     * Chooses the list thumbnail treatment from the effective hardware and the
     * source raster size. Native screen files have no raster dimensions here;
     * their converter already renders them at the intended logical size.
     */
    public function getListImagePreset(?fileElement $image = null): string
    {
        $dimensions = $image?->getRasterDimensions();

        return $this->getService(ListImagePresetResolver::class)->resolve(
            $this->getRunsOnHardwareCodes(),
            $dimensions['width'] ?? null,
            $dimensions['height'] ?? null,
        );
    }
}
