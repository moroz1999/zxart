<?php

class mapAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'map';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}