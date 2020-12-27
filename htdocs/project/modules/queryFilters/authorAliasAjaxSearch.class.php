<?php

class authorAliasAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'authorAlias';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}