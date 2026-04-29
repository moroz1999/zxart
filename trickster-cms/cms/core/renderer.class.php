<?php

use App\Paths\PathsManager;

class renderer implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    /** @var rendererPlugin */
    private static $instance;
    /** @var renderer */
    private static $factory;

    public static function getInstance($pluginName = '', $applicationName = '', $debugMode = false)
    {
        if (self::$instance === null) {
            self::$instance = self::getPlugin($pluginName, $applicationName, $debugMode);
        }
        return self::$instance;
    }

    public static function createInstance($pluginName = '', $applicationName = '', $debugMode = false)
    {
        self::$instance = self::getPlugin($pluginName, $applicationName, $debugMode);
        return self::$instance;
    }

    public static function getFactory()
    {
        if (self::$factory === null) {
            self::$factory = new renderer();
        }
        return self::$factory;
    }

    public static function getPlugin($pluginName = '', $applicationName = '', $debugMode = false)
    {
        if ($factory = self::getFactory()) {
            return $factory->createPlugin($pluginName, $applicationName, $debugMode);
        }
        return false;
    }

    protected function createPlugin($pluginName, $applicationName, $debugMode)
    {
        $plugin = false;
        $pathsManager = $this->getService(PathsManager::class);
        $fileDirectory = $pathsManager->getRelativePath('rendererPlugins');
        $fileName = $pluginName . '.class.php';
        if ($filePath = $pathsManager->getIncludeFilePath($fileDirectory . $fileName)) {
            include_once($filePath);
            $className = $pluginName . 'RendererPlugin';
            $plugin = new $className();
            if ($plugin instanceof DependencyInjectionContextInterface) {
                $this->instantiateContext($plugin);
            }
            $plugin->debugMode = $debugMode;
            $plugin->init();
        }
        return $plugin;
    }

    private function __construct()
    {
    }
}


