<?php

class receiveLogin extends structureElementAction
{
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated === true) {
            $structureElement->prepareActualData();

            if ($structureElement->title != '') {
                $structureElement->structureName = $structureElement->title;
            }
            $structureElement->persistElementData();

            $structureElement->persistDisplayMenusLinks();

            $controller->redirect($structureElement->URL);
        } else {
            $structureElement->executeAction('showForm');
        }
    }

    public function setValidators(&$validators): void
    {
        $validators['title'][] = 'notEmpty';
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'displayMenus',
            'description',
        ];
    }
}