<?php

class authorAliasSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'authorAlias';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    protected function getContentFieldNames()
    {
        return false;
    }
}