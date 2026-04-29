<?php

class receiveAdminTranslation extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param adminTranslationElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $valueFields = ['valueText', 'valueTextarea', 'valueHtml'];
            $selectedValueField = 'value' . ucfirst($structureElement->valueType);
            foreach ($valueFields as &$fieldName) {
                if ($fieldName != $selectedValueField) {
                    $structureElement->$fieldName = '';
                }
            }
            $structureElement->persistElementData();
            $translationsManager = $this->getService(translationsManager::class);
            $translationsManager->generateTranslationsFile('adminTranslations');

            if ($parentElement = $structureManager->getElementsFirstParent($structureElement->id)) {
                $controller->redirect($parentElement->URL);
            }
        }
        $structureElement->executeAction("showForm");
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'structureName',
            'valueType',
            'valueText',
            'valueTextarea',
            'valueHtml',
        ];
    }

    public function setValidators(&$validators)
    {
        $validators['structureName'][] = 'notEmpty';
    }
}