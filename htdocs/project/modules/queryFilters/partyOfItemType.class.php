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
                    $query->select('structure_links.parentStructureId')
                        ->from('structure_links')
                        ->whereIn('structure_links.type', $argument);
                }
            );
        } else {
            $query->whereIn(
                'id',
                function ($query) use ($argument) {
                    $query->select('structure_links.parentStructureId')
                        ->from('structure_links')
                        ->where('structure_links.type', '=', $argument);
                }
            );
        }

        return $query;
    }
}