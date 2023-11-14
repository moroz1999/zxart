<?php

class pressArticleAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'pressArticle';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'introduction'];
    }
}