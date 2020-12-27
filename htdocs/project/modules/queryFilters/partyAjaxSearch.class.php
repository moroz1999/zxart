<?php

class partyAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'party';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}