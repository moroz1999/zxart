<?php

class partyAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'party';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}