<?php

/**
 * Created by PhpStorm.
 * User: reneollino
 * Date: 23/10/14
 * Time: 13:24
 */
class ResourcesUniterHelper
{
    private $theme;
    private $cacheCode;
    private $userAgentEngineType;
    private $userAgent;
    private $userAgentVersion;
    private $designThemesManager;
    private $cssCachePath;
    private $jsCachePath;

    /**
     * @param mixed $designThemesManager
     */
    public function setDesignThemesManager($designThemesManager)
    {
        $this->designThemesManager = $designThemesManager;
    }

    /**
     * @return DesignThemesManager|null
     */
    public function getDesignThemesManager()
    {
        return $this->designThemesManager;
    }

    public function getCurrentThemeCode()
    {
        return $this->theme;
    }

    public function getResourceCacheFileName($type = null)
    {
        if ($type == 'css') {
            $cachePath = $this->cssCachePath;
        } elseif ($type == 'js') {
            $cachePath = $this->jsCachePath;
        } else {
            return false;
        }
        $cacheFile = $this->getCacheCode();
        if (is_file($cachePath . $cacheFile)) {
            $lastUpdated = filemtime($cachePath . $cacheFile);
        } else {
            $lastUpdated = null;
        }
        $cacheFile = $this->getCacheCode();

        return $cacheFile . $lastUpdated;
    }

    public function setCurrentThemeCode($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function getUserAgentEngineType()
    {
        return $this->userAgentEngineType;
    }

    public function setUserAgentEngineType($type)
    {
        $this->userAgentEngineType = $type;
    }

    public function getCacheCode()
    {
        if (!$this->cacheCode) {
            //            $this->cacheCode = $this->theme . '-' . $this->getUserAgentEngineType() . '-' . $this->getUserAgentCode() . '-';
            $this->cacheCode = $this->theme . '-' . $this->getUserAgentCode() . '-';
        }
        return $this->cacheCode;
    }

    /**
     * @param mixed $userAgentVersion
     */
    public function setUserAgentVersion($userAgentVersion)
    {
        $this->userAgentVersion = $userAgentVersion;
    }

    /**
     * This is now deprecated, it had an ambigous name
     * User agent version is number, but this was used for "code" (abbreviation + number).
     *
     * @deprecated
     */
    public function getUserAgentVersion()
    {
        return $this->getUserAgentCode();
    }

    public function getUserAgentCode()
    {
        if ($this->userAgent == "MSIE") {
            switch ($this->userAgentVersion) {
                case "9.0":
                    return "ie9";
                    break;
                case "8.0":
                    return "ie8";
                    break;
                case "7.0":
                    return "ie7";
                    break;
            }
        }
        if ($this->userAgent == 'iOS' && $this->userAgentVersion < 4) {
            return 'ios3';
        }

        return false;
    }

    public function getCssResources()
    {
        $designThemesManager = $this->getDesignThemesManager();
        $designThemesManager->setCurrentThemeCode($this->getCurrentThemeCode());
        $currentTheme = $designThemesManager->getCurrentTheme();

        if ($userAgentCode = $this->getUserAgentCode()) {
            $currentTheme->setExtraFolder($userAgentCode);
        }
        return $currentTheme->getCssResources();
    }

    public function getJavascriptResources()
    {
        $designThemesManager = $this->getDesignThemesManager();
        $designThemesManager->setCurrentThemeCode($this->getCurrentThemeCode());
        $currentTheme = $designThemesManager->getCurrentTheme();
        if ($currentTheme->getCode() !== 'shoppingBasketData' && $userAgentCode = $this->getUserAgentCode()) {
            $currentTheme->setExtraFolder($userAgentCode);
        }
        $javascriptResources = $currentTheme->getJavascriptResources();
        $filesList = [];
        foreach ($javascriptResources as &$resource) {
            $filesList[] = $resource['filePath'] . $resource['fileName'];
        }
        return $filesList;
    }

    public function getCssCachePath()
    {
        return $this->cssCachePath;
    }

    public function setCssCachePath($cssCachePath)
    {
        $this->cssCachePath = $cssCachePath;
    }

    public function getJsCachePath()
    {
        return $this->jsCachePath;
    }

    public function setJsCachePath($jsCachePath)
    {
        $this->jsCachePath = $jsCachePath;
    }
}