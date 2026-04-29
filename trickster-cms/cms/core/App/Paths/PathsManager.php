<?php

namespace App\Paths;
use Config;

class PathsManager
{
    protected $rootPath;
    /**
     * @var Config
     */
    protected $config;
    protected $includePaths = [];
    protected $reversedIncludePaths = [];

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function setRootPath($path)
    {
        $this->rootPath = $path;
    }

    public function getRootPath()
    {
        return $this->rootPath;
    }

    public function getPath($pathKey)
    {
        $result = '';
        if ($dir = $this->getRelativePath($pathKey)) {
            $result = ROOT_PATH . $dir;
        }
        return $result;
    }

    public function getRelativePath($pathName)
    {
        return (string)$this->config->get($pathName);
    }

    public function getIncludeFilePath($filePath)
    {
        foreach ($this->reversedIncludePaths as $includePath) {
            if (is_file($includePath . $filePath)) {
                return $includePath . $filePath;
            }
        }
        return false;
    }

    public function getIncludePaths()
    {
        return $this->includePaths;
    }

    public function addIncludePath($includePath)
    {
        $this->includePaths[] = $includePath;
        array_unshift($this->reversedIncludePaths, $includePath);
    }

    public function ensureDirectory($path)
    {
        $result = true;
        if (is_dir($path) === false) {
            $defaultCachePermissions = $this->config->get('defaultCachePermissions');
            $result = mkdir($path, $defaultCachePermissions, true);
        }
        return $result;
    }
}