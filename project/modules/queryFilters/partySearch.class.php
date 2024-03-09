<?php

class partySearchQueryFilter extends searchQueryFilter
{
    public function getTypeName()
    {
        return 'party';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    protected function getContentFieldNames()
    {
        return false;
    }
}