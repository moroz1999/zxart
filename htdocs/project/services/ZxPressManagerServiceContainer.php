<?php

class ZxPressManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new ZxPressManager();
    }

    /**
     * @param ZxPressManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $zxPressManager = $instance;
        /**
         * @var ProdsManager $prodsManager
         */
        if ($prodsManager = $this->getOption('ProdsManager')) {
            $zxPressManager->setProdsManager($prodsManager);
        } else {
            $zxPressManager->setProdsManager($this->registry->getService('ProdsManager'));
        }
        return $zxPressManager;
    }
}