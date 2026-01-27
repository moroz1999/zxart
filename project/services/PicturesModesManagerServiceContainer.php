<?php

use App\Users\CurrentUser;

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
            $picturesModesManager->setUser($this->registry->getService(CurrentUser::class));
        }
        return $picturesModesManager;
    }
}