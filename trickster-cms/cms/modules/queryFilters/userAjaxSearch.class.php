<?php

class userAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'user';
    }

    protected function getTitleFieldNames()
    {
        return ['userName'];
    }
}