<?php

class groupAliasSearchQueryFilter extends searchQueryFilter
{
    public function getTypeName()
    {
        return 'groupAlias';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    protected function getContentFieldNames()
    {
        return [];
    }
}