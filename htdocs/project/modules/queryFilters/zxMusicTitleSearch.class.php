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
        $table = $this->getTable();
        $query->where(
            function ($query) use ($argument, $table) {
                $query->where($table . '.title', 'like', '%' . $argument . '%');
                $query->orWhere($table . '.internalTitle', 'like', '%' . $argument . '%');
            }
        );

        return $query;
    }
}