<?php

class zxPicturePartyPlaceQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (!is_array($argument)) {
            $argument = (array)$argument;
        }
        $query->whereIn('partyplace', $argument);
        return $query;
    }
}