<?php

class groupSearchQueryFilter extends searchQueryFilter
{
    public function getTypeName(): string
    {
        return 'group';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    protected function getContentFieldNames()
    {
        return [];
    }
}