<?php

use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Prods\Repositories\ProdHardwareRepository;

/**
 * Releases running on the given hardware — their own codes **or** the ones they
 * inherit from their production.
 *
 * Used by the catalogue in release mode. Distinct from `zxReleaseHardware`,
 * which is the documented public API filter and keeps its literal meaning
 * ("releases carrying this code themselves"); a release whose codes moved to its
 * production would drop out of the catalogue with that one.
 */
class zxReleaseEffectiveHardwareQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxRelease';
    }

    public function getFilteredIdList($argument, $query)
    {
        $hardwareIds = $this->getService(HardwareCatalogService::class)->getIdsByCodes(array_values((array)$argument));

        return $this->getService(ProdHardwareRepository::class)
            ->addReleaseHardwareFilter($query, $this->getTable() . '.id', $hardwareIds);
    }
}
