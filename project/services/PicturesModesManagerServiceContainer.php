<?php

use App\Users\CurrentUserService;

class PicturesModesManagerServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return PicturesModesManager
     */
    public function makeInstance()
    {
        return new PicturesModesManager();
    }

    public function makeInjections($instance)
    {
        $picturesModesManager = $instance;
        if ($user = $this->getOption('user')) {
            $picturesModesManager->setUser($user);
        } else {
            $currentUserService = $this->registry->getService(CurrentUserService::class);
            $picturesModesManager->setUser($currentUserService->getCurrentUser());
        }
        return $picturesModesManager;
    }
}



