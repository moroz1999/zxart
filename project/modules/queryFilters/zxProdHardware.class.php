<?php

use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Prods\Repositories\ProdHardwareRepository;

/**
 * Productions running on the given hardware — matching the production's own
 * codes **or** any of its releases'.
 *
 * Hardware lives on both sides, so filtering only one of them would hide half
 * the catalogue. The argument is a list of codes (that is the URL and public API
 * contract); they are resolved to catalog ids once through the cached catalog so
 * the query itself compares integers against an indexed column.
 */
class zxProdHardwareQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        $hardwareIds = $this->getService(HardwareCatalogService::class)->getIdsByCodes(array_values((array)$argument));

        return $this->getService(ProdHardwareRepository::class)
            ->addProdHardwareFilter($query, $this->getTable() . '.id', $hardwareIds);
    }
}
