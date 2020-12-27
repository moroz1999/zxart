<?php

class partyOfItemTypeQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'party';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $query->whereIn(
                'id',
                function ($query) use ($argument) {
                    $query->select('parentStructureId')
                        ->from('structure_links')
                        ->whereIn('type', $argument);
                }
            );
        } else {
            $query->whereIn(
                'id',
                function ($query) use ($argument) {
                    $query->select('parentStructureId')
                        ->from('structure_links')
                        ->where('type', '=', $argument);
                }
            );
        }

        return $query;
    }
}