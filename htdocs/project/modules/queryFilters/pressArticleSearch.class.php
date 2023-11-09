<?php

class pressArticleSearchQueryFilter extends searchQueryFilter
{

    protected function getTypeName()
    {
        return 'pressArticle';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'introduction'];
    }

    protected function getContentFieldNames()
    {
        return ['content'];
    }
}