<?php

use ZxArt\Ai\AiQueryService;

class AiQueryServiceServiceContainer extends DependencyInjectionServiceContainer
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
        $AiQueryService = $instance;
        if ($configManager = $this->getOption('ConfigManager')) {
            $AiQueryService->setConfigManager($configManager);
        } else {
            $AiQueryService->setConfigManager($this->registry->getService('ConfigManager'));
        }

        return $AiQueryService;
    }
}