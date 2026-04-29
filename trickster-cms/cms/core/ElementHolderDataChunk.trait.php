<?php

/**
 * Trait ElementHolderDataChunkTrait
 *
 */
trait ElementHolderDataChunkTrait
{
    /**
     * @var structureElement
     */
    protected $structureElement;

    public function setStructureElement($structureElement)
    {
        $this->structureElement = $structureElement;
    }
}