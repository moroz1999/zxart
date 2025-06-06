<?php

class zxReleaseLanguageQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxRelease';
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
                        ->select('childStructureId')
                        ->where('type', 'structure')
                        ->whereIn('parentStructureId', static function ($prodSubQuery) use ($argument) {
                            $prodSubQuery->from('zxitem_language')
                                ->select('elementId')
                                ->whereIn('value', $argument);
                        })
                        ->whereNotIn('childStructureId', static function ($releaseSubQuery) {
                            $releaseSubQuery->from('zxitem_language')
                                ->select('elementId');
                        });
                });
        });

        return $query;
    }
}
