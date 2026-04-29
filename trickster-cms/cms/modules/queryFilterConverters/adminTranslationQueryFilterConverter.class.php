<?php

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

class adminTranslationQueryFilterConverter extends QueryFilterConverter
{
    public function convert($sourceData, $sourceType)
    {
        /**
         * @var Connection $db ;
         */
        $db = $this->getService('db');
        $query = $db
            ->table($this->getTable())
            ->select('id')
            // remove doubles from translations search results in variable langs
            ->distinct()
            ->whereIn('id', function($query){
                /**
                 * @var Builder $query
                 */
                $query
                    ->from('structure_elements')
                    ->where('structureType', '=', 'adminTranslation')
                    ->select('id');
            })
        ;
        return $query;
    }

    protected function getTable(): string
    {
        return 'module_translation';
    }
}