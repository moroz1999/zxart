<?php

class authorSearchQueryFilter extends searchQueryFilter
{
    public function getTypeName(): string
    {
        return 'author';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'realName'];
    }

    /**
     * @return void
     */
    protected function getContentFieldNames()
    {
    }
}