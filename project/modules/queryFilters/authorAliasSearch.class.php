<?php

class authorAliasSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'authorAlias';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    /**
     * @return false
     */
    protected function getContentFieldNames(): bool
    {
        return false;
    }
}