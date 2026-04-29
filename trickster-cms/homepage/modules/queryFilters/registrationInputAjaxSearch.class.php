<?php

class registrationInputAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'registrationInput';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}