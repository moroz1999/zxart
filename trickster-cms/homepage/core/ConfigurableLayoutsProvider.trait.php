<?php

/**
 * Trait ConfigurableLayoutsProviderTrait
 *
 * Trait to elements to work with configurable layouts
 *
 * @property string $layout - magic field from module
 * @property string $structureType - magic field from module structureElement
 */
trait ConfigurableLayoutsProviderTrait
{
    public function getLayoutTypes()
    {
        /**
         * @var Config $layoutsConfig
         */
        $layoutsConfig = $this->getService(ConfigManager::class)->getConfig('layouts');
        $result = $layoutsConfig->getMerged("{$this->structureType}.layouts");

        return (array)$result;
    }

    public function getLayoutsSelection($layout = "layout")
    {
        /**
         * @var Config $layoutsConfig
         */
        $layoutsConfig = $this->getService(ConfigManager::class)->getConfig('layouts');
        $result = false;
        //check deprecated layout format
        if ($layout === 'layout') {
            if ($result = $layoutsConfig->getMerged("{$this->structureType}.main.options")) {
                $this->logError('deprecated layout main used:' . __CLASS__);
            }
        }

        //now check normal format
        if (!$result) {
            $result = $layoutsConfig->getMerged("{$this->structureType}.$layout.options");
        }
        return (array)$result;
    }

    public function getDefaultLayout($layout = "layout")
    {
        /**
         * @var Config $layoutsConfig
         */
        $layoutsConfig = $this->getService(ConfigManager::class)->getConfig('layouts');
        $result = $layoutsConfig->get("{$this->structureType}.$layout.default");
        if (!$result && $layout === 'layout') {
            $this->logError('deprecated layout main used:' . __CLASS__);
            $layout = 'main';
            $result = $layoutsConfig->get("{$this->structureType}.$layout.default");
        }
        if (!$result) {
            $options = $this->getLayoutsSelection($layout);
            if ($options) {
                $result = $options[0];
            }
        }
        if ($result == 'none') {
            return '';
        }
        return (string)$result;
    }

    public function getCurrentLayout($layout = 'layout')
    {
        if ($this->$layout) {
            return $this->$layout;
        }
        return $this->getDefaultLayout($layout);
    }
}