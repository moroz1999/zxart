<?php

class groupAliasAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'groupAlias';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}