<?php

class productionAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'production';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}