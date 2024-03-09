<?php

class tagAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'tag';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'synonym'];
    }
}