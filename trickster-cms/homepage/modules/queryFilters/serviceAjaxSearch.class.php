<?php

class serviceAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'service';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}