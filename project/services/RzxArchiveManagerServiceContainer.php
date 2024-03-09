<?php

class RzxArchiveManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new RzxArchiveManager();
    }

    /**
     * @param RzxArchiveManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $rzxArchiveManager = $instance;
        /**
         * @var ProdsManager $prodsManager
         */
        if ($prodsManager = $this->getOption('ProdsManager')) {
            $rzxArchiveManager->setProdsManager($prodsManager);
        } else {
            $rzxArchiveManager->setProdsManager($this->registry->getService('ProdsManager'));
        }
        return $rzxArchiveManager;
    }
}