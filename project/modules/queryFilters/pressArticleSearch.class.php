<?php

use Illuminate\Database\Query\Builder;
use ZxArt\Search\ExtraSearchFiltersInterface;

class pressArticleSearchQueryFilter extends searchQueryFilter implements ExtraSearchFiltersInterface
{
    public function assignExtraFilters(Builder $query): Builder
    {
        $languagesManager = $this->getService(LanguagesManager::class);
        $query->where('languageId', '=', $languagesManager->getCurrentLanguageId());
        return $query;
    }

    protected function getTypeName(): string
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