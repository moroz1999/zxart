<?php

class receiveCity extends structureElementAction
{
    /**
     * @return void
     */
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

    protected function joinCities(structureElement $structureElement): bool
    {
        $structureManager = $this->getService('structureManager');
        if ($structureElement->joinCity == $structureElement->getId()) {
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
                    ['city' => $structureElement->getId()]
                );
                $db->table('module_group')->where('city', '=', $joinedCityId)->update(
                    ['city' => $structureElement->getId()]
                );
                $joinedCity->deleteElementData();
            }
        }
        return true;
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'joinCity',
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}


