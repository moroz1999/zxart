<?php

class authorAliasIdQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'authorAlias';
    }

    public function getFilteredIdList($argument, $query)
    {
        $authorAliases = (array)$argument;
        $query->whereIn($this->getTable() . '.id', $authorAliases);

        return $query;
    }
}