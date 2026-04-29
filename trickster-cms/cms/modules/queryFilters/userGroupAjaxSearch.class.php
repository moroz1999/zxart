<?php

class userGroupAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'userGroup';
    }

    protected function getTitleFieldNames()
    {
        return ['groupName'];
    }
}