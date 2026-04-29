<?php

trait LinkedQueryFilterTrait
{
    protected string $links = 'structure_links';

    /**
     * @param \Illuminate\Database\Query\Builder|array $sourceQuery
     * @param string $table
     * @param string $linkType
     * @param bool $distinct
     * @return \Illuminate\Database\Query\Builder
     */
    protected function generateParentQuery($sourceQuery, $table, $linkType, $distinct = false)
    {
        $sourceQuery->select('id');
        $correctionQuery = $this->getCorrectionQuery();
        $query = $this->getService('db')
            ->table($table)
            ->select($this->getFields())
            ->whereIn(
                $this->getTable() . '.id',
                function ($subQuery) use (
                    $sourceQuery,
                    $linkType,
                    $correctionQuery
                ) {
                    if ($sourceQuery instanceof \Illuminate\Database\Query\Builder) {
                        $subQuery->select($this->links . '.parentStructureId')
                            ->from($this->links)
                            ->where($this->links . '.type', '=', $linkType)
                            ->whereRaw('childStructureId in (' . $sourceQuery->toSql() . ')')
                            ->mergeBindings($sourceQuery);
                    } elseif (is_array($sourceQuery)) {
                        $subQuery->select($this->links . '.parentStructureId')
                            ->from($this->links)
                            ->where($this->links . '.type', '=', $linkType)
                            ->whereIn($this->links . '.childStructureId', $sourceQuery);
                    }
                    if ($correctionQuery instanceof \Illuminate\Database\Query\Builder) {
                        $subQuery->whereRaw('parentStructureId in (' . $correctionQuery->toSql() . ')')
                            ->mergeBindings($correctionQuery);
                    } elseif (is_array($correctionQuery)) {
                        $subQuery->whereIn($this->links . '.parentStructureId', $correctionQuery);
                    }
                }
            );
        if ($distinct) {
            $query->distinct();
        }
        return $query;
    }

    /**
     * @param \Illuminate\Database\Query\Builder|array $sourceQuery
     * @param string $table
     * @param string $linkType
     * @param bool $distinct
     * @return \Illuminate\Database\Query\Builder
     */
    protected function generateChildQuery($sourceQuery, $table, $linkType, $distinct = false)
    {
        $sourceQuery->select('id');

        $correctionQuery = $this->getCorrectionQuery();
        $query = $this->getService('db')
            ->table($table)
            ->select($this->getFields())
            ->whereIn(
                $this->getTable() . '.id',
                function ($subQuery) use (
                    $sourceQuery,
                    $linkType,
                    $correctionQuery
                ) {
                    if ($sourceQuery instanceof \Illuminate\Database\Query\Builder) {
                        $subQuery->select($this->links . '.childStructureId')
                            ->from($this->links)
                            ->where($this->links . '.type', '=', $linkType)
                            ->whereRaw('parentStructureId in (' . $sourceQuery->toSql() . ')')
                            ->mergeBindings($sourceQuery);
                    } elseif (is_array($sourceQuery)) {
                        $subQuery->select($this->links . '.childStructureId')
                            ->from($this->links)
                            ->where($this->links . '.type', '=', $linkType)
                            ->whereIn($this->links . '.parentStructureId', $sourceQuery);
                    }
                    if ($correctionQuery instanceof \Illuminate\Database\Query\Builder) {
                        $subQuery->whereRaw('childStructureId in (' . $correctionQuery->toSql() . ')')
                            ->mergeBindings($correctionQuery);
                    } elseif (is_array($correctionQuery)) {
                        $subQuery->whereIn($this->links . '.childStructureId', $correctionQuery);
                    }
                }
            );
        if ($distinct) {
            $query->distinct();
        }
        return $query;
    }


    abstract function getCorrectionQuery();
}