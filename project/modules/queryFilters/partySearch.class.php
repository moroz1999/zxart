<?php

class partySearchQueryFilter extends searchQueryFilter
{
    public function getTypeName(): string
    {
        return 'party';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    /**
     * @return false
     */
    protected function getContentFieldNames(): bool
    {
        return false;
    }
}