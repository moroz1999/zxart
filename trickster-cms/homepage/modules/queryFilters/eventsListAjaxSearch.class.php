<?php

class eventsListAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'eventsList';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}