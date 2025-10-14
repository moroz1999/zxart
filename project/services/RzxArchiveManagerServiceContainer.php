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
         * @var ProdsService $prodsService
         */
        if ($prodsService = $this->getOption(ProdsService::class)) {
            $rzxArchiveManager->setProdsService($prodsService);
        } else {
            $rzxArchiveManager->setProdsService($this->registry->getService(ProdsService::class));
        }
        return $rzxArchiveManager;
    }
}