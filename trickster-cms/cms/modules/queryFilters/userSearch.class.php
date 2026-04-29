<?php

class userSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'user';
    }

    protected function getTitleFieldNames()
    {
        return ['userName'];
    }

    protected function getContentFieldNames()
    {
        return false;
    }
}