<?php

class tagSearchQueryFilter extends searchQueryFilter
{
    public function getTypeName()
    {
        return 'tag';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'synonym'];
    }

    protected function getContentFieldNames()
    {
        return false;
    }
}