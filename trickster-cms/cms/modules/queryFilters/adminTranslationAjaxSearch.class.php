<?php

class adminTranslationAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    public function getTypeName()
    {
        return 'adminTranslation';
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
                ->where('structureType', '=', "adminTranslation");
        });
        return $query;
    }
}