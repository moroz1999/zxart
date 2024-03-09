<?php

class zxProdFirstLetterQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $query->where($this->getTable() . '.title', 'like', reset($argument) . '%');
        } else {
            if (preg_match('/[a-zA-Z]/', $argument)) {
                $query->where($this->getTable() . '.title', 'like', $argument . '%');
            } else {
                $query->whereRaw('engine_' . $this->getTable() . ".title NOT RLIKE '^[A-Z]'");
            }
        }
        return $query;
    }
}