<?php

/**
 * Interface for structure elements supporting dynamic fields structures in forms
 */
interface dynamicFieldsElementInterface
{
    /**
     * Method returns the list of field names which are expected to come with Request
     * @abstract
     * @return array
     */
    public function getCustomExpectedFields();

    /**
     * Method returns the index of dynamic field types ("fieldName" => "dataChunkType")
     * @abstract
     * @return array
     */
    public function getCustomModuleFields();

    /**
     * Method returns the index of dynamic field validators ("fieldName" => "validatorType")
     * @abstract
     * @return array
     */
    public function getCustomValidators();
}

