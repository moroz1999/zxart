<?php

class authorAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'author';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'realName'];
    }
}