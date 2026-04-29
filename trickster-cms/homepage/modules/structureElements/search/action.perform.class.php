<?php

class performSearch extends structureElementAction
{
    /**
     * @param searchElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->executeAction('show');
        if ($this->validated) {
            if (!$structureElement->phrase) {
                $structureElement->phrase = $controller->getParameter('phrase');
            }
            $structureElement->phrase = trim($structureElement->phrase);
            $structureElement->phrase = str_replace('%s%', '/', $structureElement->phrase);
        }
        $structureElement->setViewName('result');
    }

    public function setValidators(&$validators)
    {
        $validators['phrase'][] = 'notEmpty';
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = ['phrase'];
    }
}