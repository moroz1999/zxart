<?php

trait QueryDebugger
{
    /**
     * @return never
     */
    protected function debugQuery(\Illuminate\Database\Query\Builder $query){
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        while (($position = strpos($sql, '?')) !== false) {
            $sql = substr_replace($sql, "'" . array_shift($bindings) . "'", $position, 1);
        }
        echo "<br>\n" .$sql."\n<br>";
        exit;
    }
}