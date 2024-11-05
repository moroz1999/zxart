<?php

use ZxArt\Prods\Services\ProdsService;

class RzxArchiveManagerServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return RzxArchiveManager
     */
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
         * @var ProdsService $prodsManager
         */
        if ($prodsManager = $this->getOption(ProdsService::class)) {
            $rzxArchiveManager->setProdsService($prodsManager);
        } else {
            $rzxArchiveManager->setProdsService($this->registry->getService(ProdsService::class));
        }
        return $rzxArchiveManager;
    }
}