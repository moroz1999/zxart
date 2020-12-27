<?php

class ProdsDownloaderServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new ProdsDownloader();
    }

    public function makeInjections($instance)
    {
        $prodsDownloader = $instance;
        if ($configManager = $this->getOption('ConfigManager')) {
            $prodsDownloader->setConfigManager($configManager);
        } else {
            $prodsDownloader->setConfigManager($this->registry->getService('ConfigManager'));
        }

        return $prodsDownloader;
    }
}