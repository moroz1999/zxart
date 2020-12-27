<?php

class authorAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'author';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'realName'];
    }
}