<?php

class structureDateModifiedQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'structure';
    }

    public function getFilteredIdList($argument, $query)
    {
        $argument = (array)$argument;
        if (isset($argument[0])) {
            $start = $argument[0];
        } else {
            $start = false;
        }
        if (isset($argument[1])) {
            $end = $argument[1];
        } else {
            $end = false;
        }
        if ($start) {
            $query->where('dateModified', '>=', $start);
        }
        if ($end) {
            $query->where('dateModified', '<=', $end);
        }

        return $query;
    }
}