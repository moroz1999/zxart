<?php

class designThemeEmailDispatchmentType extends EmailDispatchmentType
{
    protected $cssThemeFilesStructure;
    protected $imagesThemeName;
    protected $emailTemplateThemeName;
    protected $emailTemplateName;
    protected $contentTemplateThemeName;
    protected $contentTemplateName;
    protected $designThemesManager;

    public function getCssFiles()
    {
        $designsManager = $this->getDesignsManager();
        foreach ($this->cssThemeFilesStructure as $themeCode => &$files) {
            if ($theme = $designsManager->getTheme($themeCode)) {
                foreach ($files as &$file) {
                    if ($css = $theme->getCssResource($file)) {
                        $this->cssFiles[] = $css['filePath'] . $css['fileName'];
                    }
                }
            }
        }
        return $this->cssFiles;
    }

    public function getCssImagesURL()
    {
        $designsManager = $this->getDesignsManager();
        if ($theme = $designsManager->getTheme($this->imagesThemeName)) {
            if ($url = $theme->getImagesUrl()) {
                $this->cssImagesURL = $url;
            }
        }
        return $this->cssImagesURL;
    }

    public function getEmailTemplate()
    {
        $designsManager = $this->getDesignsManager();
        if ($theme = $designsManager->getTheme($this->emailTemplateThemeName)) {
            if ($template = $theme->template($this->emailTemplateName)) {
                $this->emailTemplate = $template;
            }
        }
        return $this->emailTemplate;
    }

    public function getContentTemplate()
    {
        if ($theme = $this->getContentTheme()) {
            if ($template = $theme->template($this->contentTemplateName)) {
                $this->contentTemplate = $template;
            }
        }
        return $this->contentTemplate;
    }

    public function getDesignsManager()
    {
        $controller = controller::getInstance();
        return $controller->getApplication()->getDesignThemesManager();
    }

    //return desktop theme
    public function getTheme()
    {
        if (!$this->theme) {
            $designThemesManager = $this->getDesignsManager();
            $this->theme = $designThemesManager->getTheme($this->imagesThemeName);
        }
        return $this->theme;
    }

    //return document theme
    public function getContentTheme()
    {
        $designsManager = $this->getDesignsManager();
        return $designsManager->getTheme($this->contentTemplateThemeName);
    }

    public function getCurrentLanguageElement()
    {
        $controller = controller::getInstance();
        /**
         * @var LanguagesManager $languagesManager
         */
        $languagesManager = $controller->getApplication()->getLanguagesManager();

        return $languagesManager->getCurrentLanguageElement();
    }
}

