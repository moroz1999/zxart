<?php

class receiveFormTextArea extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param formTextAreaElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $structureElement->fieldType = 'textarea';
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


