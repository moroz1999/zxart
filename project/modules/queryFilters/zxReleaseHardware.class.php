<?php

use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Shared\DatabaseTable;

/**
 * Releases carrying the given hardware codes.
 *
 * The argument is a list of codes — that is the public contract of the
 * `zxReleaseHardware` API filter — while the link table stores catalog ids, so
 * the codes are resolved once through the cached catalog before the subquery.
 */
class zxReleaseHardwareQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxRelease';
    }

    public function getFilteredIdList($argument, $query)
    {
        $hardwareIds = $this->getService(HardwareCatalogService::class)->getIdsByCodes(array_values((array)$argument));
        $tableName = DatabaseTable::ZxReleaseHardware->value;

        $query->whereIn($this->getTable() . '.id', function ($subQuery) use ($hardwareIds, $tableName) {
            $subQuery->from($tableName)->select('elementId')->whereIn('hardwareId', $hardwareIds);
        });
        return $query;
    }
}
