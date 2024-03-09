<?php

class pressArticleAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'pressArticle';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'introduction'];
    }
}