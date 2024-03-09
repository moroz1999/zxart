<?php

class groupAliasSearchQueryFilter extends searchQueryFilter
{
    public function getTypeName(): string
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