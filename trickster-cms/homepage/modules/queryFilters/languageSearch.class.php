<?php

class languageSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'language';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'iso6393'];
    }

    protected function getContentFieldNames()
    {
        return false;
    }
}