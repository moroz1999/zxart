<?php

abstract class dynamicGroupFieldsStructureElement extends dynamicFieldsStructureElement
{
    protected $customFieldsList;
    protected $customFieldsGroups;

    /**
     * Method returns the list of structure elements containing dynamic fields
     * @return array
     */
    public function getCustomFieldsList()
    {
        if (is_null($this->customFieldsList)) {
            $this->customFieldsList = [];
            if ($groups = $this->getCustomFieldsGroups()) {
                foreach ($groups as &$group) {
                    if ($fields = $group->getFormFields()) {
                        foreach ($fields as &$field) {
                            $this->customFieldsList[] = $field;
                        }
                    }
                }
            }
        }
        return $this->customFieldsList;
    }

    /**
     * Method returns the list of field groups elements containing dynamic fields
     * @return mixed
     */
    public function getCustomFieldsGroups()
    {
        if (is_null($this->customFieldsGroups)) {
            $this->customFieldsGroups = [];

            $structureManager = $this->getService('structureManager');
            if ($groups = $structureManager->getElementsChildren($this->id)) {
                foreach ($groups as &$group) {
                    if ($fields = $group->getFormFields()) {
                        $this->customFieldsGroups[] = $group;
                    }
                }
            }
        }
        return $this->customFieldsGroups;
    }

    abstract public function getInheritedCustomFieldsList();
}
