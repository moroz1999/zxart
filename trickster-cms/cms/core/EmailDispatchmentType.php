<?php

class EmailDispatchmentType
{
    protected $displayUnsubscribeLink;
    protected $emailTemplate;
    protected $contentTemplate;
    protected $displayWebLink;
    protected $cssFiles;
    protected $projectCssFiles;
    protected $cssImagesURL;
    protected $theme;
    protected $linksTrackingEnabled = false;

    /**
     * @return boolean
     */
    public function isLinksTrackingEnabled()
    {
        return $this->linksTrackingEnabled;
    }

    /**
     * @var EmailDispatchmentRenderer
     */
    protected $emailDispatchmentRenderer;

    /**
     * @param EmailDispatchmentRenderer $emailDispatchmentRenderer
     */
    public function setEmailDispatchmentRenderer(EmailDispatchmentRenderer $emailDispatchmentRenderer)
    {
        $this->emailDispatchmentRenderer = $emailDispatchmentRenderer;
    }

    public function initialize()
    {
    }

    public function getContentTemplate()
    {
        return $this->contentTemplate;
    }

    public function getDisplayUnsubscribeLink()
    {
        return $this->displayUnsubscribeLink;
    }

    public function getDisplayWebLink()
    {
        return $this->displayWebLink;
    }

    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    public function getCssFiles()
    {
        $result = [];
        if (is_array($this->cssFiles)) {
            foreach ($this->cssFiles as &$path) {
                if (is_file($path)) {
                    $result[] = $path;
                }
            }
        }
        return $result;
    }

    public function getCssImagesURL()
    {
        return $this->cssImagesURL;
    }

    /**
     * please use getTrackedBlankImage instead of this
     * @deprecated
     */
    public function getTrackedImageUrl($filename)
    {
        $imageUrl = $this->getImageUrl($filename);
        $dispatchmentId = $this->emailDispatchmentRenderer->getDispatchmentHistoryId();
        if ($imageUrl && $dispatchmentId) {
            $parsedImageUrl = parse_url($imageUrl);
            $parsedImageUrl['path'] = '/emails/action:viewImage/id:' . $dispatchmentId . $parsedImageUrl['path'];
            $imageUrl = $this->emailDispatchmentRenderer->unparse_url($parsedImageUrl);
        }
        return $imageUrl;
    }

    public function getImageUrl($fileName)
    {
        $imageUrl = '';
        if ($theme = $this->getTheme()) {
            $imageUrl = $theme->getImageUrl($fileName);
        }
        return $imageUrl;
    }

    public function getTrackedBlankImage()
    {
        $controller = controller::getInstance();
        $dispatchmentId = $this->emailDispatchmentRenderer->getDispatchmentHistoryId();
        return $controller->baseURL . '/emails/action:viewBlankImage/id:' . $dispatchmentId . '/';
    }

    public function getTheme()
    {
        if (!$this->theme) {
            $designThemesManager = $this->emailDispatchmentRenderer->getDesignThemesManager();
            $this->theme = $designThemesManager->getCurrentTheme();
        }
        return $this->theme;
    }
}