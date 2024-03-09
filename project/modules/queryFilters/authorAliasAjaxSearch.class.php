<?php

class authorAliasAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'authorAlias';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}