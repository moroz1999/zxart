<?php

class ZxParsingManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new ZxParsingManager();
    }

    public function makeInjections($instance)
    {
        $zxParsingManager = $instance;
        if ($db = $this->getOption('db')) {
            $zxParsingManager->setDb($db);
        } else {
            $zxParsingManager->setDb($this->registry->getService('db'));
        }
        return $zxParsingManager;
    }
}