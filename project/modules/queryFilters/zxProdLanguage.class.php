<?php

class zxProdLanguageQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->where(function ($q) use ($argument) {
            $q->whereIn($this->getTable() . '.id', static function ($subQuery) use ($argument) {
                $subQuery->from('zxitem_language')
                    ->select('elementId')
                    ->whereIn('value', $argument);
            })
                ->orWhereIn($this->getTable() . '.id', static function ($subQuery) use ($argument) {
                    $subQuery->from('structure_links')
                        ->select('parentStructureId')
                        ->where('type', 'structure')
                        ->whereIn('childStructureId', static function ($releaseSubQuery) use ($argument) {
                            $releaseSubQuery->from('zxitem_language')
                                ->select('elementId')
                                ->whereIn('value', $argument);
                        });
                });
        });

        return $query;
    }
}
