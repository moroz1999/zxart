<?php

trait AutoMarkerTrait
{
    public function persistStructureData()
    {
        if (!$this->marker) {
            $this->marker = $this->structureType;
        }
        parent::persistStructureData();
    }
}