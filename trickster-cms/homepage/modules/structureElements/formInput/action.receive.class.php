<?php

class receiveFormInput extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param formInputElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $structureElement->fieldType = 'input';
            $structureElement->fieldName = 'field' . $structureElement->id;
            $structureElement->structureName = $structureElement->fieldName;

            $structureElement->persistElementData();
            if ($parentElement = $structureManager->getElementsFirstParent($structureElement->id)) {
                $controller->redirect($parentElement->URL);
            }
        } else {
            $structureElement->executeAction("showForm");
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'required',
            'hidden',
            'validator',
            'autocomplete',
            'placeholder',
        ];
    }

    public function setValidators(&$validators)
    {
        $validators['title'][] = 'notEmpty';
    }
}


