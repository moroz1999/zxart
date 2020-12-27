<?php

class authorSearchQueryFilter extends searchQueryFilter
{
    public function getTypeName()
    {
        return 'author';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'realName'];
    }

    protected function getContentFieldNames()
    {
    }
}