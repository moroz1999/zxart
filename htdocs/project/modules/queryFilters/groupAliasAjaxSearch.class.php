<?php

class groupAliasAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'groupAlias';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}