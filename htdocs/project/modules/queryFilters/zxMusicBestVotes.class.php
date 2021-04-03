<?php

class zxMusicBestVotesQueryFilter extends QueryFilter
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

        $query->limit($argument)->orderBy($this->getTable() . '.votes', 'desc');
        return $query;
    }
}