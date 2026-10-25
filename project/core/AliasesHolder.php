<?php


interface AliasesHolder
{
    public function getAliasElements();

    /**
     * @return array<int|string> as the database returns them
     */
    public function getAliasElementsIds();
}