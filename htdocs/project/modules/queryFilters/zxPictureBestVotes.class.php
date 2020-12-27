<?php

class zxPictureBestVotesQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $argument = reset($argument);
        }

        $query->limit($argument)->orderBy('votes', 'desc');
        return $query;
    }
}