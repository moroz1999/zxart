<?php

class zxMusicTitleSearchQueryFilter extends QueryFilter
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
        $query->where(
            function ($query) use ($argument) {
                $query->where('title', 'like', '%' . $argument . '%');
                $query->orWhere('internalTitle', 'like', '%' . $argument . '%');
            }
        );

        return $query;
    }
}