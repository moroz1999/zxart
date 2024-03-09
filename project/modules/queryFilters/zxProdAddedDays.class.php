<?php

class zxProdAddedDaysQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $days = intval(reset($argument));
        } else {
            $days = intval($argument);
        }

        $date = time() - $days * 24 * 60 * 60;

        $query->whereIn(
            $this->getTable() . '.id',
            function ($query) use ($date) {
                $query->select('structure_elements.id')
                    ->from('structure_elements')
                    ->where('structure_elements.structureType', '=', 'zxProd')
                    ->where('structure_elements.dateCreated', '>=', $date);
            }
        );

        return $query;
    }
}