<?php

class tagSearchQueryFilter extends searchQueryFilter
{
    public function getTypeName(): string
    {
        return 'tag';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'synonym'];
    }

    /**
     * @return false
     */
    protected function getContentFieldNames(): bool
    {
        return false;
    }
}