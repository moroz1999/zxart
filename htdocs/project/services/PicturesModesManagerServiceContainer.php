<?php

class PicturesModesManagerServiceContainer extends DependencyInjectionServiceContainer
{
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
            $picturesModesManager->setUser($this->registry->getService('user'));
        }
        return $picturesModesManager;
    }
}