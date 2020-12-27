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
        $query->whereIn('id', $authorAliases);

        return $query;
    }
}