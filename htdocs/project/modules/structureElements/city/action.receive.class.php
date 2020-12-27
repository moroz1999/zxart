<?php

class receiveCity extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $structureElement->structureName = $structureElement->title;

            $structureElement->persistElementData();
            $this->joinCities($structureElement);

            $controller->restart($structureElement->URL);
        }
        $structureElement->setViewName('form');
    }

    protected function joinCities($structureElement)
    {
        $structureManager = $this->getService('structureManager');
        if ($structureElement->joinCity == $structureElement->id) {
            return false;
        }
        if ($joinedCityId = $structureElement->joinCity) {
            /**
             * @var cityElement $joinedCity
             */
            if ($joinedCity = $structureManager->getElementById($joinedCityId)) {
                /**
                 * @var \Illuminate\Database\Connection $db
                 */
                $db = $this->getService('db');
                $db->table('module_author')->where('city', '=', $joinedCityId)->update(
                    ['city' => $structureElement->id]
                );
                $db->table('module_group')->where('city', '=', $joinedCityId)->update(
                    ['city' => $structureElement->id]
                );
                $joinedCity->deleteElementData();
            }
        }
        return true;
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'joinCity',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}


