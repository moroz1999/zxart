<?php

class authorAliasFirstLetterQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'authorAlias';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $query->where($this->getTable() . '.title', 'like', reset($argument) . '%');
        } else {
            $query->where($this->getTable() . '.title', 'like', $argument . '%');
        }
        return $query;
    }
}