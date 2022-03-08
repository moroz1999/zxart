<?php

class SpeccyMapsManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new SpeccyMapsManager();
    }

    /**
     * @param SpeccyMapsManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $speccyMapsManager = $instance;
        /**
         * @var ProdsManager $prodsManager
         */
        if ($prodsManager = $this->getOption('ProdsManager')) {
            $speccyMapsManager->setProdsManager($prodsManager);
        } else {
            $speccyMapsManager->setProdsManager($this->registry->getService('ProdsManager'));
        }
        return $speccyMapsManager;
    }
}