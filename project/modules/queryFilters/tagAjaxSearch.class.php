<?php

class tagAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'tag';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'synonym'];
    }
}