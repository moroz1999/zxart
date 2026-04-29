<?php

class languageAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'language';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'iso6393'];
    }
}