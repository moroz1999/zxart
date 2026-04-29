<?php

class DesignThemesManager
{
    protected $currentThemeCode;
    protected $themesDirectoryPathList = [];
    protected $themesIndex = [];
    /**
     * @var ServerSessionManager
     */
    protected $serverSessionManager;
    protected $sessionStorageEnabled = false;

    /**
     * @param ServerSessionManager $serverSessionManager
     */
    public function setServerSessionManager($serverSessionManager)
    {
        $this->serverSessionManager = $serverSessionManager;
    }

    /**
     * Returns a design theme by code
     * @param string $code
     * @return DesignTheme|bool
     */
    public function getTheme($code)
    {
        if (!isset($this->themesIndex[$code])) {
            $this->themesIndex[$code] = $this->manufactureTheme($code);
        }

        return $this->themesIndex[$code];
    }

    /**
     * Returns a current design theme by code
     * @return DesignTheme|bool
     */
    public function getCurrentTheme()
    {
        return $this->getTheme($this->getCurrentThemeCode());
    }

    public function getCurrentThemeCode()
    {
        if ($this->currentThemeCode === null) {
            if ($this->sessionStorageEnabled) {
                if ($code = $this->serverSessionManager->get('DesignTheme')) {
                    if ($this->getTheme($code)) {
                        $this->currentThemeCode = $code;
                    }
                }
            }
        }
        return $this->currentThemeCode;
    }

    /**
     * Sets the current theme by code and controls if it's accessible
     * @param string $currentThemeCode
     * @return bool|\DesignTheme
     */
    public function setCurrentThemeCode($currentThemeCode)
    {
        if ($theme = $this->getTheme($currentThemeCode)) {
            if ($this->sessionStorageEnabled) {
                $this->serverSessionManager->set('DesignTheme', $currentThemeCode);
            }
            $this->currentThemeCode = $currentThemeCode;
        }
        return $theme;
    }

    /**
     * @param string $themesDirectoryPath
     */
    public function setThemesDirectoryPath($themesDirectoryPath)
    {
        $this->themesDirectoryPathList[] = $themesDirectoryPath;
    }

    /**
     * Manufactures and returns new theme object
     * @param string $code
     * @return DesignTheme|bool
     */
    protected function manufactureTheme($code)
    {
        $result = false;
        if (!$code !== null) {
            $className = $code . 'DesignTheme';
            foreach ($this->themesDirectoryPathList as $themesDirectoryPath) {
                if (!class_exists($className, false)) {
                    $filePath = $themesDirectoryPath . $code . '.class.php';
                    if (is_file($filePath)) {
                        include_once($filePath);
                    }
                }
                if (class_exists($className, false)) {
                    $result = new $className($this, $code);
                    break;
                }
            }
        }
        return $result;
    }
}
