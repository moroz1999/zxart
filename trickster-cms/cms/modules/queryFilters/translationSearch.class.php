<?php

class translationSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'translation';
    }

    protected function getTitleFieldNames()
    {
        return ['valueText', 'valueTextarea', 'valueHtml'];
    }

    public function getFilteredIdList($argument, $query)
    {
        $query = parent::getFilteredIdList($argument, $query);
        $query->orWhereIn('id', function ($subQuery) use ($argument) {
            $subQuery
                ->select('id')
                ->from('structure_elements')
                ->where('structureName', 'like', '%%' . $argument . '%%')
                ->whereIn('structureType', ["translation", "adminTranslation"]);
        });
        return $query;
    }

    protected function getContentFieldNames()
    {
        return false;
    }
}