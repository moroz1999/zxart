<?php

use ZxArt\Ai\AiQueryService;

class AiManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance(): AiQueryService
    {
        return new AiQueryService();
    }

    /**
     * @param AiQueryService $instance
     * @return AiQueryService
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