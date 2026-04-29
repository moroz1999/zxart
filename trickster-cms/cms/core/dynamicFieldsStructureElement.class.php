<?php

abstract class dynamicFieldsStructureElement extends menuStructureElement implements dynamicFieldsElementInterface
{
    protected $customFieldsList;

    abstract public function getCustomFieldsList();

    /**
     * @return array
     */
    public function getCustomExpectedFields()
    {
        $expectedFields = [];
        if ($fields = $this->getCustomFieldsList()) {
            foreach ($fields as &$fieldInfo) {
                $expectedFields[] = $fieldInfo->fieldName;
            }
        }
        return $expectedFields;
    }

    /**
     * @return array
     */
    public function getCustomModuleFields()
    {
        $customFields = [];
        if ($fields = $this->getCustomFieldsList()) {
            foreach ($fields as &$fieldInfo) {
                if (method_exists($fieldInfo, 'getDataChunkType')) {
                    $customFields[$fieldInfo->fieldName] = $fieldInfo->getDataChunkType();
                } else {
                    $customFields[$fieldInfo->fieldName] = $fieldInfo->dataChunk;
                }
            }
        }
        return $customFields;
    }

    /**
     * @return array
     */
    public function getCustomValidators()
    {
        $validators = [];
        if ($fields = $this->getCustomFieldsList()) {
            foreach ($fields as &$fieldInfo) {
                if ($fieldInfo->required) {
                    $validators[$fieldInfo->fieldName][] = 'notEmpty';
                }
                if ($fieldInfo->validator != '') {
                    $validators[$fieldInfo->fieldName][] = $fieldInfo->validator;
                }
            }
        }
        return $validators;
    }

    /**
     * This function automatically fills custom fields with data according to auto-completion values
     * @return array
     */
    public function getFormData()
    {
        $formData = parent::getFormData();
        if ($fields = $this->getCustomFieldsList()) {
            foreach ($fields as &$field) {
                if (array_key_exists($field->fieldName, $formData) && $formData[$field->fieldName] === null) {
                    $this->setFormValue($field->fieldName, $field->getAutoCompleteValue());
                }
            }
        }
        return parent::getFormData();
    }
}

