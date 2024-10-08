<?php

class zxProdSearchQueryFilter extends searchQueryFilter
{

    protected function getTypeName()
    {
        return 'zxProd';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'altTitle'];
    }

    protected function getContentFieldNames()
    {
        return ['description'];
    }

    public function getFilteredIdList($argument, $query)
    {
        $query = parent::getFilteredIdList($argument, $query);
        $query->whereNotIn(
            $this->getTable() . '.id',
            function ($subQuery) {
                $subQuery->from('structure_links')->select('structure_links.childStructureId')->where('structure_links.parentStructureId', '=', 92171);
            }
        )->orderBy('title', 'asc');

        return $query;
    }
}