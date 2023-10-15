<?php

class AiManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance(): AiManager
    {
        return new AiManager();
    }

    /**
     * @param AiManager $instance
     * @return AiManager
     */
    public function makeInjections($instance)
    {
        $aiManager = $instance;
        if ($configManager = $this->getOption('ConfigManager')) {
            $aiManager->setConfigManager($configManager);
        } else {
            $aiManager->setConfigManager($this->registry->getService('ConfigManager'));
        }

        return $aiManager;
    }
}