<?php

class zxMusicMinPartyPlaceQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $argument = reset($argument);
        }
        $query->where('partyplace', '<=', $argument);
        $query->where('partyplace', '!=', 0);

        return $query;
    }
}